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

class CustomersWiseExport implements FromCollection,WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithStrictNullComparison

{
    protected $startDate;
    protected $endDate;
    private $srNo = 1;

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
        ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->selectRaw('
                customers.name as customer_name,
                COUNT(orders.id) as total_orders,
                SUM(orders.develivered_qty) as total_delivered,
                SUM(orders.return_qty) as total_empty
            ')
            ->groupBy('customers.name')
            ->orderBy('customers.name')
            ->get();
    }

    public function headings(): array
    {
        return [
          'Sr.No',
        'Customer Name',
        'Total Orders',
        'Total Jars Delivered',
        'Empty Jars',
        ];
    }

    public function map($order): array
    {
        return [
        $this->srNo++,
        $order->customer_name,
        (int) $order->total_orders,
        (int) $order->total_delivered,
        (int) $order->total_empty,
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
