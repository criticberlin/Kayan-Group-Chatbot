<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\ImportRow;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ImportProductsWithCorrectMappingSeeder extends Seeder
{
    public function run()
    {
        // Find the completed import (import #4)
        $import = Import::find(4);

        if (!$import) {
            $this->command->error('Import #4 not found!');
            return;
        }

        // Get all import rows (skip the header row which is the first one)
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

                // Create product with correct field mapping
                Product::create([
                    'import_id' => $import->id,
                    'department_id' => $import->department_id,
                    'sku' => $productData['product_id'] ?? $productData['internal_code_at_kayan_group_system'] ?? null,
                    'name' => $productData['brand_name_english'] ?? $productData['brand_name_arabic'] ?? 'Unnamed Product',
                    'description' => $productData['description_english'] ?? $productData['description_arabic'] ?? null,
                    'category' => $productData['category_english'] ?? $productData['category_arabic'] ?? null,
                    'price' => $this->parsePrice($productData['consumer_price_per_unit'] ?? 0),
                    'currency' => 'EGP',
                    'stock_quantity' => 0, // Not in CSV
                    'specifications' => json_encode([
                        'type' => $productData['Type_english'] ?? null,
                        'manufacturer' => $productData['manufacturer'] ?? null,
                        'weight' => $productData['wight/unit'] ?? null,
                        'packaging' => $productData['packaging_details'] ?? null,
                        'shelf_life' => $productData['shelf_life'] ?? null,
                        'unit_barcode' => $productData['unit_barcode'] ?? null,
                        'box_barcode' => $productData['box_barcode'] ?? null,
                        'carton_barcode' => $productData['carton_barcode'] ?? null,
                        'market_segment' => $productData['market_segment'] ?? null,
                        'target_audience' => $productData['target_audience'] ?? null,
                        'selling_channel' => $productData['selling_channel'] ?? null,
                    ]),
                    'is_active' => ($productData['is_active'] ?? 'yes') === 'yes',
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
        // Remove any currency symbols and convert to float
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        return $cleaned ? (float) $cleaned : 0;
    }
}
