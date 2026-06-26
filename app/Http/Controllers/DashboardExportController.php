<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class DashboardExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Issue::with([
            'location.type',
            'checklist.period',
            'documentations',
        ]);

        if ($request->filled('date')) {
            $query->whereDate(
                'date',
                $request->date
            );
        }

        if ($request->filled('location_id')) {
            $query->where(
                'location_id',
                $request->location_id
            );
        }

        if ($request->filled('location_type_id')) {

            $query->whereHas(
                'location',
                fn ($q) =>
                $q->where(
                    'location_type_id',
                    $request->location_type_id
                )
            );
        }

        if ($request->filled('shift')) {

            $query->whereHas(
                'checklist.period',
                fn ($q) =>
                $q->where(
                    'name',
                    $request->shift
                )
            );
        }

        $issues = $query->latest()->get();

        $spreadsheet = new Spreadsheet();

        $sheet =
            $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:I1');

        $sheet->setCellValue(
            'A1',
            'MASTER OUTPUT'
        );

        $sheet->fromArray([
            [
                'NO',
                'TANGGAL',
                'TIME',
                'LOKASI',
                'Y',
                'N',
                'ISSUE',
                'REMARK',
                'DOKUMENTASI'
            ]
        ], null, 'A3');

        $row = 4;

        foreach ($issues as $index => $issue) {

            $sheet->setCellValue(
                "A{$row}",
                $index + 1
            );

            $sheet->setCellValue(
                "B{$row}",
                $issue->date
            );

            $sheet->setCellValue(
                "C{$row}",
                optional(
                    $issue->created_at
                )->format('H:i')
            );

            $sheet->setCellValue(
                "D{$row}",
                $issue->location?->name
            );

            $sheet->setCellValue(
                "E{$row}",
                $issue->status === 'resolved'
                    ? 'Y'
                    : ''
            );

            $sheet->setCellValue(
                "F{$row}",
                $issue->status === 'open'
                    ? 'N'
                    : ''
            );

            $sheet->setCellValue(
                "G{$row}",
                $issue->type
            );

            $sheet->setCellValue(
                "H{$row}",
                $issue->description
            );

            $sheet->getRowDimension($row)
                ->setRowHeight(90);

            $doc =
                $issue->documentations
                    ->first();

            if ($doc) {

                $path =
                    storage_path(
                        'app/public/' .
                        $doc->image
                    );

                if (file_exists($path)) {

                    $drawing =
                        new Drawing();

                    $drawing->setPath(
                        $path
                    );

                    $drawing->setHeight(
                        80
                    );

                    $drawing->setCoordinates(
                        "I{$row}"
                    );

                    $drawing->setWorksheet(
                        $sheet
                    );
                }
            }

            $row++;
        }

        foreach (
            range('A', 'I')
            as $column
        ) {

            $sheet
                ->getColumnDimension(
                    $column
                )
                ->setAutoSize(true);
        }

        $fileName =
            'Dashboard_Issue_Report.xlsx';

        $writer =
            new Xlsx(
                $spreadsheet
            );

        $temp =
            tempnam(
                sys_get_temp_dir(),
                'excel'
            );

        $writer->save($temp);

        return response()
            ->download(
                $temp,
                $fileName
            )
            ->deleteFileAfterSend(true);
    }
}