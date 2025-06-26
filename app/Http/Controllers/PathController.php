<?php

namespace App\Http\Controllers;

use App\Http\Resources\successResource;
use App\Models\Path;
use Illuminate\Http\Request;

class PathController extends Controller
{
    public function index()
    {
        $data=Path::all();
        return new successResource([$data]);
    }
}
