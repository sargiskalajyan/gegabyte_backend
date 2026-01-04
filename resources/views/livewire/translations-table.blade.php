<div class="container-fluid py-4">
    {{-- Toasts always top right --}}
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    {{-- TYPE SELECT --}}
    <div class="mb-3">
        <select class="form-control w-25" wire:change="changeType($event.target.value)">
            @foreach(config('translations') as $key => $item)
                <option value="{{ $key }}" @selected($type === $key)>
                    {{ __('translations.types.' . $key) }}
                </option>
            @endforeach
        </select>
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
                            <input type="file"
                                   class="form-control"
                                   wire:model="form.base.image_url">

                            @if(isset($form['base']['image_url']) && is_string($form['base']['image_url']))
                                <img src="{{ asset('storage/'.$form['base']['image_url']) }}"
                                     class="mt-2 rounded"
                                     style="height:70px">
                            @endif

                            {{-- car_models make --}}
                        @elseif($type === 'car_models' && $field === 'make_id')
                            <select class="form-control" wire:model.defer="form.base.make_id">
                                <option value="">{{ __('translations.select_item') }}</option>
                                @foreach($relatedItems as $item)
                                    <option value="{{ $item->id }}">#{{ $item->id }}</option>
                                @endforeach
                            </select>

                            {{-- makes category --}}
                        @elseif($type === 'makes' && $field === 'category_id')
                            <select class="form-control" wire:model.defer="form.base.category_id">
                                <option value="">{{ __('translations.select_item') }}</option>
                                @foreach($relatedItems as $item)
                                    <option value="{{ $item->id }}">#{{ $item->id }}</option>
                                @endforeach
                            </select>

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


