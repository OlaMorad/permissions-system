<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\FaceRecognitionService;

class FaceRecognitionController extends Controller
{
    public function __construct(protected FaceRecognitionService $faceRecognitionService) {}

public function verify(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg',
    ]);

<<<<<<< Updated upstream
        $image1 = base64_encode(file_get_contents($request->file('image')->path()));
        $imagePath = public_path('storage/' . Auth::user()->avatar);

        if (!file_exists($imagePath)) {
            throw new \Exception("User avatar not found at: $imagePath");
        }
=======
    $imageFile = $request->file('image');

    // الصورة المخزنة للمستخدم
    $imagePath = public_path('storage/' . Auth::user()->avatar);
>>>>>>> Stashed changes

    if (!file_exists($imagePath)) {
        throw new \Exception("User avatar not found at: $imagePath");
    }

<<<<<<< Updated upstream
        $imageAuthUser = base64_encode(file_get_contents($imagePath));
        try {
            $result = $this->faceRecognitionService->compareFaces($image1, $imageAuthUser);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
=======
    try {
        $result = $this->faceRecognitionService->compareFaces(
            $imageFile->getPathname(), // ← نرسل المسار المؤقت للصورة المرفوعة
            $imagePath // ← نرسل مسار الصورة المخزنة
        );

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
>>>>>>> Stashed changes
    }
}






public function uploadAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $user = Auth::user();

    // خزني الصورة داخل مجلد avatars
    $path = $request->file('avatar')->store('avatars', 'public');

    // احذفي الصورة القديمة إذا كانت موجودة
    if ($user->avatar) {
        Storage::disk('public')->delete($user->avatar);
    }

    // خزني المسار الجديد داخل قاعدة البيانات
    $user->avatar = $path;
    $user->save();

    return response()->json([
        'message' => 'تم رفع الصورة بنجاح',
        'path' => asset('storage/' . $path),
    ]);
}

}
