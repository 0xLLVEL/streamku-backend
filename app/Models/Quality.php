<?php

namespace App\Models;

use Database\Factories\QualityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'label', 'width', 'height', 'bitrate', 'sort_order'])]
class Quality extends Model
{
    /** @use HasFactory<QualityFactory> */
    use HasFactory;

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
