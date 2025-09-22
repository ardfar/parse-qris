<?php

// Simple test runner to validate our implementation
// This runs basic tests without requiring full PHPUnit installation

// Try to load composer autoloader for full dependency access
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback to manual includes
    require_once __DIR__ . '/src/Data/QrisTag.php';
    require_once __DIR__ . '/src/Data/QrisData.php';
    require_once __DIR__ . '/src/Exceptions/QrisParseException.php';
    require_once __DIR__ . '/src/QrisParser.php';
}

use Ardfar\ParseQris\QrisParser;
use Ardfar\ParseQris\Data\QrisData;
use Ardfar\ParseQris\Exceptions\QrisParseException;

class SimpleTestRunner
{
    private int $tests = 0;
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        echo "🧪 Running QRIS Parser Tests\n";
        echo "============================\n\n";

        $this->testValidQrisString();
        $this->testMerchantInformation();
        $this->testAdditionalData();
        $this->testEmptyString();
        $this->testImageFileNotFound();
        $this->testImageParsing();
        $this->testDataConversion();
        $this->testValueDecoding();

        echo "\n📊 Test Results:\n";
        echo "================\n";
        echo "Total Tests: {$this->tests}\n";
        echo "✅ Passed: {$this->passed}\n";
        echo "❌ Failed: {$this->failed}\n";
        
