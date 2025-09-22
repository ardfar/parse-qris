<?php

namespace Ardfar\ParseQris\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Ardfar\ParseQris\Data\QrisData parseFromImage(string $imagePath)
 * @method static \Ardfar\ParseQris\Data\QrisData parseFromString(string $qrisPayload)
 */
class QrisParser extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'qris-parser';
    }
}