<?php
/**
 * Test script to check product stock in database
 * 
 * Usage:
 * 1. Open browser and go to: http://your-domain/test_product_stock.php?id=PRODUCT_ID
 * 2. Replace PRODUCT_ID with the actual product ID you're testing
 * 
 * This will show you exactly what's stored in the database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Get product ID from URL
$productId = $_GET['id'] ?? null;

if (!$productId) {
    die('Please provide product ID in URL: ?id=123');
}

// Get product from database
$product = \App\Model\Product::find($productId);

if (!$product) {
    die('Product not found with ID: ' . $productId);
}

echo "<h1>Product Stock Debug</h1>";
echo "<h2>Product ID: {$product->id}</h2>";
echo "<h3>Product Name: {$product->name}</h3>";

echo "<hr>";

echo "<h3>Total Stock (from database):</h3>";
echo "<pre>";
echo "Value: " . $product->total_stock . "\n";
echo "Type: " . gettype($product->total_stock) . "\n";
echo "</pre>";

echo "<hr>";

echo "<h3>Variations (from database):</h3>";
echo "<pre>";
echo "Raw JSON:\n";
echo $product->variations . "\n\n";

echo "Decoded Array:\n";
$variations = json_decode($product->variations, true);
print_r($variations);
echo "</pre>";

echo "<hr>";

echo "<h3>Choice Options (from database):</h3>";
echo "<pre>";
echo "Raw JSON:\n";
echo $product->choice_options . "\n\n";

echo "Decoded Array:\n";
$choiceOptions = json_decode($product->choice_options, true);
print_r($choiceOptions);
echo "</pre>";

echo "<hr>";

echo "<h3>Stock Analysis:</h3>";
echo "<pre>";
if ($variations && is_array($variations)) {
    $totalCalculated = 0;
    foreach ($variations as $variation) {
        echo "Variant: " . $variation['type'] . "\n";
        echo "  Price: " . $variation['price'] . "\n";
        echo "  Stock: " . $variation['stock'] . " (type: " . gettype($variation['stock']) . ")\n";
        $totalCalculated += (int)$variation['stock'];
        echo "\n";
    }
    echo "Total Stock (calculated from variants): " . $totalCalculated . "\n";
    echo "Total Stock (from database field): " . $product->total_stock . "\n";
    
    if ($totalCalculated != $product->total_stock) {
        echo "\n⚠️ WARNING: Stock mismatch!\n";
        echo "Database says: " . $product->total_stock . "\n";
        echo "Variants sum to: " . $totalCalculated . "\n";
    } else {
        echo "\n✅ Stock matches!\n";
    }
} else {
    echo "No variations found. This is a simple product.\n";
    echo "Stock: " . $product->total_stock . "\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='?id={$productId}'>Refresh</a></p>";

