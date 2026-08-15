<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Data\Requests\InitiateUploadData;
use App\Data\UploadData;
use App\Http\Controllers\Controller;
use App\Models\Upload;
use App\Services\ChunkedUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function __construct(
        private ChunkedUploadService $uploadService,
    ) {}

    public function index(): JsonResponse
    {
        $uploads = Upload::where('user_id', request()->user()->id)
            ->whereIn('status', ['pending', 'uploading', 'processing'])
            ->latest()
            ->paginate(20);

        return $this->success($uploads);
    }

    public function initiate(InitiateUploadData $data): JsonResponse
    {
        $upload = $this->uploadService->initiate(
            $data->toArray(),
            request()->user()->id,
        );

        return response()->json([
            'data' => UploadData::from([
                'upload_id' => $upload->upload_id,
                'filename' => $upload->filename,
                'total_size' => $upload->total_size,
                'chunk_size' => $upload->chunk_size,
                'total_chunks' => $upload->total_chunks,
                'received_chunks' => $upload->received_chunks,
                'status' => $upload->status,
                'progress_percent' => $upload->progress_percent,
            ]),
            'message' => 'Upload session created. Send chunks to the chunks endpoint.',
        ], 201);
    }

    public function chunk(Request $request, Upload $upload): JsonResponse
    {
        $request->validate([
            'chunk_number' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file'],
        ]);

        try {
            $chunk = $this->uploadService->storeChunk(
                $upload,
                $request->integer('chunk_number'),
                $request->file('chunk'),
            );

            return response()->json([
                'chunk_number' => $chunk->chunk_number,
                'received_chunks' => $upload->fresh()->received_chunks,
                'total_chunks' => $upload->total_chunks,
                'progress_percent' => $upload->fresh()->progress_percent,
            ]);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function status(Upload $upload): JsonResponse
    {
        return response()->json([
            'data' => $this->uploadService->getStatus($upload),
        ]);
    }

    public function complete(Upload $upload): JsonResponse
    {
        try {
            $media = $this->uploadService->complete($upload);

            return response()->json([
                'data' => $media,
                'message' => 'Upload completed. Media created.',
            ], 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function cancel(Upload $upload): JsonResponse
    {
        $this->uploadService->cancel($upload);

        return $this->success(null, 'Upload cancelled.');
    }
}
