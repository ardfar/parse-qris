<?php

namespace Ardfar\ParseQris\Exceptions;

use Exception;

class QrisParseException extends Exception
{
    public static function invalidQrisData(string $message = ''): self
    {
        return new self("Invalid QRIS data: {$message}");
    }

    public static function imageNotFound(string $path): self
    {
        return new self("Image file not found: {$path}");
    }

    public static function invalidImageFormat(): self
    {
        return new self("Invalid image format. Supported formats: jpg, jpeg, png, gif, bmp");
    }

    public static function qrCodeNotFound(): self
    {
        return new self("No QR code found in the image");
    }

    public static function qrCodeReadError(string $message = ''): self
    {
        return new self("Failed to read QR code: {$message}");
    }
}