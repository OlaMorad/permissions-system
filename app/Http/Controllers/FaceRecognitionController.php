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



    $imageFile = $request->file('image');

    // الصورة المخزنة للمستخدم
    $imagePath = public_path('storage/' . Auth::user()->avatar);

    if (!file_exists($imagePath)) {
        throw new \Exception("User avatar not found at: $imagePath");
    }


    try {
        $result = $this->faceRecognitionService->compareFaces(
            $imageFile->getPathname(), // ← نرسل المسار المؤقت للصورة المرفوعة
            $imagePath // ← نرسل مسار الصورة المخزنة
        );

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


}
