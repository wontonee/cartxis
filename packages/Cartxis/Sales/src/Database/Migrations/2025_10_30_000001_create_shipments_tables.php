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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('shipment_number')->unique();
            $table->string('status', 50)->default('pending');
            $table->string('carrier', 100)->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('tracking_url')->nullable();

            // Shiprocket integration fields
            $table->string('shiprocket_order_id')->nullable();
            $table->string('shiprocket_shipment_id')->nullable();
            $table->string('shiprocket_awb_code')->nullable();
            $table->string('shiprocket_courier_name')->nullable();
            $table->string('shiprocket_status')->nullable();
            $table->json('shiprocket_tracking_payload')->nullable();
            $table->timestamp('shiprocket_synced_at')->nullable();

            // Delivery provider integration fields
            $table->string('delivery_order_id')->nullable();
            $table->string('delivery_shipment_id')->nullable();
            $table->string('delivery_awb_code')->nullable();
            $table->string('delivery_courier_name')->nullable();
            $table->string('delivery_status')->nullable();
            $table->json('delivery_tracking_payload')->nullable();
            $table->timestamp('delivery_synced_at')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('shipment_number');
            $table->index('order_id');
            $table->index('tracking_number');
            $table->index('status');
            $table->index('shipped_at');
            $table->index('shiprocket_order_id');
            $table->index('shiprocket_shipment_id');
            $table->index('shiprocket_awb_code');
            $table->index('shiprocket_status');
            $table->index('delivery_order_id');
            $table->index('delivery_shipment_id');
            $table->index('delivery_awb_code');
            $table->index('delivery_status');
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
            
            $table->index('shipment_id');
            $table->index('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
