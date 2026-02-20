<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Maintenance;
use App\Models\Orders;
use Carbon\Carbon;
use App\Exports\DeliveryMisExport;
use App\Exports\DriverWiseExport;
use App\Exports\DateWiseExport;
use App\Exports\PlantWiseExport;
use App\Exports\CustomersWiseExport;

use Maatwebsite\Excel\Facades\Excel;


class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('pages.Settings.reports.index');
    }

    public function reports(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        return match ($request->report_type) {

            'fuel' => $this->fuelReport($request),

            'mis' => Excel::download(
                new DeliveryMisExport($startDate, $endDate),
                'Delivery_MIS_Report_' . now()->format('Ymd_His') . '.xlsx'
            ),

            'driver_wise_summery' => Excel::download(
                new DriverWiseExport($startDate, $endDate),
                'Driver_Wise_Summery_Report_' . now()->format('Ymd_His') . '.xlsx'
            ),
            
            'date_wise_summery' => Excel::download(
                new DateWiseExport($startDate, $endDate),
                'Date_Wise_Summery_Report_' . now()->format('Ymd_His') . '.xlsx'
            ),
            'plant_wise_summery' => Excel::download(
                new PlantWiseExport($startDate, $endDate),
                'Plant_Wise_Summery_Report_' . now()->format('Ymd_His') . '.xlsx'
            ),
            'customers_wise_summery' => Excel::download(
                new CustomersWiseExport($startDate, $endDate),
                'Customers_Wise_Summery_Report_' . now()->format('Ymd_His') . '.xlsx'
            ),
            

            default => response()->json([
                'message' => 'Invalid report type'
            ], 422),
        };
    }


    private function fuelReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $records = Maintenance::where('type', 'fuel')
            ->where('status', 'approved')
            ->whereRaw(
                "STR_TO_DATE(`date`, '%d-%m-%Y') BETWEEN ? AND ?",
                [$request->start_date, $request->end_date]
            )
            ->with(['driverTrash.plants'])
            ->get();

        if ($records->isEmpty()) {
            return response()->json(['message' => 'No fuel records found'], 404);
        }

        $plants = [];
        $grandTotal = 0;

        foreach ($records as $row) {
            $plantId   = $row->driverTrash->plants->id ?? 0;
            $plantName = $row->driverTrash->plants->name ?? 'Unknown';

            $driverId   = $row->driverTrash->id ?? 0;
            $driverName = $row->driverTrash->name ?? '-';

            $liters = (float) $row->description;
            $amount = (float) $row->amount;

            // Init plant
            if (!isset($plants[$plantId])) {
                $plants[$plantId] = [
                    'name' => $plantName,
                    'total_amount' => 0,
                    'drivers' => []
                ];
            }

            // Init driver
            if (!isset($plants[$plantId]['drivers'][$driverId])) {
                $plants[$plantId]['drivers'][$driverId] = [
                    'name' => $driverName,
                    'liters' => 0,
                    'amount' => 0,
                ];
            }

            // Add totals
            $plants[$plantId]['drivers'][$driverId]['liters'] += $liters;
            $plants[$plantId]['drivers'][$driverId]['amount'] += $amount;

            $plants[$plantId]['total_amount'] += $amount;
            $grandTotal += $amount;
        }

        $pdf = Pdf::loadView('pdf.fuel', [
            'plants'      => $plants,
            'grandTotal' => $grandTotal,
            'startDate'  => $request->start_date,
            'endDate'    => $request->end_date,
        ])->setPaper('a4', 'landscape');

        $month = Carbon::parse($request->start_date)->format('M_Y');
        $time  = Carbon::now()->format('His');

        $fileName = "Fuel_Report_{$month}_{$time}.pdf";

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
