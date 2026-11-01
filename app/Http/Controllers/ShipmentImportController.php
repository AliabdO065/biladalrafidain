<?php

namespace App\Http\Controllers;

use App\Models\ShipmentImport;
use App\Services\ShipmentSpreadsheetImporter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShipmentImportController extends Controller
{
    public function store(Request $request, ShipmentSpreadsheetImporter $importer)
    {
        $defaults = $request->validateWithBag('shipmentImport', [
            'file' => 'required|file|max:5120', 'origin' => 'required|string|max:191',
            'destination' => 'required|string|max:191', 'departure_date' => 'nullable|date',
            'currency' => 'nullable|in:EGP,IQD,USD,EUR,SAR,AED,TRY',
        ]);
        $file = $request->file('file');
        if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
            throw ValidationException::withMessages(['file' => __('Upload an XLSX receipt report.')])->errorBag('shipmentImport');
        }
        $alreadyImported = ShipmentImport::where('file_hash', hash_file('sha256', $file->getRealPath()))->exists();
        try {
            $import = $importer->import($file, $defaults, $request->user()->id);
        } catch (ValidationException $exception) {
            throw $exception->errorBag('shipmentImport');
        }

        return redirect()->route('dashboard.landing.shipments', ['import_id' => $import->id])->with('success', $alreadyImported
            ? __('This receipt file was already imported. No shipments were duplicated.')
            : __('Imported :shipments shipments, created :trips trips, skipped :skipped existing rows.', [
                'shipments' => $import->shipments_created, 'trips' => $import->trips_created, 'skipped' => $import->shipments_skipped,
            ]));
    }
}
