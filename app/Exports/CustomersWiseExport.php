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
            ->leftJoin('shipping_addresses', 'orders.shipping_id', '=', 'shipping_addresses.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->selectRaw('
                customers.id as customer_id,
                customers.name as customer_name,
                shipping_addresses.id as shipping_id,
                shipping_addresses.shipping_address as shipping_address,
                COUNT(orders.id) as total_orders,
                SUM(orders.develivered_qty) as total_delivered,
                SUM(orders.return_qty) as total_empty
            ')
            ->groupBy('customers.id', 'customers.name', 'shipping_addresses.id', 'shipping_addresses.shipping_address')
            ->orderBy('customers.name')
            ->orderBy('shipping_addresses.shipping_address')
            ->get();
    }

    public function headings(): array
    {
        return [
          'Sr.No',
        'Customer Name',
        'Shipping Address',
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
        $order->shipping_address ?? '-',
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
