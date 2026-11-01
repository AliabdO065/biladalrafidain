<?php

namespace Tests\Feature;

use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\ShipmentImport;
use App\Models\User;
use App\Services\ShipmentSpreadsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class ShipmentImportTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_import_creates_trip_and_preserves_every_receipt_detail(): void
    {
        $this->actingAs(User::factory()->create());
        $response = $this->upload($this->report([$this->row()]))->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard.landing.shipments', ['import_id' => ShipmentImport::firstOrFail()->id]));
        $this->assertDatabaseCount('landing_trips', 1);
        $this->assertDatabaseCount('landing_shipments', 1);
        $trip = LandingTrip::firstOrFail();
        $shipment = LandingShipment::firstOrFail();
        $this->assertSame('180', $trip->reference);
        $this->assertSame('مصر', $trip->origin);
        $this->assertSame('العراق', $trip->destination);
        $this->assertSame($trip->id, $shipment->trip_id);
        $this->assertSame('1', $shipment->report_sequence);
        $this->assertNull($shipment->receipt_number);
        $this->assertSame('200050065', $shipment->factory_number);
        $this->assertSame('200050005', $shipment->merchant_number);
        $this->assertSame('مصنع تجريبي', $shipment->sender_name);
        $this->assertSame('تاجر تجريبي', $shipment->receiver_name);
        $this->assertSame('07812922220', $shipment->receiver_phone);
        $this->assertSame('بغداد', $shipment->city);
        $this->assertSame(8, $shipment->packages_count);
        $this->assertSame('459.000', $shipment->weight_kg);
        $this->assertSame('0.8000', $shipment->item_price);
        $this->assertSame('367.20', $shipment->shipping_fee);
        $this->assertSame('400.00', $shipment->paid_amount);
        $this->assertSame('-32.80', $shipment->balance);
        $this->assertSame('1295.84', $shipment->total);
        $this->assertSame('ملاحظات تجريبية', $shipment->notes);
        $this->assertSame(3, $shipment->source_row);
        $this->assertMatchesRegularExpression('/^BR-[A-Z0-9]{16}$/', $shipment->tracking_code);
        $this->assertSame(1, $trip->events()->count());
        $this->assertSame(1, $shipment->events()->count());
    }

    public function test_same_report_and_reexported_rows_do_not_duplicate_shipments(): void
    {
        $this->actingAs(User::factory()->create());
        $rows = [$this->row(), $this->row(['report_sequence' => '2', 'sender_name' => 'مصنع ثان'])];
        $this->upload($this->report($rows))->assertSessionHasNoErrors();
        $this->upload($this->report($rows))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('shipment_imports', 1);
        $this->upload($this->report(array_reverse($rows), 'تقرير تم تصديره مرة أخرى'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('shipment_imports', 2);
        $this->assertDatabaseCount('landing_shipments', 2);
        $this->assertSame(2, ShipmentImport::latest('id')->first()->shipments_skipped);
    }

    public function test_legitimate_identical_rows_are_kept_and_existing_trip_progress_is_preserved(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = LandingTrip::create(['reference' => '180', 'origin' => 'Alexandria', 'destination' => 'Iraq', 'status' => 'in_transit']);
        $rows = [$this->row(['receiver_phone' => null]), $this->row(['report_sequence' => '2', 'receiver_phone' => null])];
        $this->upload($this->report($rows))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('landing_trips', 1);
        $this->assertDatabaseCount('landing_shipments', 2);
        $this->assertSame('in_transit', $trip->fresh()->status);
        $this->assertSame('Alexandria', $trip->fresh()->origin);
        $this->assertSame('in_transit', LandingShipment::first()->current_status);
        $this->assertSame(0, ShipmentImport::first()->trips_created);
        $this->upload($this->report($rows, 'تصدير جديد'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('landing_shipments', 2);
    }

    public function test_multiple_trip_numbers_are_grouped_and_dates_are_parsed(): void
    {
        $this->actingAs(User::factory()->create());
        $this->upload($this->report([
            $this->row(['receipt_date' => '25/10/2026', 'receipt_number' => 'REC-1']),
            $this->row(['trip_reference' => '181', 'receipt_date' => '2026-10-24', 'receipt_number' => 'REC-2']),
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('landing_trips', 2);
        $this->assertSame('2026-10-25', LandingShipment::where('receipt_number', 'REC-1')->first()->receipt_date->format('Y-m-d'));
        $this->assertSame(2, ShipmentImport::first()->trips_created);
    }

    public function test_invalid_row_rejects_entire_file_and_reopens_import_modal(): void
    {
        $this->actingAs(User::factory()->create());
        $this->from('/admin/landing/shipments')->upload($this->report([$this->row(), $this->row(['weight_kg' => 'not-a-number'])]))->assertSessionHasErrors('file', null, 'shipmentImport');
        $this->assertDatabaseCount('landing_trips', 0);
        $this->assertDatabaseCount('landing_shipments', 0);
        $this->assertDatabaseCount('shipment_imports', 0);
        $this->get('/admin/landing/shipments')->assertOk()->assertSee('id="shipment-import-modal"', false)->assertSee('data-reopen="1"', false);
    }

    public function test_datatables_search_all_rows_paginate_sort_and_filter_by_trip(): void
    {
        $this->actingAs(User::factory()->create());
        $rows = [];
        for ($i = 1; $i <= 30; $i++) {
            $rows[] = $this->row(['report_sequence' => (string) $i, 'receiver_name' => 'Merchant ' . $i, 'weight_kg' => (string) $i]);
        }
        $this->upload($this->report($rows))->assertSessionHasNoErrors();
        $this->getJson('/admin/landing/shipments/data?draw=2&start=25&length=10')->assertOk()->assertJsonPath('draw', 2)->assertJsonPath('recordsTotal', 30)->assertJsonCount(5, 'data');
        $this->getJson('/admin/landing/shipments/data?' . http_build_query(['search' => ['value' => 'Merchant 30']]))
            ->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.receiver_name', 'Merchant 30')->assertJsonPath('data.0.shipping_fee', '367.20');
        $weightIndex = 3 + array_search('weight_kg', array_keys(\App\Support\ShipmentFields::labels()), true);
        $this->getJson('/admin/landing/shipments/data?' . http_build_query(['order' => [['column' => $weightIndex, 'dir' => 'asc']]]))
            ->assertOk()->assertJsonPath('data.0.weight_kg', '1.000');
        $this->getJson('/admin/trips/data')->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.reference', '180')->assertJsonPath('data.0.shipments_count', 30);
        $otherTrip = LandingTrip::create(['reference' => '999', 'origin' => 'Cairo', 'destination' => 'Basra']);
        $this->getJson('/admin/landing/shipments/data?trip_id=' . $otherTrip->id)->assertOk()->assertJsonPath('recordsTotal', 0);
        $this->getJson('/admin/landing/shipments/data?length=100000')->assertUnprocessable();
    }

    public function test_upload_and_table_data_require_login(): void
    {
        $this->post('/admin/landing/shipments/import')->assertRedirect('/login');
        $this->getJson('/admin/landing/shipments/data')->assertUnauthorized();
        $this->getJson('/admin/trips/data')->assertUnauthorized();
    }

    public function test_table_configuration_does_not_override_datatables_column_definitions(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/landing/shipments')->assertOk()
            ->assertSee('data-field-keys=', false)->assertDontSee('data-columns=', false)
            ->assertSee('data-basic-columns=', false)->assertSee('data-table-details=', false);
        $this->get('/admin/trips')->assertOk()->assertSee('data-field-keys=', false)->assertDontSee('data-columns=', false);
    }

    public function test_supplied_report_when_available(): void
    {
        $path = getenv('TEST_INFOSAS_REPORT');
        if (! $path || ! is_file($path)) {
            $this->markTestSkipped('Set TEST_INFOSAS_REPORT to verify an external receipt report.');
        }
        $file = new UploadedFile($path, 'Infosas.xlsx', null, null, true);
        $rows = app(ShipmentSpreadsheetImporter::class)->parse($file);
        $this->assertCount(34, $rows);
        $this->assertSame(['180'], array_values(array_unique(array_column($rows, 'trip_reference'))));
        $this->assertSame(487, array_sum(array_map('intval', array_column($rows, 'packages_count'))));
        $sum = '0.00';
        foreach ($rows as $row) {
            $sum = bcadd($sum, $row['shipping_fee'], 2);
        }
        $this->assertSame('12975.35', $sum);
        $this->actingAs(User::factory()->create());
        $this->upload($file)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('landing_shipments', 34);
        $this->assertDatabaseCount('landing_trips', 1);
    }

    private function upload(UploadedFile $file)
    {
        return $this->post('/admin/landing/shipments/import', ['file' => $file, 'origin' => 'مصر', 'destination' => 'العراق']);
    }

    private function row(array $overrides = []): array
    {
        return array_replace([
            'report_sequence' => '1', 'receipt_number' => null, 'receipt_date' => null,
            'factory_number' => '200050065', 'sender_name' => 'مصنع تجريبي', 'merchant_number' => '200050005',
            'receiver_name' => 'تاجر تجريبي', 'receiver_phone' => '07812922220', 'city' => 'بغداد',
            'trip_reference' => '180', 'packages_count' => '8', 'weight_kg' => '459.00', 'item_price' => '0.80',
            'shipping_fee' => '367.20', 'paid_amount' => '400.00', 'balance' => '-32.80', 'total' => '1295.84', 'notes' => 'ملاحظات تجريبية',
        ], $overrides);
    }

    private function report(array $rows, string $title = 'تقارير سندات استلام البضائع | Infosas ERP'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'receipt-test-');
        $this->temporaryFiles[] = $path;
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('xl/workbook.xml', '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Target="worksheets/sheet1.xml"/></Relationships>');
        $xml = '<row r="1"><c r="A1" t="inlineStr"><is><t>' . htmlspecialchars($title, ENT_XML1) . '</t></is></c></row>';
        $allRows = [array_keys(ShipmentSpreadsheetImporter::HEADERS)];
        foreach ($rows as $row) {
            $allRows[] = array_map(fn ($field) => $row[$field] ?? '', array_values(ShipmentSpreadsheetImporter::HEADERS));
        }
        $allRows[] = ['الاجمالي', 'الاجمالي'];
        foreach ($allRows as $i => $values) {
            $line = $i + 2;
            $xml .= '<row r="' . $line . '">';
            foreach ($values as $column => $value) {
                if ($value !== '' && $value !== null) {
                    $xml .= '<c r="' . chr(65 + $column) . $line . '" t="inlineStr"><is><t>' . htmlspecialchars((string) $value, ENT_XML1) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }
        $zip->addFromString('xl/worksheets/sheet1.xml', '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $xml . '</sheetData></worksheet>');
        $zip->close();

        return new UploadedFile($path, 'receipt-report.xlsx', null, null, true);
    }
}
