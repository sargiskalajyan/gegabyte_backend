<?php

namespace App\Http\Livewire;

use App\Models\Listing;

class PendingListingsTable extends ListingsTable
{
    public string $pageTitle = 'listings.pending_title';

    public function render()
    {
        $query = Listing::with('photos', 'user')
            ->where('status', 'pending');

        $search = trim($this->search);
        if ($search !== '') {
            $escaped = addcslashes($search, "\\\\%_");
            $like = "%{$escaped}%";

            $normalizedSearch = mb_strtolower($search);
            $pendingLabel = mb_strtolower(__('listings.statuses.pending'));
            $matchesPendingStatus = str_contains($pendingLabel, $normalizedSearch);

            $query->where(function ($q) use ($search, $like, $matchesPendingStatus) {
                if (ctype_digit($search)) {
                    $id = (int) $search;

                    $q->orWhere('id', $id)
                        ->orWhere('user_id', $id)
                        ->orWhereHas('user', function ($uq) use ($id) {
                            $uq->where('id', $id);
                        });
                }

                if ($matchesPendingStatus) {
                    $q->orWhere('status', 'pending');
                }

                $q->orWhereHas('make.translations', function ($mq) use ($like) {
                    $mq->where('name', 'like', $like);
                });

                $q->orWhereHas('carModel.translations', function ($mq) use ($like) {
                    $mq->where('name', 'like', $like);
                });

                $q->orWhereHas('user', function ($uq) use ($like) {
                    $uq->where('username', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone_number', 'like', $like);
                });
            });
        }

        $listings = $query
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('livewire.listings-table', [
            'listings' => $listings,
            'pageTitle' => $this->pageTitle,
        ]);
    }
}