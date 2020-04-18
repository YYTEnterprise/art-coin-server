<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{

    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|in:avatar,cover,context',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2028',
        ]);

        $type = $request->input('type');
        $path = $request->file('file')->store("/public/$type");

        return [
            'path' => url(Storage::url($path)),
        ];
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'avatar' => 'required|string',
        ]);

        Storage::delete($request->input('avatar'));

        return new Response('', 200);
    }
}
