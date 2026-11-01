<?php

namespace App\Console\Commands;

use App\Models\CatalogPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneCatalogImages extends Command
{
    protected $signature = 'catalog:prune-images';

    protected $description = 'Remove abandoned catalog image uploads older than 24 hours, retaining every database reference';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $removed = 0;
        foreach (array_chunk($disk->allFiles('catalog'), 200) as $files) {
            $used = CatalogPhoto::whereIn('path', $files)->pluck('path')->all();
            foreach (array_diff($files, $used) as $file) {
                if (preg_match('#^catalog/[1-9][0-9]*/[a-f0-9-]{36}\.webp$#D', $file) && $disk->lastModified($file) < now()->subDay()->timestamp) {
                    if ($disk->delete($file)) {
                        $removed++;
                    }
                }
            }
        }
        $this->info('Removed abandoned images: '.$removed);

        return 0;
    }
}
