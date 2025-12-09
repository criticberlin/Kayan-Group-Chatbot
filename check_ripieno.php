<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Searching for Ripieno products...\n\n";

$products = App\Models\Product::where('name', 'like', '%ripieno%')
    ->orWhere('name', 'like', '%Ripieno%')
    ->get();

echo "Found " . $products->count() . " products:\n\n";

foreach ($products as $product) {
    echo "Name: " . $product->name . "\n";
    echo "Price: " . $product->currency . " " . $product->price . "\n";
    echo "Category: " . $product->category . "\n";
    echo "---\n";
}
