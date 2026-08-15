<?php

namespace Database\Seeders;

use App\Models\Quality;
use Illuminate\Database\Seeder;

class QualitySeeder extends Seeder
{
    public function run(): void
    {
        $qualities = [
            ['name' => '480p', 'label' => 'SD', 'width' => 854, 'height' => 480, 'bitrate' => 1500, 'sort_order' => 1],
            ['name' => '720p', 'label' => 'HD', 'width' => 1280, 'height' => 720, 'bitrate' => 4000, 'sort_order' => 2],
            ['name' => '1080p', 'label' => 'Full HD', 'width' => 1920, 'height' => 1080, 'bitrate' => 8000, 'sort_order' => 3],
            ['name' => '4k', 'label' => 'Ultra HD', 'width' => 3840, 'height' => 2160, 'bitrate' => 20000, 'sort_order' => 4],
        ];

        foreach ($qualities as $quality) {
            Quality::updateOrCreate(
                ['name' => $quality['name']],
                $quality,
            );
        }
    }
}
