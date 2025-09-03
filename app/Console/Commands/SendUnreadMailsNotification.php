<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

class SendUnreadMailsNotification extends Command
{

    protected $signature = 'mails:notify-unread';

    protected $description = 'إرسال إشعار بعدد الرسائل غير المقروءة لكل مستخدم';

    protected $notificationService;

    public function __construct(FirebaseNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        // نجيب كل المستخدمين اللي إلهم path مرتبط
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $role = $user->getRoleNames()->first();
            if (!$role) {
                continue;
            }

            $pathId = Role::where('name', $role)->value('path_id');
            if (!$pathId) {
                continue;
            }
            $roleId = $user->roles()->pluck('id')->first();
            // نحسب الرسائل المرتبطة بالـ path اللي لسا ما انقرت من جدول الكسر
            $unreadCount = DB::table('internal_mail_paths')
                ->join('internal_mails', 'internal_mail_paths.internal_mail_id', '=', 'internal_mails.id')
                ->where('internal_mail_paths.path_id', $pathId)
                ->where('internal_mail_paths.role_id', $roleId)
                ->where('internal_mail_paths.is_read', 0)
                ->count();

            if ($unreadCount > 0) {
                $title = "بريد جديد";
                $body = "لديك {$unreadCount} رسالة غير مقروءة.";
                $this->notificationService->sendToUser($user->id, $title, $body, [
                    'type' => 'unread_mail',
                    'count' => (string) $unreadCount,
                ]);
            }
        }

        $this->info('تم إرسال إشعارات البريد غير المقروء.');
    }
}
