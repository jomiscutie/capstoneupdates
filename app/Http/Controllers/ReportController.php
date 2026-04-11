<?php

namespace App\Http\Controllers;

use App\Http\Requests\BatchGenerateReportRequest;
use App\Http\Requests\GenerateReportRequest;
use App\Models\Attendance;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class ReportController extends Controller
{
    /**
     * Generate monthly attendance report for a specific student
     */
    public function generateMonthlyReport(GenerateReportRequest $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $studentId = (int) $request->input('student_id');
        $month = $request->input('month');

        $student = Student::findOrFail($studentId);
        if (! $student->isVisibleToCoordinator($coordinator)) {
            return back()->with('error', 'You do not have permission to view this student\'s report.');
        }

        try {
            $date = Carbon::createFromFormat('Y-m', $month);
            $data = $this->buildMonthlyReportViewData($coordinator, $student, $date, $this->resolveLogoDataUri());
            $binary = $this->renderMonthlyPdfBinary($data, $month);
            if ($binary === null) {
                return back()->with('error', 'Unable to generate the report. Please try again. If the problem persists, contact support.');
            }
            $filename = 'DTR_'.$student->student_no.'_'.$month.'.pdf';

            return response()->streamDownload(function () use ($binary) {
                echo $binary;
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            Log::error('Report generation failed', [
                'coordinator_id' => $coordinator->id ?? null,
                'student_id' => $studentId,
                'month' => $month,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Unable to generate the report. Please try again. If the problem persists, contact support.');
        }
    }

    /**
     * Batch: ZIP of monthly DTR PDFs for selected students (same month).
     */
    public function generateBatchMonthlyReports(BatchGenerateReportRequest $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $month = $request->input('month');
        $ids = array_values(array_unique(array_map('intval', $request->input('student_ids', []))));

        $date = Carbon::createFromFormat('Y-m', $month);
        $logoDataUri = $this->resolveLogoDataUri();

        $added = 0;
        $skipped = [];
        /** @var array<string, string> $entries filename in archive => file contents */
        $entries = [];

        foreach ($ids as $studentId) {
            $student = Student::find($studentId);
            if (! $student || ! $student->isVisibleToCoordinator($coordinator)) {
                $skipped[] = 'ID '.$studentId.' (not found or not in your program)';

                continue;
            }
            try {
                $data = $this->buildMonthlyReportViewData($coordinator, $student, $date, $logoDataUri);
                $binary = $this->renderMonthlyPdfBinary($data, $month);
                if ($binary === null) {
                    $skipped[] = $student->student_no.' (PDF error)';

                    continue;
                }
                $entryName = 'DTR_'.$student->student_no.'_'.$month.'.pdf';
                $entries[$entryName] = $binary;
                $added++;
            } catch (\Throwable $e) {
                Log::warning('Batch report skipped student', [
                    'student_id' => $studentId,
                    'error' => $e->getMessage(),
                ]);
                $skipped[] = ($student->student_no ?? (string) $studentId).' (error)';
            }
        }

        if ($added === 0) {
            return back()->with('error', 'No reports could be generated. Check that all selected students belong to your program and try a smaller batch.');
        }

        if ($skipped !== []) {
            $entries['_batch_notes.txt'] =
                'NORSU OJT DTR — batch export '.$month."\n".
                "Generated PDFs: {$added}\n".
                "Skipped:\n- ".implode("\n- ", $skipped)."\n";
        }

        $zipName = 'DTR_batch_'.$month.'_'.$added.'_files.zip';

        try {
            return response()->streamDownload(function () use ($entries, $zipName) {
                $out = fopen('php://output', 'wb');
                if ($out === false) {
                    throw new \RuntimeException('Could not open download stream.');
                }
                $zip = new ZipStream(
                    outputStream: $out,
                    sendHttpHeaders: false,
                    outputName: $zipName,
                    defaultCompressionMethod: CompressionMethod::STORE,
                );
                foreach ($entries as $name => $contents) {
                    $zip->addFile($name, $contents);
                }
                $zip->finish();
            }, $zipName, [
                'Content-Type' => 'application/zip',
            ]);
        } catch (\Throwable $e) {
            Log::error('Batch ZIP stream failed', [
                'coordinator_id' => $coordinator->id ?? null,
                'month' => $month,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not build the ZIP download. Please try again or contact support.');
        }
    }

    /**
     * Show report generation form
     */
    public function showReportForm()
    {
        $coordinator = Auth::guard('coordinator')->user();
        $students = Student::forCoordinator($coordinator)->verified()->orderBy('name')->get();

        return view('coordinator.generate-report', compact('students'));
    }

    private function resolveLogoDataUri(): ?string
    {
        try {
            $logoPath = public_path('images/norsu-seal.png');
            if (is_file($logoPath) && is_readable($logoPath)) {
                $contents = file_get_contents($logoPath);
                if ($contents !== false && strlen($contents) > 0 && strlen($contents) < 500000) {
                    return 'data:image/png;base64,'.base64_encode($contents);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Report logo skipped', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @return array<string, mixed> View data for reports.monthly-attendance
     */
    private function buildMonthlyReportViewData($coordinator, Student $student, Carbon $date, ?string $logoDataUri): array
    {
        $year = $date->year;
        $monthNum = $date->month;
        $studentId = $student->id;

        $attendances = Attendance::valid()->where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date', 'asc')
            ->get();

        $totalDays = $date->daysInMonth;
        $presentDays = $attendances->count();
        $absentDays = $totalDays - $presentDays;

        $lateArrivals = $attendances->filter(function ($attendance) {
            return $attendance->is_late || $attendance->afternoon_is_late;
        })->count();

        $totalMinutes = $attendances->sum(function ($attendance) {
            return Student::minutesFromRenderedHours($attendance->hours_rendered);
        });

        $totalHours = (int) floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        $byDay = $attendances->keyBy(function ($a) {
            return (int) Carbon::parse($a->date)->format('j');
        });
        $undertimeMonthMinutes = 0;
        $dtrRows = [];
        for ($day = 1; $day <= 31; $day++) {
            $inMonth = $day <= $totalDays;
            $a = $inMonth ? $byDay->get($day) : null;
            $lateTotal = 0;
            if ($a) {
                $lateTotal = (int) ($a->late_minutes ?? 0) + (int) ($a->afternoon_late_minutes ?? 0);
                $undertimeMonthMinutes += $lateTotal;
            }
            $lunchOut = null;
            if ($a) {
                $rawLunch = $a->getRawOriginal('lunch_break_out');
                if ($rawLunch) {
                    $lunchOut = Carbon::parse($rawLunch)->format(Attendance::TIME_12_FORMAT);
                }
            }
            $dtrRows[] = [
                'day' => $day,
                'in_month' => $inMonth,
                'am_in' => $a?->time_in_12,
                'am_out' => $lunchOut,
                'pm_in' => $a?->afternoon_time_in_12,
                'pm_out' => $a?->time_out_12,
                'ut_h' => $lateTotal > 0 ? intdiv($lateTotal, 60) : null,
                'ut_m' => $lateTotal > 0 ? $lateTotal % 60 : null,
            ];
        }
        $undertimeTotalH = $undertimeMonthMinutes > 0 ? intdiv($undertimeMonthMinutes, 60) : null;
        $undertimeTotalM = $undertimeMonthMinutes > 0 ? $undertimeMonthMinutes % 60 : null;

        return [
            'student' => $student,
            'month' => $date->format('F Y'),
            'monthNum' => $monthNum,
            'year' => $year,
            'attendances' => $attendances,
            'totalDays' => $totalDays,
            'presentDays' => $presentDays,
            'absentDays' => $absentDays,
            'lateArrivals' => $lateArrivals,
            'totalHours' => $totalHours,
            'totalMinutes' => $remainingMinutes,
            'coordinator' => $coordinator,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y g:i A'),
            'logoDataUri' => $logoDataUri,
            'dtrRows' => $dtrRows,
            'monthName' => $date->format('F'),
            'yearFull' => $year,
            'undertimeTotalH' => $undertimeTotalH,
            'undertimeTotalM' => $undertimeTotalM,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function renderMonthlyPdfBinary(array $data, string $monthYm): ?string
    {
        try {
            $pdf = Pdf::loadView('reports.monthly-attendance', $data);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error('Report PDF render failed', [
                'student_id' => $data['student']->id ?? null,
                'month' => $monthYm,
                'error' => $e->getMessage(),
            ]);
        }

        if (($data['logoDataUri'] ?? null) !== null) {
            try {
                $data['logoDataUri'] = null;
                $pdf = Pdf::loadView('reports.monthly-attendance', $data);
                $pdf->setPaper('A4', 'portrait');

                return $pdf->output();
            } catch (\Throwable $e2) {
                Log::error('Report retry without logo failed', ['error' => $e2->getMessage()]);
            }
        }

        return null;
    }
}
