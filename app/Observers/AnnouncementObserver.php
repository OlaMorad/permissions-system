<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Services\FirebaseNotificationService;

class AnnouncementObserver
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = app(FirebaseNotificationService::class);
    }

    public function created(Announcement $announcement)
    {
        // إرسال إشعار لجميع الأطباء
        $this->firebase->sendToRole(
            'الطبيب', // اسم الرول للأطباء
            'إعلان جديد',
            $announcement->title,
            ['announcement_id' => $announcement->id] // بيانات إضافية للفرونت
        );
    }
}
