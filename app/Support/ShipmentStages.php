<?php

namespace App\Support;

class ShipmentStages
{
    public static function labels(): array
    {
        return [
            'received' => __('Received at warehouse'),
            'customs' => __('In customs clearance'),
            'in_transit' => __('In transit'),
            'out_for_delivery' => __('Out for delivery'),
            'delivered' => __('Delivered'),
        ];
    }

    public static function label(?string $status): string
    {
        return self::labels()[$status] ?? ($status === 'on_hold' ? __('On hold') : (string) $status);
    }

    public static function next(string $status): ?string
    {
        $keys = array_keys(self::labels());
        $index = array_search($status, $keys, true);

        return $index === false ? null : ($keys[$index + 1] ?? null);
    }
}
