<?php

namespace App\Http\Controllers;

use App\Services\InternalMailArchiveService;
use Illuminate\Http\Request;

class InternalMailArchiveController extends Controller
{

   public function __construct(protected InternalMailArchiveService $archive){}

    public function add_to_archive(){
    return  $this->archive->add_to_archive();
    }
}
