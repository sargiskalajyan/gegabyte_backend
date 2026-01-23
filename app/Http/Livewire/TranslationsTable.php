<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationsTable extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [];

    public string $type = 'categories';
    public array $config = [];

    public string $search = '';

    public $languages;
    public $relatedItems = []; // makes / categories
    public array $relatedMap = []; // id => name
    public ?int $editingId = null;
    public bool $showForm = false;
    public array $form = [];



    protected function rules()
    {
        $rules = [];

        foreach ($this->languages as $lang) {
            foreach ($this->config['fields'] as $field) {
                $rules["form.translations.{$lang->id}.{$field}"] = 'nullable|string|max:255';
            }
        }

        foreach ($this->config['base_fields'] ?? [] as $field) {
            if ($this->type === 'categories' && $field === 'image_url') {
                $rules["form.base.image_url"] = 'nullable|image|max:2048';
            } else {
                $rules["form.base.{$field}"] = 'nullable';
            }
        }

        return $rules;
    }

    private function forgetFiltersCache(): void
    {
        try {
            $codes = DB::table('languages')->pluck('code');
            foreach ($codes as $code) {
                $code = (string) $code;
                if ($code !== '') {
                    Cache::forget("filters_{$code}");
                    Cache::forget("filters_{$code}_categories");
                    Cache::forget("filters_{$code}_packages");
                }
            }
        } catch (\Throwable $e) {
            // Cache invalidation should never block saving translations.
        }
    }

    public function mount()
    {
        $this->loadConfig();
    }

    public function changeType(string $type)
    {
        $this->type = $type;
        $this->reset(['editingId', 'form', 'showForm', 'search']);
        $this->resetPage();
        $this->loadConfig();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function loadConfig()
    {
        $this->config = config("translations.{$this->type}");
        abort_unless($this->config, 404);

        $this->languages = DB::table('languages')->orderBy('code')->get();

        $languageId = $this->getCurrentLanguageId();

        // Load related select data
        if ($this->type === 'car_models') {
            $this->relatedItems = DB::table('makes')
                ->leftJoin('make_translations as mt', function ($join) use ($languageId) {
                    $join
                        ->on('mt.make_id', '=', 'makes.id')
                        ->where('mt.language_id', '=', $languageId);
                })
                ->select('makes.id', 'mt.name')
                ->orderBy('makes.id')
                ->get();
        } elseif ($this->type === 'makes') {
            $this->relatedItems = DB::table('categories')
                ->leftJoin('category_translations as ct', function ($join) use ($languageId) {
                    $join
                        ->on('ct.category_id', '=', 'categories.id')
                        ->where('ct.language_id', '=', $languageId);
                })
                ->select('categories.id', 'ct.name')
                ->orderBy('categories.id')
                ->get();
        } else {
            $this->relatedItems = [];
        }

        $this->relatedMap = collect($this->relatedItems)
            ->mapWithKeys(fn ($row) => [(int) $row->id => (string) ($row->name ?? '')])
            ->all();
    }

    private function getCurrentLanguageId(): int
    {
        $code = (string) app()->getLocale();

        $lang = DB::table('languages')->where('code', $code)->first();
        if ($lang && isset($lang->id)) {
            return (int) $lang->id;
        }

        $first = DB::table('languages')->orderBy('id')->first();
        if ($first && isset($first->id)) {
            return (int) $first->id;
        }

        return 1;
    }

    public function create()
    {
        $this->editingId = null;
        $this->showForm = true;

        $this->form['base'] = [];
        foreach ($this->config['base_fields'] ?? [] as $field) {
            $this->form['base'][$field] = null;
        }

        $this->form['translations'] = [];
    }

    public function edit(int $id)
    {
        $this->editingId = $id;
        $this->showForm = true;

        $baseRow = DB::table($this->config['base_table'])->find($id);

        $this->form['base'] = [];
        foreach ($this->config['base_fields'] ?? [] as $field) {
            $this->form['base'][$field] = $baseRow->$field ?? null;
        }

        $this->form['translations'] = [];
        $rows = DB::table($this->config['translation_table'])
            ->where($this->config['foreign_key'], $id)
            ->get();

        foreach ($rows as $row) {
            foreach ($this->config['fields'] as $field) {
                $this->form['translations'][$row->language_id][$field] = $row->$field;
            }
        }
    }

    public function save()
    {
        $hasAtLeastOneTranslation = false;

        foreach ($this->form['translations'] ?? [] as $fields) {
            if (collect($fields)->filter()->isNotEmpty()) {
                $hasAtLeastOneTranslation = true;
                break;
            }
        }

        if (! $hasAtLeastOneTranslation) {
            $this->dispatch('show-admin-toast',
                title: __('translations.error_title'),
                message: __('translations.validation.at_least_one'),
                icon: 'error'
            );
            return;
        }

        $this->validate();

        DB::transaction(function () {

            // Handle category image upload
            if (
                $this->type === 'categories' &&
                isset($this->form['base']['image_url']) &&
                is_object($this->form['base']['image_url'])
            ) {
                $this->form['base']['image_url'] =
                    $this->form['base']['image_url']->store('categories', 'public');
            }

            $baseData = $this->form['base'] ?? [];

            if (! $this->editingId) {
                if (! empty($baseData)) {
                    $this->editingId = DB::table($this->config['base_table'])
                        ->insertGetId($baseData);
                } else {
                    $this->editingId = DB::table($this->config['base_table'])
                        ->insertGetId([]);
                }
            } else {
                if (! empty($baseData)) {
                    DB::table($this->config['base_table'])
                        ->where('id', $this->editingId)
                        ->update($baseData);
                }
            }

            foreach ($this->form['translations'] as $languageId => $fields) {
                if (collect($fields)->filter()->isEmpty()) continue;

                DB::table($this->config['translation_table'])
                    ->updateOrInsert(
                        [
                            $this->config['foreign_key'] => $this->editingId,
                            'language_id' => $languageId,
                        ],
                        array_merge(
                            [
                                $this->config['foreign_key'] => $this->editingId,
                                'language_id' => $languageId,
                            ],
                            $fields
                        )
                    );
            }
        });

        $this->forgetFiltersCache();

        $this->reset(['editingId', 'form', 'showForm']);

        $this->dispatch('show-admin-toast',
            title: __('translations.saved_title'),
            message: __('translations.saved_message'),
            icon: 'success'
        );
    }

    public function deleteCategory(int $id)
    {
        DB::transaction(function () use ($id) {
            DB::table($this->config['translation_table'])
                ->where($this->config['foreign_key'], $id)
                ->delete();

            DB::table($this->config['base_table'])
                ->where('id', $id)
                ->delete();
        });

        $this->forgetFiltersCache();

        $this->resetPage();
    }

    public function render()
    {
        $baseTable = $this->config['base_table'];
        $translationTable = $this->config['translation_table'];
        $foreignKey = $this->config['foreign_key'];

        $parentsQuery = DB::table($baseTable);

        $search = trim($this->search);
        if ($search !== '') {
            $escaped = addcslashes($search, "\\%_");
            $like = "%{$escaped}%";

            $parentsQuery->where(function ($query) use ($search, $like, $baseTable, $translationTable, $foreignKey) {
                if (ctype_digit($search)) {
                    $query->orWhere("{$baseTable}.id", (int) $search);
                }

                foreach (($this->config['base_fields'] ?? []) as $field) {
                    if ($field === 'image_url') {
                        continue;
                    }

                    $query->orWhere("{$baseTable}.{$field}", 'like', $like);
                }

                $query->orWhereExists(function ($subQuery) use ($like, $baseTable, $translationTable, $foreignKey) {
                    $subQuery
                        ->select(DB::raw(1))
                        ->from($translationTable)
                        ->whereColumn("{$translationTable}.{$foreignKey}", "{$baseTable}.id")
                        ->where(function ($translationQuery) use ($like) {
                            foreach ($this->config['fields'] as $field) {
                                $translationQuery->orWhere($field, 'like', $like);
                            }
                        });
                });
            });
        }

        $parents = $parentsQuery
            ->orderBy("{$baseTable}.id")
            ->paginate(10);

        $parentIds = $parents->getCollection()->pluck('id')->all();

        $translations = DB::table($translationTable)
            ->when(! empty($parentIds), fn ($q) => $q->whereIn($foreignKey, $parentIds))
            ->get()
            ->groupBy($foreignKey)
            ->map(fn ($group) => $group->keyBy('language_id'));

        return view('livewire.translations-table', compact('parents', 'translations'));
    }
}
