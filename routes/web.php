<?php

use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Storage;

Route::get('/test-b2', function () {
    Storage::disk('b2')->put('test.txt', 'This is a test.');
    return 'تم رفع الملف إلى Backblaze';
});


Route::get('/test-firebase-config', function () {
    dd([
        'credentials_file' =>config('firebase.projects.app.credentials.credentials_file'),
        'project_id' =>  config('firebase.projects.app.credentials.project_id'),
    ]);
});




Route::get('/test-firebase', function () {
    try {
        $service = app(\App\Services\FirebaseNotificationService::class);

        // استدعاء التهيئة مباشرة
        $messaging = $service->messaging();

        // رسالة تجريبية للـ topic "test-topic"
        $message = CloudMessage::withTarget('topic', 'test-topic')
            ->withNotification(Notification::create(
                '🚀 Test Title',
                'Hello from Laravel backend!'
            ));

        $messaging->send($message);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to topic test-topic'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});
