<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected SearchService $search) {}
    public function Search_degree_doctor(Request $request)
    {
        $searchTerm = $request->input('search');
        return $this->search->Search_degree_doctor($searchTerm);
    }

    public function Search_Exam_Request(Request $request){
                $searchTerm = $request->input('search');
        return $this->search->Search_Exam_Request($searchTerm);

    }
}
