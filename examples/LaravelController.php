<?php

/**
 * Laravel Controller Example
 * 
 * This example shows how to use the QRIS parser in a Laravel controller
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use QrisParser; // Using the facade
use Ardfar\ParseQris\Exceptions\QrisParseException;

class QrisController extends Controller
{
    /**
     * Parse QRIS from string input
     */
    public function parseFromString(Request $request): JsonResponse
    {
        $request->validate([
            'qris_string' => 'required|string',
        ]);

        try {
            $qrisData = QrisParser::parseFromString($request->qris_string);
            
            return response()->json([
                'success' => true,
                'data' => $qrisData->toArray(),
            ]);
        } catch (QrisParseException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Parse QRIS from uploaded image
     */
    public function parseFromImage(Request $request): JsonResponse
    {
        $request->validate([
            'qr_image' => 'required|image|mimes:jpeg,png,jpg,gif,bmp|max:2048',
        ]);

        try {
            $imagePath = $request->file('qr_image')->getPathname();
            $qrisData = QrisParser::parseFromImage($imagePath);
            
            return response()->json([
                'success' => true,
                'data' => $qrisData->toArray(),
            ]);
        } catch (QrisParseException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get merchant information only
     */
    public function getMerchantInfo(Request $request): JsonResponse
    {
        $request->validate([
            'qris_string' => 'required|string',
        ]);

        try {
            $qrisData = QrisParser::parseFromString($request->qris_string);
            
            $merchantInfo = [
                'name' => $qrisData->merchant_name,
                'city' => $qrisData->merchant_city,
                'postal_code' => $qrisData->merchant_postal_code,
                'country' => $qrisData->country_code,
                'mcc' => $qrisData->mcc,
                'currency' => $qrisData->transaction_currency,
                'merchant_information' => array_filter([
                    'info_26' => $qrisData->merchant_information_26,
                    'info_27' => $qrisData->merchant_information_27,
                    'info_50' => $qrisData->merchant_information_50,
                    'info_51' => $qrisData->merchant_information_51,
                ]),
            ];
            
            return response()->json([
                'success' => true,
                'merchant' => $merchantInfo,
            ]);
        } catch (QrisParseException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}