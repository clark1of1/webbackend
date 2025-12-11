<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Handle file upload.
     */
    public function upload(Request $request)
    {
        // Validate file
        $request->validate([
            'file' => 'required|file|max:2048', // max 2MB
        ]);

        // Store file in /storage/app/public/uploads
        $path = $request->file('file')->store('uploads', 'public');

        return response()->json([
            'message' => 'File uploaded successfully!',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ], 200);
    }

    /**
     * List all uploaded files.
     */
    public function list()
    {
        $files = Storage::disk('public')->files('uploads');

        $fileData = array_map(function ($file) {
            return [
                'name' => basename($file),
                'path' => $file,
                'url' => asset('storage/' . $file),
            ];
        }, $files);

        return response()->json($fileData, 200);
    }

    /**
     * Delete a specific file.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filePath = 'uploads/' . $request->filename;

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);

            return response()->json([
                'message' => 'File deleted successfully.'
            ], 200);
        }

        return response()->json([
            'message' => 'File not found.'
        ], 404);
    }
}
