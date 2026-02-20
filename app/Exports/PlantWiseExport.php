<?php

namespace App\Exports;

use App\Models\Orders;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;

class PlantWiseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithStrictNullComparison
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
        return Orders::query()
            ->join('drivers', 'orders.driver_id', '=', 'drivers.id')
            ->join('plants', 'drivers.plant_id', '=', 'plants.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->selectRaw('
                plants.name as plant_name,
                COUNT(orders.id) as total_orders,
                SUM(orders.develivered_qty) as total_delivered
            ')
            ->groupBy('plants.name')
            ->orderBy('plants.name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Plant',
            'Total Order',
            'Total Jar Delivered',
        ];
    }

    public function map($order): array
    {
        return [
            $order->plant_name,
            (int) $order->total_orders,
            (int) $order->total_delivered,
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
