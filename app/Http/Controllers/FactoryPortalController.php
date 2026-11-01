<?php

namespace App\Http\Controllers;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\CatalogPhoto;
use App\Models\FactoryAccount;
use App\Models\LandingSetting;
use App\Services\CatalogImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FactoryPortalController extends Controller
{
    public function loginForm()
    {
        return $this->authForm('login');
    }

    public function registerForm()
    {
        return $this->authForm('register');
    }

    private function authForm(string $mode)
    {
        if (Auth::guard('factory')->check()) {
            return redirect()->route('factory.home');
        }

        return view('catalog.auth', ['mode' => $mode, 'settings' => LandingSetting::first()]);
    }

    public function register(Request $request)
    {
        $request->merge(['email' => mb_strtolower((string) $request->input('email'))]);
        $data = $request->validate(['name' => 'required|string|min:2|max:191', 'email' => 'required|email|max:191|unique:factory_accounts,email',
            'phone' => ['required', 'regex:/^\+?[0-9][0-9\s()\-]{6,28}$/D'], 'password' => 'required|string|min:10|max:128|confirmed', 'terms' => 'accepted']);
        unset($data['terms']);
        $data['phone'] = $this->phone($data['phone']);
        $data['password'] = Hash::make($data['password']);
        $factory = FactoryAccount::create($data);
        Auth::guard('factory')->login($factory);
        $request->session()->regenerate();

        return redirect()->route('factory.home')->with('success', __('catalog.registered'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email|max:191', 'password' => 'required|string|max:128']);
        $credentials['email'] = mb_strtolower($credentials['email']);
        if (! Auth::guard('factory')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        $request->session()->regenerate();

        return redirect()->route('factory.home');
    }

    public function logout(Request $request)
    {
        Auth::guard('factory')->logout();
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

        return redirect()->route('factory.login');
    }

    public function home()
    {
        $factory = Auth::guard('factory')->user();
        $items = $factory->items()->with(['photos', 'category'])->latest()->paginate(12);
        $bytes = (int) CatalogPhoto::whereHas('item', fn ($q) => $q->where('factory_id', $factory->id))->sum('bytes');

        return view('catalog.portal', compact('factory', 'items', 'bytes') + ['settings' => LandingSetting::first()]);
    }

    public function profile(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|min:2|max:191', 'phone' => ['required', 'regex:/^\+?[0-9][0-9\s()\-]{6,28}$/D'],
            'city' => 'nullable|string|max:100', 'activity' => 'nullable|string|max:191', 'description' => 'nullable|string|max:2000']);
        $data['phone'] = $this->phone($data['phone']);
        DB::transaction(function () use ($data) {
            $factory = FactoryAccount::lockForUpdate()->findOrFail(Auth::guard('factory')->id());
            $factory->fill($data);
            if ($factory->isDirty() && in_array($factory->status, ['approved', 'rejected'], true)) {
                $factory->status = 'pending';
                $factory->review_note = null;
            }
            $factory->save();
        });

        return back()->with('success', __('catalog.profile_saved'));
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => 'required|string|max:128', 'password' => 'required|string|min:10|max:128|confirmed']);
        $factory = Auth::guard('factory')->user();
        if (! Hash::check($data['current_password'], $factory->password)) {
            throw ValidationException::withMessages(['current_password' => __('auth.password')]);
        }
        $factory->password = Hash::make($data['password']);
        $factory->setRememberToken(\Illuminate\Support\Str::random(60));
        $factory->save();
        $request->session()->regenerate(true);

        return back()->with('success', __('catalog.saved'));
    }

    public function create()
    {
        return $this->form(new CatalogItem);
    }

    public function edit(int $id)
    {
        return $this->form($this->owned($id));
    }

    private function form(CatalogItem $item)
    {
        $factory = Auth::guard('factory')->user();
        $this->requireApproved($factory);
        $categories = CatalogCategory::available()->with('parent')->orderBy('name_ar')->get();
        $item->load('photos');

        return view('catalog.edit', compact('factory', 'categories', 'item') + ['settings' => LandingSetting::first()]);
    }

    public function store(Request $request, CatalogImages $images)
    {
        return $this->saveItem($request, $images, null);
    }

    public function update(Request $request, CatalogImages $images, int $id)
    {
        return $this->saveItem($request, $images, $id);
    }

    private function saveItem(Request $request, CatalogImages $images, ?int $id)
    {
        $factory = Auth::guard('factory')->user();
        $this->requireApproved($factory);
        $item = $id ? $this->owned($id) : null;
        $data = $request->validate(['title' => 'required|string|max:191', 'kind' => 'required|in:product,work', 'description' => 'required|string|max:3000',
            'category_id' => 'required|integer', 'price' => ['nullable', 'regex:/^[0-9]{1,12}(?:\.[0-9]{1,2})?$/D'],
            'currency' => 'nullable|required_with:price|in:USD,EGP,IQD,EUR,SAR,AED,TRY', 'minimum_order' => 'nullable|integer|min:1|max:1000000',
            'photos' => 'nullable|array|max:'.config('catalog.max_photos'), 'photos.*' => 'required|file|max:'.config('catalog.upload_kb'),
            'remove_photos' => 'nullable|array|max:'.config('catalog.max_photos'), 'remove_photos.*' => 'integer']);
        if (! CatalogCategory::available()->whereKey($data['category_id'])->exists()) {
            throw ValidationException::withMessages(['category_id' => __('catalog.category_unavailable')]);
        }
        $remove = array_map('intval', $data['remove_photos'] ?? []);
        $oldPhotos = $item ? $item->photos()->get() : collect();
        if (array_diff($remove, $oldPhotos->pluck('id')->all())) {
            abort(404);
        }
        $files = $request->file('photos', []);
        $photoCount = $oldPhotos->whereNotIn('id', $remove)->count() + count($files);
        if ($photoCount < 1 || $photoCount > config('catalog.max_photos')) {
            throw ValidationException::withMessages(['photos' => __('catalog.photo_count', ['max' => config('catalog.max_photos')])]);
        }
        unset($data['photos'],$data['remove_photos']);
        if (! isset($data['price'])) {
            $data['price'] = null;
            $data['currency'] = null;
        }
        $stored = [];
        try {
            foreach ($files as $file) {
                $stored[] = $images->store($file, $factory->id);
            }
            DB::transaction(function () use ($factory, $item, $data, $stored, $remove) {
                $locked = FactoryAccount::lockForUpdate()->findOrFail($factory->id);
                $this->requireApproved($locked);
                if (! $item && $locked->items()->count() >= config('catalog.max_items')) {
                    throw ValidationException::withMessages(['item' => __('catalog.item_limit')]);
                }
                $current = $item ? $locked->items()->lockForUpdate()->findOrFail($item->id) : new CatalogItem;
                // Concurrent photo edits require a fresh form rather than bypassing quotas.
                $photos = $current->exists ? $current->photos()->get() : collect();
                $kept = $photos->whereNotIn('id', $remove);
                if (array_diff($remove, $photos->pluck('id')->all()) || $kept->count() + count($stored) < 1 || $kept->count() + count($stored) > config('catalog.max_photos')) {
                    throw ValidationException::withMessages(['photos' => __('catalog.photo_count', ['max' => config('catalog.max_photos')])]);
                }
                $used = (int) CatalogPhoto::whereHas('item', fn ($q) => $q->where('factory_id', $locked->id))->sum('bytes');
                $newBytes = array_sum(array_column($stored, 'bytes'));
                if ($used - $photos->whereIn('id', $remove)->sum('bytes') + $newBytes > config('catalog.factory_bytes')) {
                    throw ValidationException::withMessages(['photos' => __('catalog.storage_limit')]);
                }
                $current->fill($data);
                $current->factory_id = $locked->id;
                $current->status = 'pending';
                $current->review_note = null;
                $current->save();
                foreach ($stored as $photo) {
                    $current->photos()->create($photo);
                }
                $deleted = $photos->whereIn('id', $remove)->pluck('path')->all();
                $current->photos()->whereIn('id', $remove)->delete();
                DB::afterCommit(fn () => Storage::disk('local')->delete($deleted));
            });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete(array_column($stored, 'path'));
            throw $error;
        }

        return redirect()->route('factory.home')->with('success', __('catalog.item_saved'));
    }

    public function delete(int $id)
    {
        $item = $this->owned($id);
        DB::transaction(function () use ($item) {
            FactoryAccount::lockForUpdate()->findOrFail($item->factory_id);
            $paths = $item->photos()->pluck('path')->all();
            $item->delete();
            DB::afterCommit(fn () => Storage::disk('local')->delete($paths));
        });

        return back()->with('success', __('catalog.deleted'));
    }

    private function owned(int $id): CatalogItem
    {
        return Auth::guard('factory')->user()->items()->findOrFail($id);
    }

    private function phone(string $value): string
    {
        $phone = \App\Support\ShipmentAccountIdentity::phone($value);
        if (! $phone) {
            throw ValidationException::withMessages(['phone' => __('catalog.phone_hint')]);
        }

        return $phone;
    }

    private function requireApproved(FactoryAccount $factory): void
    {
        abort_unless($factory->status === 'approved', 403, __('catalog.not_approved'));
    }
}
