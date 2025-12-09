<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ImportRow;
use Illuminate\Database\Seeder;

class ViewProductDataSeeder extends Seeder
{
    public function run()
    {
        // Check first product
        $product = Product::first();
        $this->command->info("First Product Data:");
        $this->command->info(json_encode($product->toArray(), JSON_PRETTY_PRINT));

        // Check first import row
        $row = ImportRow::where('import_id', 4)->first();
        $this->command->info("\nFirst Import Row Data:");
        $this->command->info(json_encode($row->raw_data, JSON_PRETTY_PRINT));
    }
}
