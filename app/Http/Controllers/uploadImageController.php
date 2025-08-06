<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class uploadImageController extends Controller
{
    public function uploadAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $user = Auth::user();

    $path = $request->file('avatar')->store('avatars', 'public');

    if ($user->avatar) {
        Storage::disk('public')->delete($user->avatar);
    }

    $user->avatar = $path;
    $user->save();

    return response()->json([
        'message' => 'تم رفع الصورة بنجاح',
        'path' => asset('storage/' . $path),
    ]);
}
}
