<?php

namespace Ardfar\ParseQris\Data;

class QrisTag
{
    public function __construct(
        public string $id,
        public string $name,
        public ?array $children = null
    ) {}

    public static function getAllTags(): array
    {
        return [
            // Basic tags without children
            new self('00', 'payload_format_indicator'),
            new self('01', 'point_of_initiation_method'),
            new self('02', 'merchant_principal_visa'),
            new self('04', 'merchant_principal_mastercard'),
            
            // Merchant information tags with children
            new self('26', 'merchant_information_26', [
                new self('00', 'global_unique_identifier'),
                new self('01', 'merchant_pan'),
                new self('02', 'merchant_id'),
                new self('03', 'merchant_criteria'),
            ]),
            new self('27', 'merchant_information_27', [
                new self('00', 'global_unique_identifier'),
                new self('01', 'merchant_pan'),
                new self('02', 'merchant_id'),
                new self('03', 'merchant_criteria'),
            ]),
            new self('50', 'merchant_information_50', [
                new self('00', 'global_unique_identifier'),
                new self('01', 'merchant_pan'),
                new self('02', 'merchant_id'),
                new self('03', 'merchant_criteria'),
            ]),
            new self('51', 'merchant_information_51', [
                new self('00', 'global_unique_identifier'),
                new self('01', 'merchant_pan'),
                new self('02', 'merchant_id'),
                new self('03', 'merchant_criteria'),
            ]),
            
            // Other tags
            new self('52', 'mcc'),
            new self('53', 'transaction_currency'),
            new self('54', 'transaction_amount'),
            new self('55', 'tip_indicator'),
            new self('56', 'fixed_tip_amount'),
            new self('57', 'percentage_tip_amount'),
            new self('58', 'country_code'),
            new self('59', 'merchant_name'),
            new self('60', 'merchant_city'),
            new self('61', 'merchant_postal_code'),
            
            // Additional data tag with children
            new self('62', 'additional_data', [
                new self('00', 'billing_ide'),
                new self('01', 'bill_number'),
                new self('02', 'mobile_number'),
                new self('03', 'store_label'),
                new self('04', 'loyalty_number'),
                new self('05', 'reference_label'),
                new self('06', 'customer_label'),
                new self('07', 'terminal_label'),
                new self('08', 'purpose_of_transaction'),
                new self('09', 'additional_consumer_data'),
                new self('10', 'merchant_tax_id'),
                new self('11', 'merchant_channel'),
            ]),
            new self('63', 'crc'),
        ];
    }
}