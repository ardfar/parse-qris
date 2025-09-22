<?php

namespace Ardfar\ParseQris\Data;

class QrisData
{
    public function __construct(
        public ?string $payload_format_indicator = null,
        public ?string $point_of_initiation_method = null,
        public ?string $merchant_principal_visa = null,
        public ?string $merchant_principal_mastercard = null,
        public ?array $merchant_information_26 = null,
        public ?array $merchant_information_27 = null,
        public ?array $merchant_information_50 = null,
        public ?array $merchant_information_51 = null,
        public ?string $mcc = null,
        public ?string $transaction_currency = null,
        public ?float $transaction_amount = null,
        public ?string $tip_indicator = null,
        public ?float $fixed_tip_amount = null,
        public ?float $percentage_tip_amount = null,
        public ?string $country_code = null,
        public ?string $merchant_name = null,
        public ?string $merchant_city = null,
        public ?string $merchant_postal_code = null,
        public ?array $additional_data = null,
        public ?string $crc = null
    ) {}

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $result = [];

        foreach ($properties as $property) {
            $value = $property->getValue($this);
            if ($value !== null) {
                $result[$property->getName()] = $value;
            }
        }

        return $result;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}