<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       $selectQuery = DB::table('jar_transport_logs as jtl')
            ->join('jar_transportations as jt', 'jt.id', '=', 'jtl.jar_transportation_id')
            ->leftJoin('jar_transport_driver_logs as jtdl', function ($join) {
                $join->on('jtdl.jar_transport_log_id', '=', 'jtl.id')
                    ->on('jtdl.action', '=', 'jtl.action');
            })
            ->whereNull('jtdl.id')
            ->select([
                'jt.driver_id',
                'jtl.id as jar_transport_log_id',
                'jtl.action',
                DB::raw("'pending' as status"),
                DB::raw("'' as remark"),
                'jtl.deleted_at',
                'jtl.created_at',
                'jtl.updated_at',
            ]);

        DB::table('jar_transport_driver_logs')->insertUsing(
            [
                'driver_id',
                'jar_transport_log_id',
                'action',
                'status',
                'remark',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            $selectQuery
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
