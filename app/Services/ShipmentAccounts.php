<?php

namespace App\Services;

use App\Models\LandingShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShipmentAccounts
{
    public function filters(Request $request): array
    {
        return $request->validate([
            'trip_id' => 'nullable|integer|exists:landing_trips,id',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from'), 'after_or_equal:from')],
            'search' => 'nullable|string|max:191',
            'bucket' => 'nullable|in:EGP,IQD,USD,EUR,SAR,AED,TRY',
        ]);
    }

    public function shipments(array $filters)
    {
        $query = LandingShipment::query()->whereNotNull('account_key');
        if (! empty($filters['trip_id'])) {
            $query->where('trip_id', $filters['trip_id']);
        }
        if (! empty($filters['from'])) {
            $query->whereRaw('COALESCE(receipt_date, DATE(created_at)) >= ?', [$filters['from']]);
        }
        if (! empty($filters['to'])) {
            $query->whereRaw('COALESCE(receipt_date, DATE(created_at)) <= ?', [$filters['to']]);
        }

        return $query;
    }

    public function accountQuery(string $key, array $filters)
    {
        $query = $this->shipments($filters)->where('account_key', $key);

        return empty($filters['bucket']) ? $query->whereNull('currency') : $query->where('currency', $filters['bucket']);
    }

    public function listing(array $filters)
    {
        return $this->listingQuery($filters)->orderBy('contact.receiver_name')->paginate(20)->withQueryString();
    }

    public function listingQuery(array $filters)
    {
        $groups = $this->shipments($filters)->select('account_key', 'currency')
            ->selectRaw('MAX(id) as latest_id, COUNT(*) as shipment_count, SUM(shipping_fee) as shipping_fee, SUM(paid_amount) as paid_amount')
            ->selectRaw('SUM(CASE WHEN balance IS NOT NULL THEN balance WHEN shipping_fee IS NOT NULL AND paid_amount IS NOT NULL THEN shipping_fee - paid_amount ELSE 0 END) as balance')
            ->selectRaw('SUM(CASE WHEN shipping_fee IS NULL OR paid_amount IS NULL THEN 1 ELSE 0 END) as incomplete')
            ->groupBy('account_key', 'currency');
        $query = DB::query()->fromSub($groups, 'accounts')->join('landing_shipments as contact', 'contact.id', '=', 'accounts.latest_id')
            ->select('accounts.*', 'contact.receiver_name', 'contact.receiver_phone', 'contact.merchant_number');
        if (! empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(fn ($q) => $q->where('contact.receiver_name', 'like', $search)->orWhere('contact.receiver_phone', 'like', $search)->orWhere('contact.merchant_number', 'like', $search));
        }

        return $query;
    }

    public function summary(string $key, array $filters, bool $lock = false): array
    {
        $query = $this->accountQuery($key, $filters)->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }

        return $this->summarize($query->cursor(), $filters['bucket'] ?? null);
    }

    public function tripSummaries(int $tripId, bool $lock = false): array
    {
        $query = $this->shipments(['trip_id' => $tripId])->orderBy('account_key')->orderBy('currency')->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $groups = [];
        $current = null;
        $rows = [];
        $flush = function () use (&$groups, &$rows) {
            if (! $rows) {
                return;
            }
            $first = $rows[0];
            $slot = hash('sha256', $first->account_key . '|' . ($first->currency ?? ''));
            $groups[$slot] = $this->summarize($rows, $first->currency) + ['slot' => $slot, 'key' => $first->account_key, 'bucket' => $first->currency];
            $rows = [];
        };
        foreach ($query->cursor() as $shipment) {
            $identity = $shipment->account_key . '|' . ($shipment->currency ?? '');
            if ($current !== null && $current !== $identity) {
                $flush();
            }
            $current = $identity;
            $rows[] = $shipment;
        }
        $flush();

        return $groups;
    }

    public function tripVersion(array $groups): string
    {
        $versions = [];
        foreach ($groups as $slot => $summary) {
            $versions[$slot] = $summary['version'];
        }
        ksort($versions);

        return hash('sha256', json_encode($versions, JSON_THROW_ON_ERROR));
    }

    private function summarize(iterable $rows, ?string $currency): array
    {
        $summary = ['count' => 0, 'fee' => '0.00', 'paid' => '0.00', 'balance' => '0.00', 'incomplete' => 0, 'name' => '', 'phone' => '', 'currency' => $currency];
        $phones = [];
        $version = hash_init('sha256');
        foreach ($rows as $shipment) {
            $summary['count']++;
            $summary['name'] = $shipment->receiver_name ?: __('Unnamed merchant');
            if ($shipment->receiver_phone) {
                $summary['phone'] = $shipment->receiver_phone;
                $phones[$shipment->receiver_phone] = true;
            }
            $summary['fee'] = bcadd($summary['fee'], $shipment->shipping_fee ?? '0', 2);
            $summary['paid'] = bcadd($summary['paid'], $shipment->paid_amount ?? '0', 2);
            $balance = $shipment->balance;
            if ($balance === null && $shipment->shipping_fee !== null && $shipment->paid_amount !== null) {
                $balance = bcsub($shipment->shipping_fee, $shipment->paid_amount, 2);
            }
            $summary['balance'] = bcadd($summary['balance'], $balance ?? '0', 2);
            if ($shipment->shipping_fee === null || $shipment->paid_amount === null) {
                $summary['incomplete']++;
            }
            hash_update($version, json_encode($shipment->only(['id', 'tracking_code', 'receiver_name', 'receiver_phone', 'trip_id', 'receipt_date', 'currency', 'shipping_fee', 'paid_amount', 'balance']), JSON_THROW_ON_ERROR));
        }
        abort_if($summary['count'] === 0, 404);
        $summary['version'] = hash_final($version);
        $summary['phone_count'] = count($phones);

        return $summary;
    }

    public function period(array $filters): string
    {
        $parts = [];
        if (! empty($filters['trip_id'])) {
            $reference = \App\Models\LandingTrip::find($filters['trip_id'])->reference;
            $parts[] = __('Trip') . ' ' . $reference;
        }
        if (! empty($filters['from']) || ! empty($filters['to'])) {
            $parts[] = ($filters['from'] ?? __('Beginning')) . ' — ' . ($filters['to'] ?? __('Today'));
        }

        return implode(' · ', $parts) ?: __('All shipments');
    }
}
