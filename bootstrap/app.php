<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'Verify.Session' => \App\Http\Middleware\VerifySingleSession::class,
            'working.hours' => \App\Http\Middleware\CheckWorkingHours::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

       $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            // رسالة مخصصة
            $message = 'الرابط المطلوب غير موجود. الرجاء التحقق من الرابط.';

            // إذا كان الطلب يريد JSON (API مثلا)
            if ($request->expectsJson()) {
                return new JsonResponse(['message' => $message], Response::HTTP_NOT_FOUND);
            }

            return response($message, Response::HTTP_NOT_FOUND);
        });
        
  $exceptions->renderable(function (\Exception $e, $request) {

            $message = strtolower($e->getMessage());

            // قائمة كلمات مفتاحية تدل على انقطاع الاتصال أو مشاكل الشبكة
            $connectionIssues = [
                'broken pipe',
                'connection reset by peer',
                'connection aborted',
                'connection refused',
                'connection timed out',
                'client disconnected',
                'stream socket',
            ];

            foreach ($connectionIssues as $issue) {
                if (str_contains($message, $issue)) {
                    return response()->json([
                        'message' => 'انقطع الاتصال أثناء معالجة الطلب. الرجاء المحاولة مرة أخرى.'
                    ], 499); // كود 499 مخصص لحالة انقطاع العميل قبل اكتمال الرد
                }
            }

        });


    })
    ->create();
