<div class="container-fluid py-4">
    {{-- Toasts always top right --}}
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    <div class="card my-4">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0 text-white">{{ __('listings.title') }}</h6>
        </div>

        <div class="card-body px-0">

            <div class="table-responsive">
                <table class="table align-items-center mb-0 text-center">
                    <thead>
                    <tr>
                        <th>{{ __('listings.photos') }}</th>
                        <th>{{ __('listings.id') }}</th>
                        <th>{{ __('listings.make_model') }}</th>
                        <th>{{ __('listings.description') }}</th>
                        <th>{{ __('listings.price') }}</th>
                        <th>{{ __('listings.user') }}</th>
                        <th>{{ __('listings.status') }}</th>
                        <th>{{ __('listings.change') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($listings as $listing)
                        <tr>
                            <td>
                                <button class="btn btn-sm btn-info"
                                        wire:click="openGallery({{ $listing->id }})">
                                    {{ __('listings.gallery') }}
                                    ({{ $listing->photos->count() }})
                                </button>
                            </td>

                            <td>{{ $listing->id }}</td>

                            <td>
                                <b>{{ $listing->make_name }}</b> /
                                {{ $listing->model_name }}
                            </td>

                            <td style="max-width:180px" class="text-truncate">
                                {{ $listing->description }}
                            </td>

                            <td>${{ number_format($listing->price,2) }}</td>

                            <td>{{ $listing->user->username ?? '-' }}</td>

                            <td>
                                <span class="badge bg-primary">
                                    {{ __('listings.statuses.' . $listing->status) }}
                                </span>
                            </td>

                            <td>
                                <select class="form-control form-control-sm"
                                        wire:change="updateStatus({{ $listing->id }}, $event.target.value)">
                                    @foreach ($statusOptions as $status)
                                        <option value="{{ $status }}"
                                            @selected($listing->status == $status)>
                                            {{ __('listings.statuses.' . $status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $listings->links() }}
            </div>
        </div>
    </div>


    {{-- -------------------------------
        FULLSCREEN GALLERY MODAL
    -------------------------------- --}}
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ __('listings.gallery') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="galleryCarousel" class="carousel slide">
                        <div class="carousel-inner">
                            @foreach ($galleryPhotos as $i => $photo)
                                <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">
                                    <div class="text-center">
                                        <img src="{{ $photo['url'] }}"
                                             class="img-fluid"
                                             style="max-height:500px; cursor:zoom-in;"
                                             wire:click="openZoom('{{ $photo['url'] }}')">
                                    </div>

                                    <div class="text-center mt-2 d-flex justify-content-center gap-2">
                                        <button class="btn btn-danger btn-sm"
                                                wire:click="deletePhoto({{ $photo['id'] }})">
                                            {{ __('listings.delete') }}
                                        </button>


                                        @if($photo['is_default'])
                                                <button class="btn btn-warning btn-sm" disabled>
                                                    {{ __('listings.default') }}
                                                </button>
                                        @else
                                            <button class="btn btn-success btn-sm"
                                                    wire:click="setDefaultPhoto({{ $photo['id'] }})">
                                                {{ __('listings.make_default') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button class="carousel-control-prev" type="button"
                                data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button"
                                data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- -------------------------------
        ZOOM LIGHTBOX MODAL
    -------------------------------- --}}
    <div class="modal fade" id="zoomModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">

                <button id="zoomCloseBtn"
                        class="btn btn-light position-absolute top-0 end-0 m-3"
                        style="z-index: 9999">
                    {{ __('listings.close') }}
                </button>

                <div class="modal-body d-flex justify-content-center align-items-center">
                    @if($zoomImage)
                        <img src="{{ $zoomImage }}" class="img-fluid" style="max-height:95vh;">
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>



<script>
    document.addEventListener('DOMContentLoaded', () => {

        const galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));
        const zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));

        // OPEN GALLERY
        window.addEventListener('open-gallery-modal', () => {
            galleryModal.show();
        });

        // CLOSE GALLERY
        window.addEventListener('close-gallery-modal', () => {
            galleryModal.hide();
        });

        // OPEN ZOOM
        window.addEventListener('open-zoom-modal', () => {
            galleryModal.hide();
            zoomModal.show();
        });

        // CLOSE ZOOM
        document.getElementById('zoomCloseBtn').addEventListener('click', () => {
            zoomModal.hide();
        });

    });
</script>
