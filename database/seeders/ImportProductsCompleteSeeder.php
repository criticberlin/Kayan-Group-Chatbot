<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\ImportRow;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ImportProductsCompleteSeeder extends Seeder
{
    public function run()
    {
        // Find the completed import (import #4)
        $import = Import::find(4);

        if (!$import) {
            $this->command->error('Import #4 not found!');
            return;
        }

        // Get all import rows
        $importRows = ImportRow::where('import_id', $import->id)
            ->orderBy('id')
            ->get();

        if ($importRows->isEmpty()) {
            $this->command->error('No import rows found!');
            return;
        }

        // Get header row
        $headerRow = $importRows->first();
        $headers = $headerRow->raw_data;

        $this->command->info("CSV Headers: " . implode(', ', array_filter($headers)));
        $this->command->info("Found {$importRows->count()} total rows (including header)");

        // Clear existing products
        Product::truncate();

        $imported = 0;
        $failed = 0;

        // Skip first row (header) and process data rows
        foreach ($importRows->skip(1) as $row) {
            try {
                $data = $row->raw_data;

                // Map CSV columns to database fields
                $productData = [];
                foreach ($headers as $index => $header) {
                    if ($header && isset($data[$index])) {
                        $productData[$header] = $data[$index];
                    }
                }

                // Create product with ALL field mapping
                Product::create([
                    'import_id' => $import->id,
                    'department_id' => $import->department_id,

                    // Identifiers
                    'sku' => $productData['product_id'] ?? null,
                    'internal_code' => $productData['internal_code_at_kayan_group_system'] ?? null,
                    'serial_number' => $productData['serial_number'] ?? null,

                    // Names & descriptions
                    'name' => $productData['brand_name_english'] ?? $productData['brand_name_arabic'] ?? 'Unnamed Product',
                    'name_arabic' => $productData['brand_name_arabic'] ?? null,
                    'description' => $productData['description_english'] ?? null,
                    'description_arabic' => $productData['description_arabic'] ?? null,

                    // Categories & types
                    'category' => $productData['category_english'] ?? null,
                    'category_arabic' => $productData['category_arabic'] ?? null,
                    'type_english' => $productData['Type_english'] ?? null,
                    'type_arabic' => $productData['Type_arabic'] ?? null,

                    // Marketing data
                    'launch_date' => $this->parseDate($productData['launch_date'] ?? null),
                    'market_segment' => $productData['market_segment'] ?? null,
                    'target_audience' => $productData['target_audience'] ?? null,

                    // Pricing
                    'price' => $this->parsePrice($productData['consumer_price_per_unit'] ?? 0),
                    'currency' => 'EGP',
                    'stock_quantity' => 0,

                    // Product details
                    'manufacturer' => $productData['manufacturer'] ?? null,
                    'weight' => $productData['wight/unit'] ?? null,
                    'packaging_details' => $productData['packaging_details'] ?? null,
                    'shelf_life' => $productData['shelf_life'] ?? null,

                    // Barcodes
                    'unit_barcode' => $productData['unit_barcode'] ?? null,
                    'box_barcode' => $productData['box_barcode'] ?? null,
                    'carton_barcode' => $productData['carton_barcode'] ?? null,

                    // Sales
                    'selling_channel' => $productData['selling_channel'] ?? null,

                    // Status
                    'is_active' => ($productData['is_active'] ?? 'yes') === 'yes',

                    // Audit
                    'created_by' => $import->user_id,
                    'updated_by' => $import->user_id,
                ]);

                $imported++;

            } catch (\Exception $e) {
                $failed++;
                $this->command->warn("Failed to import row {$row->id}: " . $e->getMessage());
            }
        }

        // Update import status
        $import->update([
            'status' => 'completed',
            'processed_rows' => $imported,
            'failed_rows' => $failed,
            'completed_at' => now(),
        ]);

        $this->command->info("Import completed!");
        $this->command->info("Imported: {$imported} products");
        $this->command->info("Failed: {$failed} products");
    }

    private function parsePrice($value)
    {
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        return $cleaned ? (float) $cleaned : 0;
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
