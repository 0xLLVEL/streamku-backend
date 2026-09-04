<?php

namespace App\Models;

use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'tmdb_id', 'key', 'site', 'type', 'name', 'official',
])]
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'official' => 'boolean',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function videoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'key' => $this->key,
            'site' => $this->site,
            'name' => $this->name,
            'official' => $this->official ? true : false,
        ];
    }
}
