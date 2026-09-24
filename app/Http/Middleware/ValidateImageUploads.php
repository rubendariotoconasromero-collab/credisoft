<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ValidateImageUploads
{
    public function handle(Request $request, Closure $next)
    {
        // Validate every upload before any controller can delete or update a record.
        foreach (Arr::dot($request->allFiles()) as $key => $file) {
            Validator::make(['imagen' => $file], [
                'imagen' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048|dimensions:max_width=6000,max_height=6000',
            ])->validate();
        }
        return $next($request);
    }
}