        if ($this->failed === 0) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed.\n";
            exit(1);
        }
    }

    private function test(string $name, callable $testFn): void
    {
        $this->tests++;
        echo "🔸 {$name}... ";
        
        try {
            $testFn();
            echo "✅ PASS\n";
            $this->passed++;
        } catch (Exception $e) {
            echo "❌ FAIL: " . $e->getMessage() . "\n";
            $this->failed++;
        }
    }

    private function testValidQrisString(): void
    {
        $this->test("Parse Valid QRIS String", function () {
            $parser = new QrisParser();
            $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
            
            $result = $parser->parseFromString($qrisString);
            
            if (!($result instanceof QrisData)) {
                throw new Exception("Expected QrisData instance");
            }
            if ($result->payload_format_indicator !== '01') {
                throw new Exception("Expected payload_format_indicator to be '01'");
            }
            if ($result->point_of_initiation_method !== 'STATIC') {
                throw new Exception("Expected point_of_initiation_method to be 'STATIC'");
            }
            if ($result->merchant_name !== 'BIOLBE') {
                throw new Exception("Expected merchant_name to be 'BIOLBE'");
            }
            if ($result->transaction_currency !== 'RUPIAH') {
                throw new Exception("Expected transaction_currency to be 'RUPIAH'");
            }
        });
    }

    private function testMerchantInformation(): void
    {
        $this->test("Parse Merchant Information", function () {
            $parser = new QrisParser();
            $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
            
            $result = $parser->parseFromString($qrisString);
            
            if (!$result->merchant_information_26) {
                throw new Exception("Expected merchant_information_26 to be present");
            }
            if ($result->merchant_information_26['global_unique_identifier'] !== 'ID.CO.TELKOM.WWW') {
                throw new Exception("Expected correct global_unique_identifier in merchant_information_26");
            }
            if (!$result->merchant_information_51) {
                throw new Exception("Expected merchant_information_51 to be present");
            }
            if ($result->merchant_information_51['merchant_id'] !== 'ID1021125405972') {
                throw new Exception("Expected correct merchant_id in merchant_information_51");
            }
        });
    }

    private function testAdditionalData(): void
    {
        $this->test("Parse Additional Data", function () {
            $parser = new QrisParser();
            $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
            
            $result = $parser->parseFromString($qrisString);
            
            if (!$result->additional_data) {
                throw new Exception("Expected additional_data to be present");
            }
            if ($result->additional_data['reference_label'] !== '114793812') {
                throw new Exception("Expected correct reference_label");
            }
            if ($result->additional_data['terminal_label'] !== 'A03') {
                throw new Exception("Expected correct terminal_label");
            }
        });
    }

    private function testEmptyString(): void
    {
        $this->test("Handle Empty String", function () {
            $parser = new QrisParser();
            
            $exceptionThrown = false;
            try {
                $parser->parseFromString('');
            } catch (QrisParseException $e) {
                $exceptionThrown = true;
                if (strpos($e->getMessage(), 'Empty payload') === false) {
                    throw new Exception("Expected 'Empty payload' in exception message");
                }
            }
            
            if (!$exceptionThrown) {
                throw new Exception("Expected QrisParseException to be thrown");
            }
        });
    }

    private function testImageFileNotFound(): void
    {
        $this->test("Handle Image File Not Found", function () {
            $parser = new QrisParser();
            
            $exceptionThrown = false;
            try {
                $parser->parseFromImage('/nonexistent/path.jpg');
            } catch (QrisParseException $e) {
                $exceptionThrown = true;
                if (strpos($e->getMessage(), 'Image file not found') === false) {
                    throw new Exception("Expected 'Image file not found' in exception message");
                }
            }
            
            if (!$exceptionThrown) {
                throw new Exception("Expected QrisParseException to be thrown");
            }
        });
    }

    private function testImageParsing(): void
    {
        $this->test("Parse QRIS from Image", function () {
            // First, create a test QR image with QRIS data
            $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
            
            try {
                // Try to load QR generation library
                if (!class_exists('chillerlan\QRCode\QRCode')) {
                    throw new Exception("QR generation library not available - skipping image test");
                }
                
                $options = new chillerlan\QRCode\QROptions([
                    'version'    => chillerlan\QRCode\QRCode::VERSION_AUTO,
                    'outputType' => chillerlan\QRCode\QRCode::OUTPUT_IMAGICK,
                    'eccLevel'   => chillerlan\QRCode\QRCode::ECC_L,
                ]);
                
                $qrCode = new chillerlan\QRCode\QRCode($options);
                $qrData = $qrCode->render($qrisString);
                
                $testImagePath = '/tmp/test_qris_parsing.png';
                file_put_contents($testImagePath, $qrData);
                
                // Verify image was created
                if (!file_exists($testImagePath) || !getimagesize($testImagePath)) {
                    throw new Exception("Failed to create test QR image");
                }
                
                // Test parsing from image
                $parser = new QrisParser();
                $result = $parser->parseFromImage($testImagePath);
                
                // Verify the parsed data matches expected values
                if ($result->merchant_name !== 'BIOLBE') {
                    throw new Exception("Expected merchant name 'BIOLBE', got: " . $result->merchant_name);
                }
                
                if ($result->merchant_city !== 'KAB. MALANG') {
                    throw new Exception("Expected city 'KAB. MALANG', got: " . $result->merchant_city);
                }
                
                if (!$result->merchant_information_26 || $result->merchant_information_26['global_unique_identifier'] !== 'ID.CO.TELKOM.WWW') {
                    throw new Exception("Merchant information 26 parsing failed");
                }
                
                // Clean up test file
                @unlink($testImagePath);
                
            } catch (Exception $e) {
                // If QR generation fails, test the image not found functionality
                if (strpos($e->getMessage(), 'QR generation library not available') !== false) {
                    // Skip this test gracefully
                    echo " (SKIPPED - QR generation not available) ";
                    return;
                }
                throw $e;
            }
        });
    }

    private function testDataConversion(): void
    {
        $this->test("Data Conversion to Array and JSON", function () {
            $parser = new QrisParser();
            $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
            
            $result = $parser->parseFromString($qrisString);
            
            $array = $result->toArray();
            if (!is_array($array)) {
                throw new Exception("Expected toArray() to return array");
            }
            if (!isset($array['merchant_name'])) {
                throw new Exception("Expected merchant_name in array");
            }
            if ($array['merchant_name'] !== 'BIOLBE') {
                throw new Exception("Expected correct merchant_name in array");
            }
            
            $json = $result->toJson();
            if (!is_string($json)) {
                throw new Exception("Expected toJson() to return string");
            }
            
            $decoded = json_decode($json, true);
            if (!is_array($decoded)) {
                throw new Exception("Expected valid JSON");
            }
            if ($decoded['merchant_name'] !== 'BIOLBE') {
                throw new Exception("Expected correct merchant_name in JSON");
            }
        });
    }

    private function testValueDecoding(): void
    {
        $this->test("Value Decoding", function () {
            $parser = new QrisParser();
            $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
            
            $result = $parser->parseFromString($qrisString);
            
            // Test decoded values
            if ($result->point_of_initiation_method !== 'STATIC') {
                throw new Exception("Expected decoded point_of_initiation_method");
            }
            if ($result->transaction_currency !== 'RUPIAH') {
                throw new Exception("Expected decoded transaction_currency");
            }
            if ($result->tip_indicator !== 'INPUT_TIP') {
                throw new Exception("Expected decoded tip_indicator");
            }
        });
    }
}

$runner = new SimpleTestRunner();
$runner->run();