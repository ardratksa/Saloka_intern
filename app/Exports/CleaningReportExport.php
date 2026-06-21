<?php

namespace App\Exports;

use App\Models\Checklist;
use App\Models\LocationName;
use App\Models\Period;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CleaningReportExport implements
    FromArray,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        protected string $startDate,
        protected string $endDate
    ) {}

    public function array(): array
    {
        $periods = Period::orderBy('time_start')
            ->get();

        $locations = LocationName::orderBy('name')
            ->get();

        $rows = [];

        $rows[] = [
            'OUTPUT CLEANING REPORT'
        ];

        $rows[] = [
            'Periode',
            $this->startDate .
            ' s/d ' .
            $this->endDate
        ];

        $rows[] = [];

        $header = [
            'No',
            'Lokasi',
        ];

        foreach ($periods as $period) {

            $header[] = substr(
                $period->time_start,
                0,
                5
            );
        }

        $header[] = 'Total Cleaning';

        $rows[] = $header;

        foreach ($locations as $index => $location) {

            $row = [
                $index + 1,
                $location->name,
            ];

            $totalCleaning = 0;

            foreach ($periods as $period) {

                $count = Checklist::where(
                        'location_id',
                        $location->id
                    )
                    ->where(
                        'periode_id',
                        $period->id
                    )
                    ->where(
                        'status',
                        'done'
                    )
                    ->whereBetween(
                        'date',
                        [
                            $this->startDate,
                            $this->endDate,
                        ]
                    )
                    ->count();

                $row[] = $count;

                $totalCleaning += $count;
            }

            $row[] = $totalCleaning;

            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(
        Worksheet $sheet
    ) {

        $lastColumn =
            $sheet->getHighestColumn();

        $lastRow =
            $sheet->getHighestRow();

        // Merge title
        $sheet->mergeCells(
            "A1:{$lastColumn}1"
        );

        // Title style
        $sheet->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet->getStyle('A1')
            ->getAlignment()
            ->setHorizontal('center');

        // Header style (row 4)
        $sheet->getStyle(
            "A4:{$lastColumn}4"
        )
        ->getFont()
        ->setBold(true);

        $sheet->getStyle(
            "A4:{$lastColumn}4"
        )
        ->getAlignment()
        ->setHorizontal('center');

        // Border
        $sheet->getStyle(
            "A4:{$lastColumn}{$lastRow}"
        )
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(
            Border::BORDER_THIN
        );

        return [];
    }
}