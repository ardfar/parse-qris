<?php

namespace Ardfar\ParseQris;

use Ardfar\ParseQris\Data\QrisData;
use Ardfar\ParseQris\Data\QrisTag;
use Ardfar\ParseQris\Exceptions\QrisParseException;
use Zxing\QrReader;

class QrisParser
{
    private array $tags;

    public function __construct()
    {
        $this->tags = QrisTag::getAllTags();
    }

    /**
     * Parse QRIS information from an image file
     * Uses khanamiryan/qrcode-detector-decoder library for QR code detection and decoding
     */
    public function parseFromImage(string $imagePath): QrisData
    {
        if (!file_exists($imagePath)) {
            throw QrisParseException::imageNotFound($imagePath);
        }

        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            throw QrisParseException::invalidImageFormat();
        }

        try {
            // Use the QrReader library to decode QR code from the image
            $qrReader = new QrReader($imagePath, QrReader::SOURCE_TYPE_FILE);
            $qrText = $qrReader->text();

            if ($qrText === false || $qrText === null) {
                $error = $qrReader->getError();
                $errorMessage = $error ? $error->getMessage() : 'No QR code found in the image';
                throw QrisParseException::qrCodeNotFound();
            }

            // Parse the decoded QR text as QRIS string
            return $this->parseFromString($qrText);
            
        } catch (QrisParseException $e) {
            // Re-throw QRIS exceptions as-is
            throw $e;
        } catch (\Exception $e) {
            throw QrisParseException::qrCodeReadError($e->getMessage());
        }
    }

    /**
     * Parse QRIS information from a decoded string
     */
    public function parseFromString(string $qrisPayload): QrisData
    {
        if (empty($qrisPayload)) {
            throw QrisParseException::invalidQrisData('Empty payload');
        }

        $data = [];
        $this->parse($this->tags, $qrisPayload, null, $data);
        
        return $this->createQrisData($data);
    }

    /**
     * Create QrisData object from parsed array
     */
    private function createQrisData(array $data): QrisData
    {
        return new QrisData(
            payload_format_indicator: $data['payload_format_indicator'] ?? null,
            point_of_initiation_method: $data['point_of_initiation_method'] ?? null,
            merchant_principal_visa: $data['merchant_principal_visa'] ?? null,
            merchant_principal_mastercard: $data['merchant_principal_mastercard'] ?? null,
            merchant_information_26: $data['merchant_information_26'] ?? null,
            merchant_information_27: $data['merchant_information_27'] ?? null,
            merchant_information_50: $data['merchant_information_50'] ?? null,
            merchant_information_51: $data['merchant_information_51'] ?? null,
            mcc: $data['mcc'] ?? null,
            transaction_currency: $data['transaction_currency'] ?? null,
            transaction_amount: $data['transaction_amount'] ?? null,
            tip_indicator: $data['tip_indicator'] ?? null,
            fixed_tip_amount: $data['fixed_tip_amount'] ?? null,
            percentage_tip_amount: $data['percentage_tip_amount'] ?? null,
            country_code: $data['country_code'] ?? null,
            merchant_name: $data['merchant_name'] ?? null,
            merchant_city: $data['merchant_city'] ?? null,
            merchant_postal_code: $data['merchant_postal_code'] ?? null,
            additional_data: $data['additional_data'] ?? null,
            crc: $data['crc'] ?? null
        );
    }

    /**
     * Parse the QRIS payload recursively
     */
    private function parse(array $tags, string $qrPayload, ?string $parent, array &$data): void
    {
        $index = 0;

        while ($index < strlen($qrPayload)) {
            if ($index + 4 > strlen($qrPayload)) {
                break; // Not enough characters for tag+length
            }

            $qr = substr($qrPayload, $index);
            $tagId = substr($qr, 0, 2);
            $tagValueLength = (int) substr($qr, 2, 2);
            
            if ($index + 4 + $tagValueLength > strlen($qrPayload)) {
                break; // Not enough characters for the value
            }

            $tagValue = substr($qr, 4, $tagValueLength);

            // Find the tag in the tags array
            $findTag = $this->findTag($tags, $tagId);
            
            if ($findTag) {
                if ($findTag->children) {
                    // If the tag has children, initialize a nested object and recurse
                    $childData = [];
                    $this->parse($findTag->children, $tagValue, null, $childData);
                    
                    if ($parent) {
                        if (!isset($data[$parent])) {
                            $data[$parent] = [];
                        }
                        $data[$parent] = array_merge($data[$parent], $childData);
                    } else {
                        $data[$findTag->name] = $childData;
                    }
                } else {
                    // Decode the tag value if necessary
                    $tagValue = $this->decodeTagValue($findTag->name, $tagValue);

                    // Assign the tag value to the data object
                    if ($parent) {
                        if (!isset($data[$parent])) {
                            $data[$parent] = [];
                        }
                        $data[$parent][$findTag->name] = $tagValue;
                    } else {
                        $data[$findTag->name] = $tagValue;
                    }
                }
            }

            // Move to the next tag in the QR payload
            $index += $tagValueLength + 4;
        }
    }

    /**
     * Find a tag by ID in the tags array
     */
    private function findTag(array $tags, string $tagId): ?QrisTag
    {
        foreach ($tags as $tag) {
            if ($tag->id === $tagId) {
                return $tag;
            }
        }
        return null;
    }

    /**
     * Decode specific tag values based on their names
     */
    private function decodeTagValue(string $tagName, string $value): mixed
    {
        return match ($tagName) {
            'point_of_initiation_method' => $value === '11' ? 'STATIC' : ($value === '12' ? 'DYNAMIC' : $value),
            'transaction_currency' => $value === '360' ? 'RUPIAH' : $value,
            'tip_indicator' => match ($value) {
                '01' => 'INPUT_TIP',
                '02' => 'FIXED_TIP',
                '03' => 'FIXED_TIP_PERCENTAGE',
                default => $value
            },
            'fixed_tip_amount', 'transaction_amount', 'percentage_tip_amount' => (float) $value,
            default => $value
        };
    }
}