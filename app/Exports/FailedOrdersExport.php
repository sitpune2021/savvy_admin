<?php

namespace App\Exports;

use App\Models\OrderGenerationFailure;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedOrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        protected $startDate,
        protected $endDate
    ) {
    }

    public function collection()
    {
        return OrderGenerationFailure::query()
            ->whereBetween('failure_date', [
                $this->startDate->toDateString(),
                $this->endDate->toDateString(),
            ])
            ->with([
                'customer',
                'contract.product',
                'shipping.Plant',
                'shipping.driver.routes',
            ])
            ->orderBy('attempted_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Sr. No.',
            'Attempt Date',
            'Contract ID',
            'Customer Code',
            'Customer Name',
            'Plant',
            'Route',
            'Driver',
            'Vehicle No.',
            'Shipping Address',
            'Product',
            'Ordered Quantity',
            'Contract Type',
            'Failure Reason',
            'Details',
            'Source',
            'Attempted At',
        ];
    }

    public function map($order): array
    {
        static $index = 0;


        return [
            ++$index,
            $order->failure_date?->format('d-m-Y'),
            $order->contract_id ?? '-',
            $order->customer?->code ?? '-',
            $order->customer?->name ?? '-',
            $order->shipping?->Plant?->name ?? '-',
            $order->shipping?->driver?->routes?->name ?? '-',
            $order->shipping?->driver?->name ?? '-',
            $order->shipping?->driver?->vehicle_no ?? '-',
            $order->shipping?->shipping_address ?? '-',
            $order->contract?->product?->name ?? '-',
            $order->contract?->quantity ?? 0,
            ucfirst($order->contract?->type ?? '-'),
            $order->reason,
            $order->details ?? '-',
            ucfirst(str_replace('_', ' ', $order->source)),
            $order->attempted_at?->format('d-m-Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
            },
        ];
    }
}
