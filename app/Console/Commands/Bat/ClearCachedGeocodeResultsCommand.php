<?php

declare(strict_types=1);

namespace App\Console\Commands\Bat;

use App\Models\CachedGeocodeResult;
use Illuminate\Console\Command;

class ClearCachedGeocodeResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bat:clear-cached-geocode-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the cached geocode results';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CachedGeocodeResult::query()->truncate();
    }
}
