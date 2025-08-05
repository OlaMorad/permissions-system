<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FaceRecognitionService;

class FaceRecognitionController extends Controller
{

    public function __construct(protected FaceRecognitionService $faceRecognitionService) {}

    public function verify(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $image1 = base64_encode(file_get_contents($request->file('image')->path()));
$imagePath = public_path('storage/' . Auth::user()->avatar);

if (!file_exists($imagePath)) {
    throw new \Exception("User avatar not found at: $imagePath");
}


$imageAuthUser = base64_encode(file_get_contents($imagePath));
        try {
            $result = $this->faceRecognitionService->compareFaces($image1, $imageAuthUser);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
