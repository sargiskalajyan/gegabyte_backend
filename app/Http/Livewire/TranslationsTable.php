<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class TranslationsTable extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [];

    public string $type = 'categories';
    public array $config = [];

    public $languages;
    public $relatedItems = []; // makes / categories
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

    public function mount()
    {
        $this->loadConfig();
    }

    public function changeType(string $type)
    {
        $this->type = $type;
        $this->reset(['editingId', 'form', 'showForm']);
        $this->resetPage();
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->config = config("translations.{$this->type}");
        abort_unless($this->config, 404);

        $this->languages = DB::table('languages')->orderBy('code')->get();

        // Load related select data
        if ($this->type === 'car_models') {
            $this->relatedItems = DB::table('makes')->get();
        } elseif ($this->type === 'makes') {
            $this->relatedItems = DB::table('categories')->get();
        } else {
            $this->relatedItems = [];
        }
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

            if (! $this->editingId) {
                $this->editingId = DB::table($this->config['base_table'])
                    ->insertGetId($this->form['base'] ?? []);
            } else {
                DB::table($this->config['base_table'])
                    ->where('id', $this->editingId)
                    ->update($this->form['base'] ?? []);
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

        $this->resetPage();
    }

    public function render()
    {
        $parents = DB::table($this->config['base_table'])
            ->orderBy('id')
            ->paginate(10);

        $translations = DB::table($this->config['translation_table'])
            ->get()
            ->groupBy($this->config['foreign_key'])
            ->map(fn ($group) => $group->keyBy('language_id'));

        return view('livewire.translations-table', compact('parents', 'translations'));
    }
}
