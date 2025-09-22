# Parse QRIS

A Laravel library for parsing QRIS (Quick Response Code Indonesian Standard) information from QR code images and strings.

## Features

- ✅ Parse QRIS information from decoded strings
- 🖼️ Interface for parsing from QR code images (requires additional QR decoder library)
- 📊 Extract comprehensive merchant information
- 💰 Transaction and payment details parsing
- 🏷️ Support for all standard QRIS tags
- 📱 Laravel integration with service provider and facade
- 🔍 Type-safe data structures

## Installation

Install via Composer:

```bash
composer require ardfar/parse-qris
```

For Laravel auto-discovery, the service provider will be automatically registered. For manual registration, add to `config/app.php`:

```php
'providers' => [
    // ...
    Ardfar\ParseQris\QrisServiceProvider::class,
],

'aliases' => [
    // ...
    'QrisParser' => Ardfar\ParseQris\Facades\QrisParser::class,
],
```

## Usage

### Using the Facade

```php
use QrisParser;

$qrisString = "00020101021126680016ID.CO.TELKOM.WWW...";
$qrisData = QrisParser::parseFromString($qrisString);

echo "Merchant: " . $qrisData->merchant_name;
echo "City: " . $qrisData->merchant_city;
echo "Currency: " . $qrisData->transaction_currency;
```

### Using the Parser Class Directly

```php
use Ardfar\ParseQris\QrisParser;

$parser = new QrisParser();
$qrisData = $parser->parseFromString($qrisString);

// Access parsed data
echo "Payload Format: " . $qrisData->payload_format_indicator;
echo "Point of Initiation: " . $qrisData->point_of_initiation_method;
echo "Merchant Name: " . $qrisData->merchant_name;
echo "Merchant City: " . $qrisData->merchant_city;
echo "Currency: " . $qrisData->transaction_currency;
echo "Country Code: " . $qrisData->country_code;
echo "MCC: " . $qrisData->mcc;

// Access merchant information
if ($qrisData->merchant_information_26) {
    echo "Global ID: " . $qrisData->merchant_information_26['global_unique_identifier'];
    echo "Merchant PAN: " . $qrisData->merchant_information_26['merchant_pan'];
    echo "Merchant ID: " . $qrisData->merchant_information_26['merchant_id'];
    echo "Criteria: " . $qrisData->merchant_information_26['merchant_criteria'];
}

// Access additional data
if ($qrisData->additional_data) {
    echo "Reference Label: " . $qrisData->additional_data['reference_label'];
    echo "Terminal Label: " . $qrisData->additional_data['terminal_label'];
}
```

### Converting to Array or JSON

```php
$qrisData = QrisParser::parseFromString($qrisString);

// Convert to array
$array = $qrisData->toArray();

// Convert to JSON
$json = $qrisData->toJson();
```

### Parsing from Images (Future Feature)

```php
try {
    $qrisData = QrisParser::parseFromImage('/path/to/qr-code-image.jpg');
} catch (QrisParseException $e) {
    echo "Error: " . $e->getMessage();
    // Currently requires manual QR decoding
    // You can use online tools or QR libraries to decode the image first
}
```

## Example Output

Given this QRIS string:
```
00020101021126680016ID.CO.TELKOM.WWW011893600898026635207502150001952663520750303UMI51440014ID.CO.QRIS.WWW0215ID10211254059720303UMI5204549953033605502015802ID5906BIOLBE6011KAB. MALANG610565168622005091147938120703A03630404CB
```

The parser will return:

```json
{
    "payload_format_indicator": "01",
    "point_of_initiation_method": "STATIC",
    "merchant_information_26": {
        "global_unique_identifier": "ID.CO.TELKOM.WWW",
        "merchant_pan": "936008980266352075",
        "merchant_id": "000195266352075",
        "merchant_criteria": "UMI"
    },
    "merchant_information_51": {
        "global_unique_identifier": "ID.CO.QRIS.WWW",
        "merchant_id": "ID1021125405972",
        "merchant_criteria": "UMI"
    },
    "mcc": "5499",
    "transaction_currency": "RUPIAH",
    "tip_indicator": "INPUT_TIP",
    "country_code": "ID",
    "merchant_name": "BIOLBE",
    "merchant_city": "KAB. MALANG",
    "merchant_postal_code": "65168",
    "additional_data": {
        "reference_label": "114793812",
        "terminal_label": "A03"
    },
    "crc": "04CB"
}
```

## QRIS Information Glossary

The library extracts the following information from QRIS codes:

- **Merchant Name & Location**: Business name, city, and postal code
- **National Merchant ID (NMID)**: Official merchant registration number
- **Merchant PAN**: Acquirer identification number
- **Merchant ID**: Internal merchant identifier within PJSP system
- **MCC (Merchant Category Code)**: Business type classification
- **Transaction Details**: Currency, amount, tip information
- **Additional Data**: Reference labels, terminal information, etc.

## Error Handling

The library throws `QrisParseException` for various error conditions:

- Invalid or empty QRIS data
- Image file not found
- Invalid image format
- QR code not found in image
- QR code reading errors

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- GD extension (for image processing)

## Image QR Code Reading

For full image QR code reading functionality, you'll need to install an additional QR code decoder library:

```bash
composer require khanamiryan/qrcode-detector-decoder
```

Then the `parseFromImage()` method will be able to automatically detect and decode QR codes from images.

## Testing

The library includes comprehensive tests. Run them with:

```bash
vendor/bin/phpunit
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- Inspired by [terryds/qris-decoder](https://github.com/terryds/qris-decoder)
- Built for the Laravel ecosystem
- Follows Indonesian QRIS standards
