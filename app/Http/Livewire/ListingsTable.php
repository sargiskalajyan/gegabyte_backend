<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Support\Facades\Storage;

class ListingsTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $statusOptions = ['draft','pending','published','rejected','expired'];

    public $galleryPhotos = [];
    public $galleryListingId = null;

    public $zoomImage = null;


    public function openGallery($listingId)
    {
        $listing = Listing::with('photos')->find($listingId);

        $this->galleryPhotos = $listing->photos->map(fn($p) => [
            'id' => $p->id,
            'url' => $p->url
        ])->toArray();

        $this->galleryListingId = $listingId;

        $this->dispatch('open-gallery-modal');
    }


    public function deletePhoto($photoId)
    {
        $photo = ListingPhoto::find($photoId);

        if (!$photo) {
            return;
        }

        $file = $photo->getRawOriginal('url');

        if ($file && Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
        }

        $photo->delete();

        // Refresh gallery or close modal if empty
        if ($this->galleryListingId) {
            $this->dispatch('close-gallery-modal');
        }

        $this->dispatch(
            'show-admin-toast',
            title: __('listings.deleted_title'),
            message: __('listings.deleted_message'),
            icon: "delete"
        );
    }


    public function openZoom($url)
    {
        $this->zoomImage = $url;
        $this->dispatch('open-zoom-modal');
    }


    public function updateStatus($id, $newStatus)
    {
        $listing = Listing::with('photos')->find($id);
        if (!$listing) {
            return;
        }

        $listing->update(['status' => $newStatus]);

        $image = $listing->photos->first()->thumbnail
            ?? $listing->photos->first()->url
            ?? null;

        // Build translated message
        $title = __('listings.status_updated_title');
        $message = __('listings.status_updated_message', [
            'id' => $listing->id,
            'status' => __('listings.statuses.' . $newStatus),
        ]);

        $this->dispatch('show-admin-toast',
            title: $title,
            message: $message,
            icon: 'check',
            image: $image
        );
    }


    public function render()
    {
        $listings = Listing::with('photos','user')
            ->orderBy('id', 'desc')
            ->paginate(2);

        return view('livewire.listings-table', compact('listings'));
    }
}
