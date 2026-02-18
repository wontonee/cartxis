<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('delivery_order_id')->nullable()->after('tracking_url');
            $table->string('delivery_shipment_id')->nullable()->after('delivery_order_id');
            $table->string('delivery_awb_code')->nullable()->after('delivery_shipment_id');
            $table->string('delivery_courier_name')->nullable()->after('delivery_awb_code');
            $table->string('delivery_status')->nullable()->after('delivery_courier_name');
            $table->json('delivery_tracking_payload')->nullable()->after('delivery_status');
            $table->timestamp('delivery_synced_at')->nullable()->after('delivery_tracking_payload');

            $table->index('delivery_order_id');
            $table->index('delivery_shipment_id');
            $table->index('delivery_awb_code');
            $table->index('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['delivery_order_id']);
            $table->dropIndex(['delivery_shipment_id']);
            $table->dropIndex(['delivery_awb_code']);
            $table->dropIndex(['delivery_status']);

            $table->dropColumn([
                'delivery_order_id',
                'delivery_shipment_id',
                'delivery_awb_code',
                'delivery_courier_name',
                'delivery_status',
                'delivery_tracking_payload',
                'delivery_synced_at',
            ]);
        });
    }
};
