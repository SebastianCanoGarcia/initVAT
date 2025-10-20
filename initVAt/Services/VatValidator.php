<?php
namespace App\Services;

class VatValidator
{
    /**
     * Validate an Italian VAT number.
     * - Valid: IT + 11 digits
     * - Correctable: 11 digits only
     * - Invalid: anything else
     */
    public function validate(string $vat): array
    {
        $original = $vat;
        $vat = strtoupper(trim($vat));
        $clean = preg_replace('/[^A-Z0-9]/', '', $vat);

        // Case 1: Valid directly
        if (preg_match('/^IT\d{11}$/', $clean)) {
            return [
                'status' => 'valid',
                'vat' => $clean
            ];
        }

        // Case 2: Correctable (only 11 digits)
        if (preg_match('/^\d{11}$/', $clean)) {
            return [
                'status' => 'corrected',
                'vat' => 'IT' . $clean,
                'correction' => 'Added prefix IT'
            ];
        }

        // Case 3: Invalid
        return [
            'status' => 'invalid',
            'vat' => $original,
            'error' => 'Invalid format: must be IT + 11 digits or 11 digits only'
        ];
    }
}
