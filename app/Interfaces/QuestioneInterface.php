<?php
namespace App\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface QuestioneInterface
{
    public function addFromForm(Request $request);
    public function addFromExcel(Collection $rows): void;
}
