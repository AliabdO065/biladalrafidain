<?php

namespace App\Http\Controllers;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\FactoryAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CatalogAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, ['pending', 'approved', 'rejected', 'suspended', 'all'], true), 422);
        $factories = FactoryAccount::withCount('items')->when($status !== 'all', fn ($q) => $q->where('status', $status))->latest()->paginate(15, ['*'], 'factories_page')->withQueryString();
        $items = CatalogItem::with(['factory', 'category', 'photos'])
            ->when($status === 'suspended', fn ($q) => $q->whereHas('factory', fn ($f) => $f->where('status', 'suspended')))
            ->when(! in_array($status, ['all', 'suspended'], true), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(15, ['*'], 'items_page')->withQueryString();
        $categories = CatalogCategory::with('parent')->orderBy('parent_id')->orderBy('name_ar')->get();

        return view('dashboard.catalog.index', compact('factories', 'items', 'categories', 'status'));
    }

    public function factory(Request $request, FactoryAccount $factory)
    {
        $data = $request->validate(['status' => 'required|in:approved,rejected,suspended', 'review_note' => 'nullable|string|max:500', 'version' => 'required|string|size:64']);
        DB::transaction(function () use ($factory, $data) {
            $factory = FactoryAccount::lockForUpdate()->findOrFail($factory->id);
            if (! hash_equals($factory->reviewVersion(), $data['version'])) {
                throw ValidationException::withMessages(['version' => __('catalog.stale_review')]);
            }
            $factory->status = $data['status'];
            $factory->review_note = $data['review_note'] ?? null;
            $factory->save();
        });

        return back()->with('success', __('catalog.saved'));
    }

    public function item(Request $request, CatalogItem $item)
    {
        $data = $request->validate(['status' => 'required|in:approved,rejected', 'review_note' => 'nullable|string|max:500', 'version' => 'required|string|size:64']);
        DB::transaction(function () use ($item, $data) {
            $factory = FactoryAccount::lockForUpdate()->findOrFail($item->factory_id);
            $item = CatalogItem::lockForUpdate()->findOrFail($item->id);
            if (! hash_equals($item->reviewVersion(), $data['version'])) {
                throw ValidationException::withMessages(['version' => __('catalog.stale_review')]);
            }
            if ($data['status'] === 'approved' && ($factory->status !== 'approved' || ! CatalogCategory::available()->whereKey($item->category_id)->exists() || ! $item->photos()->exists())) {
                throw ValidationException::withMessages(['status' => __('catalog.approve_requirements')]);
            }
            $item->status = $data['status'];
            $item->review_note = $data['review_note'] ?? null;
            $item->save();
        });

        return back()->with('success', __('catalog.saved'));
    }

    public function category(Request $request, CatalogCategory $category = null)
    {
        $data = $request->validate(['name_ar' => 'required|string|max:100', 'name_en' => 'required|string|max:100', 'parent_id' => 'nullable|integer', 'active' => 'sometimes|accepted']);
        $category = $category ?? new CatalogCategory;
        if (! empty($data['parent_id'])) {
            $parent = CatalogCategory::whereNull('parent_id')->findOrFail($data['parent_id']);
            if ($parent->id === $category->id || ($category->exists && $category->children()->exists())) {
                throw ValidationException::withMessages(['parent_id' => __('catalog.category_parent')]);
            }
        }
        $category->fill($data);
        $category->active = $request->boolean('active');
        $category->save();

        return back()->with('success', __('catalog.saved'));
    }
}
