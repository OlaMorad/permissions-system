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

    public function Search_Exam_Request(Request $request)
    {
        $searchTerm = $request->input('search');
        return $this->search->Search_Exam_Request($searchTerm);
    }

    public function Search_Specialization_Name(Request $request)
    {
        $searchTerm = $request->input('search');
        return $this->search->Search_Specialization_Name($searchTerm);
    }

    public function Search_Employee(Request $request)
    {
        $searchTerm = $request->input('search');
        return $this->search->Search_Employee($searchTerm);
    }

        public function Search_Form(Request $request)
    {
        $searchTerm = $request->input('search');
        return $this->search->Search_Form($searchTerm);
    }

            public function Search_Announcements(Request $request)
    {
        $searchTerm = $request->input('search');
        return $this->search->Search_Announcements($searchTerm);
    }

    public function Search_Archive(Request $request){
        $searchTerm = $request->input('search');
        return $this->search->Search_Archive($searchTerm);

    }
    public function TransactionSearch(Request $request)
    {
        $key = $request->input('key');
        return $this->search->TransactionSearch($key);

    }
    public function ArchiveTransactionSearch(Request $request)
    {
        $key = $request->input('key');
        return $this->search->Archive_Transaction_Search($key);
    }
}
