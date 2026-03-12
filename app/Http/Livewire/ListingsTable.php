<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Services\SmsService;
use Illuminate\Support\Facades\Storage;

class ListingsTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $statusOptions = ['draft', 'published', 'rejected'];

    public string $search = '';

    public $galleryPhotos = [];
    public $galleryListingId = null;

    public $zoomImage = null;


    public function updatingSearch(): void
    {
        $this->resetPage();
    }


    /**
     * @param $listingId
     * @return void
     */
    public function openGallery($listingId)
    {
        $listing = Listing::with('photos')->find($listingId);

        $this->galleryPhotos = $listing->photos->map(fn($p) => [
            'id' => $p->id,
            'url' => $p->url,
            'is_default' => $p->is_default,
        ])->toArray();

        $this->galleryListingId = $listingId;

        $this->dispatch('open-gallery-modal');
    }


    /**
     * @param $photoId
     * @return void
     */
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


    /**
     * @param $url
     * @return void
     */
    public function openZoom($url)
    {
        $this->zoomImage = $url;
        $this->dispatch('open-zoom-modal');
    }


    /**
     * @param $id
     * @param $newStatus
     * @return void
     */
    public function updateStatus($id, $newStatus)
    {
        $listing = Listing::with(['photos', 'user.language'])->find($id);
        if (!$listing) {
            return;
        }

        if (!in_array($newStatus, $this->statusOptions))  {
            return;
        }

        $listing->status = $newStatus;

        if ($newStatus === 'published') {
            $listing->published_until = now()->addDays(30)->startOfDay();
        }


        if ($newStatus === 'pending' || $newStatus === 'draft') {
            $listing->published_until = null;
        }

        $listing->save();

        if ($newStatus === 'published') {
            $this->sendPublishedSms($listing);
        }

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


    /**
     * @param $photoId
     * @return void
     */
    public function setDefaultPhoto($photoId)
    {
        $photo = ListingPhoto::find($photoId);

        if (!$photo) {
            return;
        }

        // Reset previous default for this listing
        ListingPhoto::where('listing_id', $photo->listing_id)
            ->update(['is_default' => false]);

        // Set new default
        $photo->update(['is_default' => true]);

        $this->dispatch('close-gallery-modal');

        $this->dispatch(
            'show-admin-toast',
            title: __('listings.default_photo_title'),
            message: __('listings.default_photo_message'),
            icon: 'check',
            image: $photo->thumbnail ?? $photo->url
        );
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function render()
    {
        $query = Listing::with('photos', 'user');

        $search = trim($this->search);
        if ($search !== '') {
            $escaped = addcslashes($search, "\\\\%_");
            $like = "%{$escaped}%";

            // Try to match translated status label (what is shown with __('listings.statuses.*'))
            $normalizedSearch = mb_strtolower($search);
            $matchedStatus = null;

            foreach ($this->statusOptions as $status) {
                $label = mb_strtolower(__('listings.statuses.' . $status));

                if (str_contains($label, $normalizedSearch)) {
                    $matchedStatus = $status;
                    break;
                }
            }

            $query->where(function ($q) use ($search, $like, $matchedStatus) {
                if (ctype_digit($search)) {
                    $id = (int) $search;

                    $q->orWhere('id', $id)
                        ->orWhere('user_id', $id)
                        ->orWhereHas('user', function ($uq) use ($id) {
                            $uq->where('id', $id);
                        });
                }

                if ($matchedStatus !== null) {
                    $q->orWhere('status', $matchedStatus);
                } else {
                    $q->orWhere('status', 'like', $like);
                }

                // Make name (translated)
                $q->orWhereHas('make.translations', function ($mq) use ($like) {
                    $mq->where('name', 'like', $like);
                });

                // Car model name (translated)
                $q->orWhereHas('carModel.translations', function ($mq) use ($like) {
                    $mq->where('name', 'like', $like);
                });

                // User fields
                $q->orWhereHas('user', function ($uq) use ($like) {
                    $uq->where('username', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone_number', 'like', $like);
                });
            });
        }

        $listings = $query
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.listings-table', compact('listings'));
    }


    /**
     * @param Listing $listing
     * @return void
     */
    private function sendPublishedSms(Listing $listing): void
    {
        $phone = $listing->user?->phone_number;
        $isVerified = $listing->user?->phone_number_verified_at;

        if (!$phone || !$isVerified) {
            return;
        }

        $locale = 'hy';
        $message = trans('listings.sms_published', [], $locale);

        app(SmsService::class)->sendSms($phone, $message);
    }
}
