<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('shiprocket_order_id')->nullable()->after('tracking_url');
            $table->string('shiprocket_shipment_id')->nullable()->after('shiprocket_order_id');
            $table->string('shiprocket_awb_code')->nullable()->after('shiprocket_shipment_id');
            $table->string('shiprocket_courier_name')->nullable()->after('shiprocket_awb_code');
            $table->string('shiprocket_status')->nullable()->after('shiprocket_courier_name');
            $table->json('shiprocket_tracking_payload')->nullable()->after('shiprocket_status');
            $table->timestamp('shiprocket_synced_at')->nullable()->after('shiprocket_tracking_payload');

            $table->index('shiprocket_order_id');
            $table->index('shiprocket_shipment_id');
            $table->index('shiprocket_awb_code');
            $table->index('shiprocket_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['shiprocket_order_id']);
            $table->dropIndex(['shiprocket_shipment_id']);
            $table->dropIndex(['shiprocket_awb_code']);
            $table->dropIndex(['shiprocket_status']);

            $table->dropColumn([
                'shiprocket_order_id',
                'shiprocket_shipment_id',
                'shiprocket_awb_code',
                'shiprocket_courier_name',
                'shiprocket_status',
                'shiprocket_tracking_payload',
                'shiprocket_synced_at',
            ]);
        });
    }
};
