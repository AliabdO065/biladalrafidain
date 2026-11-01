<?php

namespace App\Http\Controllers;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\CatalogPhoto;
use App\Models\FactoryAccount;
use App\Models\LandingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    public function factories(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:100', 'city' => 'nullable|string|max:100']);
        $factories = FactoryAccount::where('status', 'approved')->withCount(['items' => fn ($q) => $q->published()])
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where(fn ($s) => $s->where('name', 'like', '%'.$value.'%')->orWhere('activity', 'like', '%'.$value.'%')))
            ->when($filters['city'] ?? null, fn ($q, $value) => $q->where('city', 'like', '%'.$value.'%'))->orderBy('name')->paginate(18)->withQueryString();

        return view('catalog.factories', compact('factories', 'filters') + ['settings' => LandingSetting::first()]);
    }

    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:100', 'category' => 'nullable|integer', 'kind' => 'nullable|in:product,work', 'city' => 'nullable|string|max:100']);
        $categoryIds = [];
        if (! empty($filters['category'])) {
            $selected = CatalogCategory::available()->findOrFail($filters['category']);
            $categoryIds = [$selected->id, ...$selected->children()->available()->pluck('id')->all()];
        }
        $items = CatalogItem::published()->with(['factory', 'category', 'photos'])
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->when($filters['kind'] ?? null, fn ($q, $value) => $q->where('kind', $value))
            ->when($filters['city'] ?? null, fn ($q, $value) => $q->whereHas('factory', fn ($f) => $f->where('city', 'like', '%'.$value.'%')))
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where(fn ($s) => $s->where('title', 'like', '%'.$value.'%')->orWhere('description', 'like', '%'.$value.'%')->orWhereHas('factory', fn ($f) => $f->where('name', 'like', '%'.$value.'%')->orWhere('activity', 'like', '%'.$value.'%'))))
            ->latest('id')->paginate(18)->withQueryString();
        $categories = CatalogCategory::available()->with('parent')->orderBy('name_ar')->get();

        return view('catalog.index', compact('items', 'filters', 'categories') + ['settings' => LandingSetting::first()]);
    }

    public function show(int $id)
    {
        $item = CatalogItem::published()->with(['factory', 'category', 'photos'])->findOrFail($id);

        return view('catalog.show', compact('item') + ['settings' => LandingSetting::first()]);
    }

    public function factory(int $id)
    {
        $factory = FactoryAccount::where('status', 'approved')->findOrFail($id);
        $items = $factory->items()->published()->with(['factory', 'category', 'photos'])->latest('id')->paginate(18);

        return view('catalog.factory', compact('factory', 'items') + ['settings' => LandingSetting::first()]);
    }

    public function photo(CatalogPhoto $photo)
    {
        $item = $photo->item;
        abort_unless($item, 404);
        $public = CatalogItem::published()->whereKey($item->id)->exists();
        $owner = (int) Auth::guard('factory')->id() === (int) $item->factory_id;
        abort_unless($public || $owner || Auth::guard('web')->check(), 404);
        abort_unless(Storage::disk('local')->exists($photo->path), 404);

        return response()->file(Storage::disk('local')->path($photo->path), ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
