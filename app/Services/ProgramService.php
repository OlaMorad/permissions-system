<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamRequestEnum;
use App\Http\Resources\successResource;
use App\Presenters\ProgramPresenter;


class ProgramService
{
    public function create_Program(array $data): Program
    {
        return DB::transaction(function () use ($data) {
            $exams = $data['exams'];

            // استخراج تاريخ البداية والنهاية من تواريخ الامتحانات
            $date = collect($exams)->pluck('date')->sort();
            $startDate = $date->first();
            $endDate = $date->last();

            $program = Program::create([
                'month' => $data['month'],
                'year' => $data['year'],
                'exams_count' => count($exams),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            foreach ($exams as $exam) {
                $program->exams()->create([
                    'specialization_id' => $exam['specialization_id'],
                    'day' => $exam['day'],
                    'date' => $exam['date'],
                    'simple_ratio' => $exam['simple_ratio'],
                    'average_ratio' => $exam['average_ratio'],
                    'hard_ratio' => $exam['hard_ratio'],
                    'start_time' => $exam['start_time'],
                    'end_time' => $exam['end_time'],
                ]);
            }

            return $program;
        });
    }
    // عرض كل برامج الامتحانات
    public function get_all_programs()
    {
        $programs = Program::get();
        return new successResource(ProgramPresenter::program($programs));


        return new successResource($programs);
    }
    // عرض تفاصيل برنامج امتحاني
    public function show_program_details($id)
    {
        $program = Program::with('exams.specialization')->find($id);

        if (!$program) {
            return response()->json([
                'message' => 'البرنامج غير موجود.',
            ], 404);
        }
        return new successResource(ProgramPresenter::exams($program->exams));
    }
}
