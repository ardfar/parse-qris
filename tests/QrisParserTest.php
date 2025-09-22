<?php

namespace Ardfar\ParseQris\Tests;

use Ardfar\ParseQris\QrisParser;
use Ardfar\ParseQris\Data\QrisData;
use Ardfar\ParseQris\Exceptions\QrisParseException;
use PHPUnit\Framework\TestCase;

class QrisParserTest extends TestCase
{
    private QrisParser $parser;

    protected function setUp(): void
    {
        $this->parser = new QrisParser();
    }

    public function testParseValidQrisString(): void
    {
        $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
        
        $result = $this->parser->parseFromString($qrisString);
        
        $this->assertInstanceOf(QrisData::class, $result);
        $this->assertEquals('01', $result->payload_format_indicator);
        $this->assertEquals('STATIC', $result->point_of_initiation_method);
        $this->assertEquals('5499', $result->mcc);
        $this->assertEquals('RUPIAH', $result->transaction_currency);
        $this->assertEquals('INPUT_TIP', $result->tip_indicator);
        $this->assertEquals('ID', $result->country_code);
        $this->assertEquals('BIOLBE', $result->merchant_name);
        $this->assertEquals('KAB. MALANG', $result->merchant_city);
        $this->assertEquals('65168', $result->merchant_postal_code);
        $this->assertEquals('04CB', $result->crc);
    }

    public function testParseMerchantInformation(): void
    {
        $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
        
        $result = $this->parser->parseFromString($qrisString);
        
        $this->assertNotNull($result->merchant_information_26);
        $this->assertEquals('ID.CO.TELKOM.WWW', $result->merchant_information_26['global_unique_identifier']);
        $this->assertEquals('936008980266352075', $result->merchant_information_26['merchant_pan']);
        $this->assertEquals('000195266352075', $result->merchant_information_26['merchant_id']);
        $this->assertEquals('UMI', $result->merchant_information_26['merchant_criteria']);

        $this->assertNotNull($result->merchant_information_51);
        $this->assertEquals('ID.CO.QRIS.WWW', $result->merchant_information_51['global_unique_identifier']);
        $this->assertEquals('ID1021125405972', $result->merchant_information_51['merchant_id']);
        $this->assertEquals('UMI', $result->merchant_information_51['merchant_criteria']);
    }

    public function testParseAdditionalData(): void
    {
        $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
        
        $result = $this->parser->parseFromString($qrisString);
        
        $this->assertNotNull($result->additional_data);
        $this->assertEquals('114793812', $result->additional_data['reference_label']);
        $this->assertEquals('A03', $result->additional_data['terminal_label']);
    }

    public function testParseEmptyString(): void
    {
        $this->expectException(QrisParseException::class);
        $this->expectExceptionMessage('Invalid QRIS data: Empty payload');
        
        $this->parser->parseFromString('');
    }

    public function testParseFromImageThrowsException(): void
    {
        $this->expectException(QrisParseException::class);
        $this->expectExceptionMessage('Image file not found');
        
        $this->parser->parseFromImage('/non/existent/path.jpg');
    }

    public function testParseFromImageSuccess(): void
    {
        // Skip this test if QR generation is not available
        if (!class_exists('chillerlan\QRCode\QRCode')) {
            $this->markTestSkipped('QR generation library not available');
        }

        $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
        
        // Create a temporary QR code image
        $options = new \chillerlan\QRCode\QROptions([
            'version'    => \chillerlan\QRCode\QRCode::VERSION_AUTO,
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGICK,
            'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_L,
        ]);
        
        $qrCode = new \chillerlan\QRCode\QRCode($options);
        $qrData = $qrCode->render($qrisString);
        
        $tempImagePath = sys_get_temp_dir() . '/test_qr_' . uniqid() . '.png';
        file_put_contents($tempImagePath, $qrData);
        
        try {
            // Test that we can parse the image
            $result = $this->parser->parseFromImage($tempImagePath);
            
            // Verify the parsed data
            $this->assertEquals('BIOLBE', $result->merchant_name);
            $this->assertEquals('KAB. MALANG', $result->merchant_city);
            $this->assertEquals('ID', $result->country_code);
            $this->assertNotNull($result->merchant_information_26);
            $this->assertEquals('ID.CO.TELKOM.WWW', $result->merchant_information_26['global_unique_identifier']);
            
        } finally {
            // Clean up the temporary file
            @unlink($tempImagePath);
        }
    }

    public function testQrisDataToArray(): void
    {
        $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
        
        $result = $this->parser->parseFromString($qrisString);
        $array = $result->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('payload_format_indicator', $array);
        $this->assertArrayHasKey('merchant_name', $array);
        $this->assertArrayHasKey('merchant_information_26', $array);
        $this->assertEquals('BIOLBE', $array['merchant_name']);
    }

    public function testQrisDataToJson(): void
    {
        $qrisString = "00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB";
        
        $result = $this->parser->parseFromString($qrisString);
        $json = $result->toJson();
        
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('BIOLBE', $decoded['merchant_name']);
    }
}