<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Additional product identifiers
            $table->string('internal_code', 100)->nullable()->after('sku');
            $table->string('serial_number', 100)->nullable()->after('internal_code');

            // Arabic fields
            $table->string('name_arabic')->nullable()->after('name');
            $table->text('description_arabic')->nullable()->after('description');
            $table->string('category_arabic', 100)->nullable()->after('category');
            $table->string('type_arabic', 100)->nullable()->after('category_arabic');

            // English type
            $table->string('type_english', 100)->nullable()->after('type_arabic');

            // Marketing & targeting
            $table->date('launch_date')->nullable()->after('type_english');
            $table->string('market_segment', 100)->nullable()->after('launch_date');
            $table->string('target_audience', 100)->nullable()->after('market_segment');

            // Product details
            $table->string('manufacturer')->nullable()->after('price');
            $table->string('weight', 50)->nullable()->after('manufacturer');
            $table->text('packaging_details')->nullable()->after('weight');
            $table->string('shelf_life', 100)->nullable()->after('packaging_details');

            // Barcodes
            $table->string('unit_barcode', 50)->nullable()->after('shelf_life');
            $table->string('box_barcode', 50)->nullable()->after('unit_barcode');
            $table->string('carton_barcode', 50)->nullable()->after('box_barcode');

            // Sales channel
            $table->string('selling_channel', 100)->nullable()->after('carton_barcode');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'internal_code',
                'serial_number',
                'name_arabic',
                'description_arabic',
                'category_arabic',
                'type_arabic',
                'type_english',
                'launch_date',
                'market_segment',
                'target_audience',
                'manufacturer',
                'weight',
                'packaging_details',
                'shelf_life',
                'unit_barcode',
                'box_barcode',
                'carton_barcode',
                'selling_channel',
            ]);
        });
    }
};
