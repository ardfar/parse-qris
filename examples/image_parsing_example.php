<?php
/**
 * Example: Parse QRIS from QR Image
 * 
 * This example demonstrates how to extract QRIS information directly from a QR code image file.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ardfar\ParseQris\QrisParser;
use Ardfar\ParseQris\Exceptions\QrisParseException;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

echo "🏪 QRIS Image Parser Example\n";
echo "============================\n\n";

// Sample QRIS string (this would normally come from a QR image)
$sampleQrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";

// 1. First, let's create a sample QR image (this would normally be provided by the user)
echo "📸 Creating sample QR code image...\n";

try {
    $options = new QROptions([
        'version'    => QRCode::VERSION_AUTO,
        'outputType' => QRCode::OUTPUT_IMAGICK,
        'eccLevel'   => QRCode::ECC_L,
    ]);
    
    $qrCode = new QRCode($options);
    $qrImage = $qrCode->render($sampleQrisString);
    
    $imagePath = __DIR__ . '/../examples/sample_qr.png';
    file_put_contents($imagePath, $qrImage);
    
    echo "✅ QR image created at: {$imagePath}\n\n";
    
} catch (Exception $e) {
    echo "❌ Error creating QR image: " . $e->getMessage() . "\n";
    echo "   Continuing with manual example...\n\n";
    $imagePath = null;
}

// 2. Parse QRIS information from the QR image
if ($imagePath && file_exists($imagePath)) {
    echo "🔍 Parsing QRIS information from QR image...\n";
    
    try {
        $parser = new QrisParser();
        $qrisData = $parser->parseFromImage($imagePath);
        
        echo "✅ Successfully parsed QR code!\n\n";
        
        // Display merchant information
        echo "🏪 Merchant Information:\n";
        echo "   Name: " . ($qrisData->merchant_name ?: 'N/A') . "\n";
        echo "   City: " . ($qrisData->merchant_city ?: 'N/A') . "\n";
        echo "   Country: " . ($qrisData->country_code ?: 'N/A') . "\n";
        echo "   Postal Code: " . ($qrisData->merchant_postal_code ?: 'N/A') . "\n";
        
        // Display payment information
        echo "\n💳 Payment Information:\n";
        echo "   Currency: " . ($qrisData->transaction_currency ?: 'N/A') . "\n";
        echo "   MCC (Merchant Category): " . ($qrisData->mcc ?: 'N/A') . "\n";
        echo "   Amount: " . ($qrisData->transaction_amount ?: 'N/A') . "\n";
        
        // Display merchant account details
        if ($qrisData->merchant_information_26) {
            echo "\n📊 Primary Merchant Account (Tag 26):\n";
            foreach ($qrisData->merchant_information_26 as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                echo "   {$label}: {$value}\n";
            }
        }
        
        if ($qrisData->merchant_information_51) {
            echo "\n📋 Secondary Merchant Account (Tag 51):\n";
            foreach ($qrisData->merchant_information_51 as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                echo "   {$label}: {$value}\n";
            }
        }
        
        // Display additional data
        if ($qrisData->additional_data) {
            echo "\n🔧 Additional Data:\n";
            foreach ($qrisData->additional_data as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                echo "   {$label}: {$value}\n";
            }
        }
        
        // Convert to JSON for API usage
        echo "\n📄 JSON Output (for API usage):\n";
        echo $qrisData->toJson(JSON_PRETTY_PRINT) . "\n";
        
        // Clean up the demo image
        @unlink($imagePath);
        
    } catch (QrisParseException $e) {
        echo "❌ Error parsing QRIS: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Could not create demo QR image. \n";
    echo "   In a real application, you would provide an existing QR image file.\n";
}

echo "\n🎉 Example completed!\n";
echo "\nTo use this in your application:\n";
echo "1. Provide a QR code image file (jpg, png, gif, bmp)\n";
echo "2. Call \$parser->parseFromImage('/path/to/qr-image.jpg')\n";
echo "3. Access the parsed QRIS data from the returned QrisData object\n";