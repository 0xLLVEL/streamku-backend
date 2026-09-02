<?php

namespace App\Data\Requests;

use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class InitiateUploadData extends Data
{
    public function __construct(
        public string $filename,
        public string $mime_type,
        public int $total_size,
        public int $media_id,
        public string $media_type,
        public string $type,
        public int $quality_id,
        public string $collection = 'default',
        public ?array $metadata = null,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'total_size' => ['required', 'integer', 'min:1'],
            'media_id' => ['required', 'integer'],
            'media_type' => ['required', 'string', 'in:movie,episode'],
            'type' => ['required', 'string', 'in:video,image,subtitle'],
            'quality_id' => ['required', 'integer', 'exists:qualities,id'],
            'collection' => ['string', 'in:default,poster,backdrop,thumbnail,stream,subtitles,video'],
            'metadata' => ['nullable', 'array'],
            'metadata.language' => ['nullable', 'string', 'max:50'],
            'metadata.label' => ['nullable', 'string', 'max:100'],
        ];
    }
}
