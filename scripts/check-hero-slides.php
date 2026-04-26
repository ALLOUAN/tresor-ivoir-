<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\AppearanceSlide;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasTable('appearance_slides')) {
    echo "NO_TABLE\n";
    exit(0);
}

$q = AppearanceSlide::query()->where('is_active', true)->whereNotNull('desktop_image_url')->orderBy('display_order')->orderByDesc('id');
echo 'count='.$q->count()."\n";
$s = $q->first();
if ($s) {
    echo 'desktop='.$s->desktop_image_url."\n";
}
