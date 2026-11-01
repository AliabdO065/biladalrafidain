<?php

namespace App\Services;

use App\Models\AccountBatch;
use App\Models\LandingTrip;
use App\Models\Merchant;
use App\Support\ShipmentAccountIdentity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripAccountStatements
{
    public function prepare(LandingTrip $trip, array $data, int $userId, ShipmentAccounts $accounts): AccountBatch
    {
        return DB::transaction(function () use ($trip, $data, $userId, $accounts) {
            LandingTrip::lockForUpdate()->findOrFail($trip->id);
            $groups = $accounts->tripSummaries($trip->id, true);
            if (! $groups || ! hash_equals($accounts->tripVersion($groups), $data['version'])) {
                $this->error(__('Trip shipments changed. Refresh and review all recipients again.'));
            }
            $expected = array_keys($groups);
            $submitted = array_keys($data['recipients']);
            sort($expected);
            sort($submitted);
            if ($expected !== $submitted) {
                $this->error(__('Review every merchant in this trip before preparing the batch.'));
            }
            $resolved = [];
            foreach ($groups as $slot => $summary) {
                $recipient = $data['recipients'][$slot];
                if ($summary['incomplete']) {
                    $this->error(__(':merchant has incomplete shipment amounts. Complete them first.', ['merchant' => $summary['name']]));
                }
                $country = ($recipient['country_code'] ?? null) ?: ($data['default_country_code'] ?? null);
                $phone = ShipmentAccountIdentity::phone($recipient['phone'], $country);
                if (! $phone) {
                    $this->error(__('Confirm the WhatsApp number and country for :merchant.', ['merchant' => $summary['name']]));
                }
                $currency = ($recipient['currency'] ?? null) ?: ($data['default_currency'] ?? null);
                if ($summary['currency']) {
                    if (! empty($recipient['currency']) && $recipient['currency'] !== $summary['currency']) {
                        $this->error(__('The statement currency must match its shipments.'));
                    }
                    $currency = $summary['currency'];
                }
                if (! in_array($currency, ['EGP', 'IQD', 'USD', 'EUR', 'SAR', 'AED', 'TRY'], true)) {
                    $this->error(__('Choose the currency for :merchant.', ['merchant' => $summary['name']]));
                }
                // Unknown-currency rows explicitly assigned to an existing currency
                // join that merchant's one statement in the same currency.
                $identity = $summary['key'] . '|' . $currency;
                if (! isset($resolved[$identity])) {
                    $resolved[$identity] = ['key' => $summary['key'], 'name' => $summary['name'], 'phone' => $phone,
                        'currency' => $currency, 'count' => 0, 'fee' => '0.00', 'paid' => '0.00', 'balance' => '0.00', 'versions' => []];
                }
                if ($resolved[$identity]['phone'] !== $phone) {
                    $this->error(__('Use the same recipient number for the same merchant and currency.'));
                }
                $resolved[$identity]['count'] += $summary['count'];
                foreach (['fee', 'paid', 'balance'] as $field) {
                    $resolved[$identity][$field] = bcadd($resolved[$identity][$field], $summary[$field], 2);
                }
                $resolved[$identity]['versions'][$slot] = $summary['version'];
            }
            ksort($resolved);
            foreach ($resolved as &$row) {
                if (bccomp($row['balance'], '9999999999999.99', 2) === 1 || bccomp($row['balance'], '-9999999999999.99', 2) === -1) {
                    $this->error(__('This balance exceeds the supported statement amount.'));
                }
                ksort($row['versions']);
                $row['version'] = count($row['versions']) === 1 ? reset($row['versions']) : hash('sha256', json_encode($row['versions']));
            }
            unset($row);
            $fingerprint = hash('sha256', json_encode(['trip-statements-v1', $trip->id, $resolved], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $batch = AccountBatch::firstOrCreate(['file_hash' => $fingerprint], [
                'title' => __('Trip') . ' ' . $trip->reference,
                'created_by' => $userId, 'source' => 'trip_shipments',
                'source_filters' => ['trip_id' => $trip->id, 'trip_version' => $data['version']],
            ]);
            if (! $batch->wasRecentlyCreated) {
                return $batch;
            }
            foreach ($resolved as $row) {
                $merchant = Merchant::firstOrCreate(['phone' => $row['phone']], ['name' => $row['name']]);
                $batch->statements()->create([
                    'merchant_id' => $merchant->id, 'source_account_key' => $row['key'], 'name' => $row['name'], 'phone' => $row['phone'],
                    'amount' => $row['balance'], 'currency' => $row['currency'], 'shipment_count' => $row['count'],
                    'shipping_fee' => $row['fee'], 'paid_amount' => $row['paid'], 'source_version' => $row['version'],
                    'details' => __('Shipping fees: :fee; collected: :paid; shipments: :count', ['fee' => $row['fee'], 'paid' => $row['paid'], 'count' => $row['count']]),
                ]);
            }

            return $batch;
        });
    }

    public function assertCurrent(AccountBatch $batch, ShipmentAccounts $accounts): void
    {
        if ($batch->source !== 'trip_shipments') {
            return;
        }
        $filters = $batch->source_filters;
        $groups = $accounts->tripSummaries((int) $filters['trip_id'], true);
        if (! hash_equals((string) $filters['trip_version'], $accounts->tripVersion($groups))) {
            $this->error(__('Trip shipments changed. Prepare a new trip batch before sending.'));
        }
    }

    private function error(string $message): void
    {
        throw ValidationException::withMessages(['trip_accounts' => $message]);
    }
}
