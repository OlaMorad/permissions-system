<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Http\Resources\successResource;

class FaceRecognitionService
{
    public function compareFaces($path1, $path2)
    {
        $response = Http::asMultipart()
            ->attach('img1', file_get_contents($path1), 'img1.jpg')
            ->attach('img2', file_get_contents($path2), 'img2.jpg')
            ->post('http://127.0.0.1:5000/process');

        if ($response->successful()) {

            $data = $response->json();
            return new successResource($data);
        }

        throw new \Exception('Face API Request failed: ' . $response->body());
    }
}
