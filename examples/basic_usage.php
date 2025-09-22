<?php

/**
 * Example usage of the Parse QRIS library
 * This demonstrates how to use the library to parse QRIS information
 */

require_once __DIR__ . '/../src/Data/QrisTag.php';
require_once __DIR__ . '/../src/Data/QrisData.php';
require_once __DIR__ . '/../src/Exceptions/QrisParseException.php';
require_once __DIR__ . '/../src/QrisParser.php';

use Ardfar\ParseQris\QrisParser;
use Ardfar\ParseQris\Exceptions\QrisParseException;

echo "🏪 QRIS Parser Example\n";
echo "=====================\n\n";

// Example QRIS strings for testing
$examples = [
    [
        'name' => 'Sample Merchant (BIOLBE)',
        'qris' => "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB"
    ]
];

$parser = new QrisParser();

foreach ($examples as $index => $example) {
    echo "📋 Example " . ($index + 1) . ": " . $example['name'] . "\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $result = $parser->parseFromString($example['qris']);
        
        // Basic merchant information
        echo "🏪 Merchant Information:\n";
        echo "   Name: " . ($result->merchant_name ?: 'N/A') . "\n";
        echo "   City: " . ($result->merchant_city ?: 'N/A') . "\n";
        echo "   Postal Code: " . ($result->merchant_postal_code ?: 'N/A') . "\n";
        echo "   Country: " . ($result->country_code ?: 'N/A') . "\n";
        echo "   MCC: " . ($result->mcc ?: 'N/A') . "\n";
        
        // Payment information
        echo "\n💰 Payment Information:\n";
        echo "   Currency: " . ($result->transaction_currency ?: 'N/A') . "\n";
        echo "   Tip Indicator: " . ($result->tip_indicator ?: 'N/A') . "\n";
        echo "   Initiation Method: " . ($result->point_of_initiation_method ?: 'N/A') . "\n";
        
        // Transaction amount (if present)
        if ($result->transaction_amount) {
            echo "   Amount: " . number_format($result->transaction_amount, 2) . "\n";
        }
        
        // Merchant details from different information blocks
        $merchantInfoBlocks = [
            '26' => $result->merchant_information_26,
            '27' => $result->merchant_information_27,
            '50' => $result->merchant_information_50,
            '51' => $result->merchant_information_51,
        ];
        
        foreach ($merchantInfoBlocks as $blockNum => $info) {
            if ($info) {
                echo "\n📊 Merchant Info Block {$blockNum}:\n";
                echo "   Global ID: " . ($info['global_unique_identifier'] ?? 'N/A') . "\n";
                if (isset($info['merchant_pan'])) {
                    echo "   Merchant PAN: " . $info['merchant_pan'] . "\n";
                }
                echo "   Merchant ID: " . ($info['merchant_id'] ?? 'N/A') . "\n";
                echo "   Criteria: " . ($info['merchant_criteria'] ?? 'N/A') . "\n";
            }
        }
        
        // Additional data
        if ($result->additional_data) {
            echo "\n📄 Additional Data:\n";
            foreach ($result->additional_data as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                echo "   {$label}: {$value}\n";
            }
        }
        
        // Technical details
        echo "\n🔧 Technical Details:\n";
        echo "   Payload Format: " . ($result->payload_format_indicator ?: 'N/A') . "\n";
        echo "   CRC: " . ($result->crc ?: 'N/A') . "\n";
        
        echo "\n✅ Parsing completed successfully!\n";
        
    } catch (QrisParseException $e) {
        echo "❌ Error parsing QRIS: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n\n";
}

// Demonstrate array and JSON conversion
echo "📤 Data Export Examples:\n";
echo str_repeat("-", 30) . "\n";

try {
    $result = $parser->parseFromString($examples[0]['qris']);
    
    echo "\n🔗 Array Export:\n";
    $array = $result->toArray();
    echo "Array keys: " . implode(', ', array_keys($array)) . "\n";
    
    echo "\n📋 JSON Export (formatted):\n";
    echo $result->toJson() . "\n";
    
} catch (QrisParseException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Demonstrate error handling
echo "\n❌ Error Handling Examples:\n";
echo str_repeat("-", 30) . "\n";

// Test with empty string
try {
    $parser->parseFromString('');
} catch (QrisParseException $e) {
    echo "✓ Empty string error: " . $e->getMessage() . "\n";
}

// Test with invalid file path
try {
    $parser->parseFromImage('/nonexistent/path/image.jpg');
} catch (QrisParseException $e) {
    echo "✓ File not found error: " . $e->getMessage() . "\n";
}

echo "\n🎉 All examples completed!\n";