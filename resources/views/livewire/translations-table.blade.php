<div class="container-fluid py-4">
    {{-- Toasts always top right --}}
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    {{-- TYPE SELECT + SEARCH --}}
    <div class="row g-3 mb-3 align-items-end">
        <div class="col-12 col-md-3">
            <select class="form-control" wire:change="changeType($event.target.value)">
                @foreach(config('translations') as $key => $item)
                    <option value="{{ $key }}" @selected($type === $key)>
                        {{ __('translations.types.' . $key) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-5">
            <div class="input-group input-group-outline @if(strlen($search ?? '') > 0) is-filled @endif">
                <label class="form-label">{{ __('translations.search') }}</label>
                <input type="text"
                       class="form-control"
                       wire:model.live.debounce.300ms="search"
                       autocomplete="off">
            </div>
        </div>

        <div class="col-12 col-md-auto">
            @if(!empty($search))
                <button class="btn btn-outline-secondary mb-0"
                        type="button"
                        wire:click="$set('search','')"
                        wire:loading.attr="disabled"
                        wire:target="search">
                    {{ __('translations.clear_search') }}
                </button>
            @endif
        </div>
    </div>

    {{-- ADD NEW --}}
    <button class="btn btn-primary mb-3" wire:click="create">
        {{ __('translations.add_new') }}
    </button>

    {{-- FORM --}}
    @if($showForm)
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                {{ $editingId ? __('translations.edit').' #'.$editingId : __('translations.add_new') }}
            </div>

            <div class="card-body">

                {{-- BASE FIELDS --}}
                @foreach($config['base_fields'] ?? [] as $field)
                    <div class="mb-3">
                        <label class="form-label">
                            {{ __('translations.fields.' . $field) }}
                        </label>

                        {{-- parent_id --}}
                        @if($field === 'parent_id')
                            <select class="form-control" wire:model.defer="form.base.parent_id">
                                <option value="">{{ __('translations.select_item') }}</option>
                                @foreach($parents as $p)
                                    <option value="{{ $p->id }}">#{{ $p->id }}</option>
                                @endforeach
                            </select>

                            {{-- categories image --}}
                        @elseif($type === 'categories' && $field === 'image_url')
                            <div class="d-flex flex-wrap align-items-center gap-2"
                                 wire:key="category-image-input-{{ $type }}-{{ $editingId ?? 'new' }}">
                                <input id="category_image_url"
                                       type="file"
                                       class="d-none"
                                       accept="image/*"
                                       wire:model="form.base.image_url">

                                <label for="category_image_url" class="btn btn-outline-secondary mb-0">
                                    {{ __('translations.choose_file') }}
                                </label>

                                <span class="text-sm text-muted">
                                    @if(isset($form['base']['image_url']) && is_object($form['base']['image_url']))
                                        {{ $form['base']['image_url']->getClientOriginalName() }}
                                    @elseif(isset($form['base']['image_url']) && is_string($form['base']['image_url']) && $form['base']['image_url'])
                                        {{ basename($form['base']['image_url']) }}
                                    @else
                                        {{ __('translations.no_file_chosen') }}
                                    @endif
                                </span>
                            </div>

                            <div class="form-text">{{ __('translations.choose_file_help') }}</div>

                            @if(isset($form['base']['image_url']) && is_string($form['base']['image_url']))
                                <img src="{{ asset('storage/'.$form['base']['image_url']) }}"
                                     class="mt-2 rounded"
                                     style="height:70px">
                            @endif

                            {{-- car_models make --}}
                        @elseif($type === 'car_models' && $field === 'make_id')
                            <select class="form-control @error('form.base.make_id') is-invalid @enderror" wire:model.defer="form.base.make_id">
                                <option value="">{{ __('translations.select_item') }}</option>
                                @foreach($relatedItems as $item)
                                    <option value="{{ $item->id }}">
                                        #{{ $item->id }}@if(!empty($item->name)) — {{ $item->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('form.base.make_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            {{-- makes category --}}
                        @elseif($type === 'makes' && $field === 'category_id')
                            <select class="form-control @error('form.base.category_id') is-invalid @enderror" wire:model.defer="form.base.category_id">
                                <option value="">{{ __('translations.select_item') }}</option>
                                @foreach($relatedItems as $item)
                                    <option value="{{ $item->id }}">
                                        #{{ $item->id }}@if(!empty($item->name)) — {{ $item->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('form.base.category_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                        @else
                            <input type="text"
                                   class="form-control"
                                   wire:model.defer="form.base.{{ $field }}">
                        @endif
                    </div>
                @endforeach

                {{-- TRANSLATIONS --}}
                @foreach($languages as $lang)
                    <div class="border rounded p-3 mb-3">
                        <strong>{{ strtoupper($lang->code) }}</strong>
                        <div class="row mt-2">
                            @foreach($config['fields'] as $field)
                                <div class="col-md-4">
                                    <input type="text"
                                           class="form-control"
                                           placeholder="{{ __('translations.fields.' . $field) }}"
                                           wire:model.defer="form.translations.{{ $lang->id }}.{{ $field }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button class="btn btn-success" wire:click="save">
                    {{ __('translations.save') }}
                </button>

                <button class="btn btn-secondary ms-2"
                        wire:click="$set('showForm', false)">
                    {{ __('translations.cancel') }}
                </button>
            </div>
        </div>
    @endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>

                    @foreach($config['base_fields'] ?? [] as $field)
                        <th>{{ __('translations.fields.' . $field) }}</th>
                    @endforeach

                    <th>{{ __('translations.title') }}</th>
                    <th width="160">{{ __('translations.actions') }}</th>
                </tr>
                </thead>

                <tbody>
                @foreach($parents as $parent)
                    <tr>
                        <td>#{{ $parent->id }}</td>

                        {{-- BASE VALUES --}}
                        @foreach($config['base_fields'] ?? [] as $field)
                            <td>
                                @if($type === 'categories' && $field === 'image_url' && $parent->$field)
                                    <img src="{{ asset('storage/'.$parent->$field) }}"
                                         style="height:40px">
                                @elseif($type === 'makes' && $field === 'category_id')
                                    {{ $relatedMap[$parent->$field] ?? ('#'.($parent->$field ?? '-')) }}
                                @elseif($type === 'car_models' && $field === 'make_id')
                                    {{ $relatedMap[$parent->$field] ?? ('#'.($parent->$field ?? '-')) }}
                                @else
                                    {{ $parent->$field ?? '-' }}
                                @endif
                            </td>
                        @endforeach

                        {{-- TRANSLATIONS --}}
                        <td class="text-start">
                            @foreach($languages as $lang)
                                @php
                                    $translation = $translations->get($parent->id)?->get($lang->id);
                                @endphp
                                <div>
                                    <strong>{{ strtoupper($lang->code) }}:</strong>
                                    {{ $translation?->{$config['fields'][0]} ?? '-' }}
                                </div>
                            @endforeach
                        </td>

                        {{-- ACTIONS --}}
                        <td>
                            <button class="btn btn-sm btn-info"
                                    wire:click="edit({{ $parent->id }})">
                                {{ __('translations.edit') }}
                            </button>

                            <button class="btn btn-sm btn-danger"
                                    wire:click="$dispatch('confirm-delete', {{ $parent->id }})">
                                {{ __('translations.delete') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $parents->links() }}
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirm-delete', (id) => {
            Swal.fire({
                title: @json(__('translations.confirm_delete_title')),
                text: @json(__('translations.confirm_delete_text')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: @json(__('translations.confirm_delete_yes')),
                cancelButtonText: @json(__('translations.confirm_delete_cancel')),
            }).then((result) => {
                if (result.isConfirmed) {
                @this.call('deleteCategory', id);
                }
            });
        });
    });
</script>


