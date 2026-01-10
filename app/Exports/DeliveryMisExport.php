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
use Maatwebsite\Excel\Events\AfterSheet;

class DeliveryMisExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function collection()
    {
        return Orders::whereHas('drivers')
            ->with(['customers', 'drivers.plants'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Plant',
            'Driver ID',
            'Driver Name',
            'Vehicle No',
            'Customer Name',
            'Customer Code',
            'Order ID',
            'Jar Quantity',
            'Empty Jars Collected',
            'Batch No',
            'Delivery Status',
            'Remarks',
        ];
    }

    public function map($order): array
    {
        return [
            $order->created_at->format('d-m-Y'),
            $order->drivers->plants->name ?? '-',
            $order->drivers->id ?? '-',
            $order->drivers->name ?? '-',
            $order->drivers->vehicle_no ?? '-',
            $order->customers->name ?? '-',
            $order->customers->code ?? '-',
            $order->id,
            $order->develivered_qty,
            $order->return_qty,
            $order->batch_no ?? '#'.$order->id ,
            ucfirst($order->status),
            $order->remarks ?? '-',
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
