<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\successResource;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // عرض جميع الإعلانات
    public function index()
    {
        $data = Announcement::select('id', 'title', 'created_at')->get();
        return new successResource($data);
    }
    // عرض إعلان واحد بالتفصيل
    public function show($id)
    {
        $data = Announcement::findOrFail($id);
        return new successResource($data);
    }

    // إنشاء إعلان جديد
    public function store(AnnouncementRequest $request)
    {
        $data = Announcement::create($request->validated());
        return response()->json([
            'message' => 'تم إنشاء الإعلان بنجاح',
            'data' => $data,
        ], 201);
    }
}
