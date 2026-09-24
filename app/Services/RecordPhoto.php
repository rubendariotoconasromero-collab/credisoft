<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordPhoto
{
    public function replace(Request $request, string $table, string $field): array
    {
        $data = $request->validate([
            $field => 'required|integer|exists:'.$table.',id',
            'imagen' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048|dimensions:max_width=6000,max_height=6000',
        ]);
        $file = $request->file('imagen');
        $filename = (string) Str::uuid().'.'.$file->extension();
        $directory = \App\Services\PrivateImages::directory($table);
        $newPath = $directory.DIRECTORY_SEPARATOR.$filename;
        try {
            DB::transaction(function () use ($table, $field, $data, $file, $filename, $directory) {
                $record = DB::table($table)->where('id', $data[$field])->lockForUpdate()->first();
                abort_unless($record, 404);
                $file->move($directory, $filename);
                DB::table($table)->where('id', $record->id)->update(['imagen' => $filename]);
                $previous = $record->imagen;
                DB::afterCommit(function () use ($directory, $previous) {
                    // Only delete a file directly inside this managed directory.
                    if ($previous && $previous !== 'default.png' && basename($previous) === $previous) {
                        $path = $directory.DIRECTORY_SEPARATOR.$previous;
                        if (is_file($path) && !is_link($path)) @unlink($path);
                    }
                });
            });
        } catch (\Throwable $e) {
            if (is_file($newPath)) @unlink($newPath);
            throw $e;
        }
        return ['success' => true, 'imagen' => $filename, 'message' => 'Foto actualizada correctamente'];
    }
}
