<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\ImportRow;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ImportProductsDirectlySeeder extends Seeder
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
        $importRows = ImportRow::where('import_id', $import->id)->get();

        if ($importRows->isEmpty()) {
            $this->command->error('No import rows found!');
            return;
        }

        $this->command->info("Found {$importRows->count()} rows to import");

        $imported = 0;
        $failed = 0;

        foreach ($importRows as $row) {
            try {
                $data = $row->raw_data;

                // Create product from import row data
                Product::create([
                    'import_id' => $import->id,
                    'department_id' => $import->department_id,
                    'sku' => $data['sku'] ?? null,
                    'name' => $data['name'] ?? $data['product_name'] ?? 'Unknown',
                    'description' => $data['description'] ?? null,
                    'category' => $data['category'] ?? null,
                    'price' => $data['price'] ?? 0,
                    'currency' => $data['currency'] ?? 'USD',
                    'stock_quantity' => $data['stock_quantity'] ?? $data['stock'] ?? 0,
                    'specifications' => isset($data['specifications']) ? json_decode($data['specifications'], true) : null,
                    'is_active' => true,
                    'created_by' => $import->user_id,
                    'updated_by' => $import->user_id,
                ]);

                $imported++;

                // Update import row status
                $row->update(['status' => 'completed']);

            } catch (\Exception $e) {
                $failed++;
                $row->update([
                    'status' => 'failed',
                    'validation_errors' => [$e->getMessage()],
                ]);
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
}
