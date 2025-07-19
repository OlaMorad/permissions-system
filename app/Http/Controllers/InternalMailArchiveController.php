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
    public function show_received_archive(){
        return $this->archive->show_received_archive();
    }

    public function show_sent_archive_for_director($role_name){
         return $this->archive->show_sent_archive_for_director($role_name);
    }

    public function show_received_archive_for_director($role_name){
        return $this->archive->show_received_archive_for_director($role_name);
    }
}
