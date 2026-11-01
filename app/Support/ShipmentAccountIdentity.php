<?php

namespace App\Support;

class ShipmentAccountIdentity
{
    public static function key(array $shipment): string
    {
        $number = trim((string) ($shipment['merchant_number'] ?? ''));
        $name = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) ($shipment['receiver_name'] ?? ''))));
        $phone = preg_replace('/[^0-9+]/', '', (string) ($shipment['receiver_phone'] ?? ''));
        $identity = $number !== '' ? ['number', $number]
            : ($name !== '' ? ['contact', $name, $phone] : ['shipment', $shipment['tracking_code'] ?? '']);

        return hash('sha256', json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public static function phone(string $value, ?string $country = null): ?string
    {
        $value = strtr($value, array_combine(preg_split('//u', '٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')));
        $value = preg_replace('/[\s()\-]/u', '', trim($value));
        if (str_starts_with($value, '00')) {
            $value = '+' . substr($value, 2);
        } elseif (preg_match('/^[1-9][0-9]{7,14}$/D', $value) && (str_starts_with($value, '964') || str_starts_with($value, '20'))) {
            $value = '+' . $value;
        } elseif (! str_starts_with($value, '+') && in_array($country, ['20', '964'], true) && preg_match('/^[0-9]+$/D', $value)) {
            $value = '+' . $country . ltrim($value, '0');
        }

        return preg_match('/^\+[1-9][0-9]{7,14}$/D', $value) ? $value : null;
    }
}
