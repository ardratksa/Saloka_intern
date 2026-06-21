<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\CleaningReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    public function cleaning(Request $request)
    {
        return Excel::download(
            new CleaningReportExport(
                $request->start_date,
                $request->end_date
            ),
            'cleaning-report.xlsx'
        );
    }
}