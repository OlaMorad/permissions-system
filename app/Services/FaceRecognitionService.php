<?php

namespace App\Services;

use App\Http\Resources\successResource;
use Illuminate\Support\Facades\Http;
class FaceRecognitionService
{
    public function compareFaces($base64Image1, $base64Image2)
    {
$response = Http::asMultipart()
    ->attach('img1', base64_decode($base64Image1), 'img1.jpg')
    ->attach('img2', base64_decode($base64Image2), 'img2.jpg')
    ->post('https://c00b74b199a2.ngrok-free.app/process');



        if ($response->successful()) {
            return new successResource([]);
        }

        throw new \Exception('Face API Request failed: ' . $response->body());
    }
}
