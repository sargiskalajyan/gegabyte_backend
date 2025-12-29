<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class ExpireListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire published listings at midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Listing::where('status', 'published')
            ->whereNotNull('published_until')
            ->where('published_until', '<=', now()->startOfDay())
            ->update([
                'status' => 'expired',
            ]);

        return Command::SUCCESS;
    }
}
