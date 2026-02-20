<?php

namespace App\Exports;

use App\Models\Drivers;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DriverWiseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithStrictNullComparison   
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
       

        return Drivers::with([
            'plants',
            'orders' => function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->orderBy('created_at');
            },
            'jarMaintance' => function ($q) {
                $q->whereBetween('date', [$this->startDate, $this->endDate]);
            }
        ])
        ->whereHas('orders', function ($q) {
            $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
        })
        ->orderBy('created_at')
        ->get();

            
    }

    public function headings(): array
    {
        return [
            'Sr. No',
            'Date',
            'Plant Name',
            'Driver Name',
            'Vehicle Name',
            'Total Order',
            'Total Jar  Delivered',
            'Empty Jars',
            'Leakage Jars',
            'Green Jars',
        ];
    }

    public function map($driver): array
    {
        static $index = 1;

        $greenJarQty = $driver->jarMaintance
            ->where('type', 'green-jar')
            ->sum('qty');

        $leackJarQty = $driver->jarMaintance
            ->where('type', 'leacked-jar')
            ->sum('qty');

        $greenJarQty = $greenJarQty === null ? 0 : (int) $greenJarQty;
        $leackJarQty = $leackJarQty === null ? 0 : (int) $leackJarQty;

       
        return [
            $index++,
            $this->startDate->format('d-m-Y') . ' to ' . $this->endDate->format('d-m-Y'),
            $driver->plants->name ?? '-',
            $driver->name ?? '-',
            $driver->vehicle_name ?? '-',
            $driver->orders->count(),
            $driver->orders->sum('develivered_qty'),
            $driver->orders->sum('return_qty'),
            $greenJarQty,
            $leackJarQty,
        ];
    }


    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ // Header row
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('A2');

                $highestColumn = $event->sheet->getDelegate()->getHighestColumn();
                $highestRow    = $event->sheet->getDelegate()->getHighestRow();

                // Enable filter on full header range
                $event->sheet->getDelegate()
                    ->setAutoFilter("A1:{$highestColumn}{$highestRow}");
            },
        ];
    }
    
}
