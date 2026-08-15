<?php

namespace App\Models;

use Database\Factories\CastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'tmdb_id', 'name', 'character', 'profile_path', 'order',
])]
class Cast extends Model
{
    /** @use HasFactory<CastFactory> */
    use HasFactory;

    /**
     * @return MorphTo<Model, $this>
     */
    public function castable(): MorphTo
    {
        return $this->morphTo();
    }
}
