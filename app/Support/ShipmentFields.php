<?php

namespace App\Support;

class ShipmentFields
{
    public static function labels(): array
    {
        return [
            'report_sequence' => __('Report Sequence'),
            'receipt_number' => __('Receipt Number'), 'receipt_date' => __('Receipt Date'),
            'factory_number' => __('Factory / Company Number'), 'sender_name' => __('Factory / Sender'),
            'merchant_number' => __('Merchant / Beneficiary Number'), 'receiver_name' => __('Merchant / Receiver'),
            'receiver_phone' => __('Receiver Phone'), 'city' => __('Receiver City'),
            'packages_count' => __('Package Count'), 'weight_kg' => __('Weight / kg'),
            'item_price' => __('Item Price'), 'shipping_fee' => __('Shipping Fee'),
            'paid_amount' => __('Amount Collected'), 'balance' => __('Remaining Balance'), 'total' => __('Total'), 'notes' => __('Notes'),
            'currency' => __('Currency'),
        ];
    }

    public static function rules(): array
    {
        return [
            'report_sequence' => 'nullable|string|max:191',
            'receipt_number' => 'nullable|string|max:191', 'receipt_date' => 'nullable|date',
            'factory_number' => 'nullable|string|max:191', 'merchant_number' => 'nullable|string|max:191',
            'receiver_phone' => 'nullable|string|max:191', 'city' => 'nullable|string|max:191',
            'packages_count' => 'nullable|integer|min:0|max:1000000',
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'regex:/^[0-9]{1,12}(?:\.[0-9]{1,3})?$/D'],
            'item_price' => ['nullable', 'numeric', 'regex:/^-?[0-9]{1,12}(?:\.[0-9]{1,4})?$/D'],
            'shipping_fee' => ['nullable', 'numeric', 'regex:/^-?[0-9]{1,12}(?:\.[0-9]{1,2})?$/D'],
            'paid_amount' => ['nullable', 'numeric', 'regex:/^-?[0-9]{1,12}(?:\.[0-9]{1,2})?$/D'],
            'balance' => ['nullable', 'numeric', 'regex:/^-?[0-9]{1,12}(?:\.[0-9]{1,2})?$/D'],
            'total' => ['nullable', 'numeric', 'regex:/^-?[0-9]{1,12}(?:\.[0-9]{1,2})?$/D'],
            'notes' => 'nullable|string|max:5000',
            'currency' => 'nullable|in:EGP,IQD,USD,EUR,SAR,AED,TRY',
        ];
    }
}
