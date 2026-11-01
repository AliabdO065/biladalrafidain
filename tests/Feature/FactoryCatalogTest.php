<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\FactoryAccount;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactoryCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Real commits exercise image cleanup; only rebuild the isolated in-memory test DB.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->artisan('migrate:fresh')->assertExitCode(0);
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
        Storage::fake('local');
    }

    private function factory(string $status = 'approved'): FactoryAccount
    {
        $factory = FactoryAccount::create(['name' => 'Factory '.uniqid(), 'email' => uniqid().'@example.com', 'phone' => '+201012345678', 'password' => Hash::make('secret-password')]);
        $factory->status = $status;
        $factory->save();

        return $factory;
    }

    private function data(array $overrides = []): array
    {
        return array_merge(['title' => 'Cotton shirt', 'description' => 'Factory-made cotton shirt.', 'kind' => 'product', 'category_id' => CatalogCategory::first()->id, 'photos' => [UploadedFile::fake()->image('shirt.jpg', 2000, 1200)]], $overrides);
    }

    private function item(FactoryAccount $factory, string $status = 'approved'): CatalogItem
    {
        $item = new CatalogItem(['title' => 'Visible shirt', 'description' => 'Public description', 'kind' => 'product', 'category_id' => CatalogCategory::first()->id]);
        $item->factory_id = $factory->id;
        $item->status = $status;
        $item->save();
        $path = 'catalog/'.$factory->id.'/'.\Illuminate\Support\Str::uuid().'.webp';
        Storage::disk('local')->put($path, 'fake fixture bytes');
        $item->photos()->create(['path' => $path, 'bytes' => 18]);

        return $item;
    }

    public function test_marketplace_home_only_features_published_content_and_links_categories_in_both_languages(): void
    {
        $factory = $this->factory();
        $public = $this->item($factory);
        $private = $this->item($factory, 'pending');
        $private->update(['title' => 'Private item never featured']);
        $hiddenFactory = $this->factory('pending');
        $hidden = $this->item($hiddenFactory);
        $hidden->update(['title' => 'Unapproved factory item']);
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['site_locale' => $locale])->get('/')->assertOk()
                ->assertViewHas('marketCounts', fn ($counts) => $counts['factories'] === 1 && $counts['products'] === 1)
                ->assertSee($public->title)->assertSee($factory->name)
                ->assertDontSee($private->title)->assertDontSee($hidden->title)->assertDontSee($hiddenFactory->name)
                ->assertSee(route('fronted.catalog.photo', $public->photos->first()), false)
                ->assertDontSee(route('fronted.catalog.photo', $private->photos->first()), false)
                ->assertSee(route('fronted.catalog', ['category' => $public->category_id]), false)
                ->assertSee('name="q"', false);
        }
        $factory->status = 'suspended';
        $factory->save();
        $this->get('/')->assertOk()->assertDontSee($public->title)->assertDontSee($factory->name);
    }

    public function test_registration_creates_only_a_pending_factory_with_a_hashed_password(): void
    {
        $this->post('/factory/register', ['name' => 'New factory', 'email' => 'OWNER@EXAMPLE.COM', 'phone' => '+201012345678', 'password' => 'secret-password', 'password_confirmation' => 'secret-password', 'terms' => 1, 'status' => 'approved'])->assertRedirect('/factory');
        $factory = FactoryAccount::firstOrFail();
        $this->assertSame('pending', $factory->status);
        $this->assertSame('owner@example.com', $factory->email);
        $this->assertTrue(Hash::check('secret-password', $factory->password));
        $this->assertDatabaseCount('users', 0);
        $this->get('/factory')->assertOk();
        $this->get('/admin')->assertRedirect('/login');
        $this->post('/factory/items', $this->data())->assertForbidden();
    }

    public function test_factory_login_and_logout_are_separate_from_admin_authentication(): void
    {
        $factory = $this->factory();
        $this->get('/factory')->assertRedirect('/factory/login');
        $this->post('/factory/login', ['email' => $factory->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/factory/login', ['email' => $factory->email, 'password' => 'secret-password'])->assertRedirect('/factory');
        $this->get('/admin/catalog')->assertRedirect('/login');
        $this->post('/factory/logout')->assertRedirect('/factory/login');
        $this->get('/factory')->assertRedirect('/factory/login');
    }

    public function test_uploads_are_compressed_to_private_webp_and_wait_for_review(): void
    {
        $factory = $this->factory();
        $this->actingAs($factory, 'factory');
        $this->post('/factory/items', $this->data(['factory_id' => 999, 'status' => 'approved']))->assertRedirect('/factory')->assertSessionHasNoErrors();
        $item = CatalogItem::firstOrFail();
        $photo = $item->photos()->firstOrFail();
        $this->assertSame($factory->id, $item->factory_id);
        $this->assertSame('pending', $item->status);
        $this->assertStringEndsWith('.webp', $photo->path);
        Storage::disk('local')->assertExists($photo->path);
        $info = getimagesize(Storage::disk('local')->path($photo->path));
        $this->assertSame('image/webp', $info['mime']);
        $this->assertLessThanOrEqual(1400, max($info[0], $info[1]));
        $this->assertLessThanOrEqual(config('catalog.image_bytes'), $photo->bytes);
        $this->get('/catalog/photos/'.$photo->id)->assertOk();
        Auth::guard('factory')->logout();
        $this->get('/catalog')->assertDontSee('Cotton shirt');
        $this->get('/catalog/items/'.$item->id)->assertNotFound();
        $this->get('/catalog/photos/'.$photo->id)->assertNotFound();
    }

    public function test_only_admin_approval_publishes_and_factory_suspension_hides_items_and_images(): void
    {
        $factory = $this->factory('pending');
        $item = $this->item($factory, 'pending');
        $photo = $item->photos()->first();
        $this->actingAs(User::factory()->create(), 'web');
        $this->post('/admin/catalog/items/'.$item->id, ['status' => 'approved', 'version' => $item->reviewVersion()])->assertSessionHasErrors('status');
        $this->post('/admin/catalog/factories/'.$factory->id, ['status' => 'approved', 'version' => $factory->reviewVersion()])->assertRedirect();
        $this->post('/admin/catalog/items/'.$item->id, ['status' => 'approved', 'version' => $item->reviewVersion()])->assertRedirect()->assertSessionHasNoErrors();
        Auth::guard('web')->logout();
        $this->get('/catalog')->assertSee($item->title);
        $this->get('/catalog/items/'.$item->id)->assertOk();
        $this->get('/catalog/photos/'.$photo->id)->assertOk();
        $this->actingAs(User::factory()->create(), 'web');
        $this->post('/admin/catalog/factories/'.$factory->id, ['status' => 'suspended', 'version' => $factory->fresh()->reviewVersion()])->assertRedirect();
        Auth::guard('web')->logout();
        $this->get('/catalog/items/'.$item->id)->assertNotFound();
        $this->get('/catalog/photos/'.$photo->id)->assertNotFound();
    }

    public function test_a_factory_cannot_edit_delete_or_view_other_factory_private_items(): void
    {
        $one = $this->factory();
        $two = $this->factory();
        $item = $this->item($two, 'pending');
        $this->actingAs($one, 'factory');
        $this->get('/factory/items/'.$item->id.'/edit')->assertNotFound();
        $this->post('/factory/items/'.$item->id, $this->data())->assertNotFound();
        $this->delete('/factory/items/'.$item->id)->assertNotFound();
        $this->get('/catalog/photos/'.$item->photos()->first()->id)->assertNotFound();
    }

    public function test_editing_published_content_hides_it_until_review_and_stale_approvals_fail(): void
    {
        $factory = $this->factory();
        $item = $this->item($factory);
        $oldVersion = $item->reviewVersion();
        $this->actingAs($factory, 'factory');
        $data = $this->data(['title' => 'Changed shirt', 'photos' => []]);
        $this->post('/factory/items/'.$item->id, $data)->assertRedirect('/factory');
        $this->assertSame('pending', $item->fresh()->status);
        $this->actingAs(User::factory()->create(), 'web');
        $this->post('/admin/catalog/items/'.$item->id, ['status' => 'approved', 'version' => $oldVersion])->assertSessionHasErrors('version');
        $this->assertSame('pending', $item->fresh()->status);
    }

    public function test_profile_edits_require_reapproval_and_cannot_override_a_suspension(): void
    {
        $factory = $this->factory();
        $item = $this->item($factory);
        $this->actingAs($factory, 'factory');
        $this->post('/factory/profile', ['name' => $factory->name, 'phone' => $factory->phone, 'city' => 'Cairo', 'status' => 'approved'])->assertRedirect();
        $this->assertSame('pending', $factory->fresh()->status);
        $this->get('/catalog/items/'.$item->id)->assertNotFound();
        $factory->status = 'suspended';
        $factory->save();
        $this->post('/factory/profile', ['name' => $factory->name, 'phone' => $factory->phone, 'city' => 'Giza', 'status' => 'approved'])->assertRedirect();
        $this->assertSame('suspended', $factory->fresh()->status);
    }

    public function test_invalid_images_missing_photos_currency_and_quota_errors_leave_no_files(): void
    {
        $factory = $this->factory();
        $this->actingAs($factory, 'factory');
        $this->post('/factory/items', $this->data(['photos' => [UploadedFile::fake()->createWithContent('fake.jpg', '<svg><script>alert(1)</script></svg>')]]))->assertSessionHasErrors('photos');
        $this->post('/factory/items', $this->data(['photos' => []]))->assertSessionHasErrors('photos');
        $this->post('/factory/items', $this->data(['price' => '10.00']))->assertSessionHasErrors('currency');
        config(['catalog.factory_bytes' => 1]);
        $this->post('/factory/items', $this->data())->assertSessionHasErrors('photos');
        $this->assertDatabaseCount('catalog_items', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('catalog'));
    }

    public function test_replacing_and_deleting_images_releases_storage(): void
    {
        $factory = $this->factory();
        $item = $this->item($factory);
        $old = $item->photos()->first();
        $this->actingAs($factory, 'factory');
        $this->post('/factory/items/'.$item->id, $this->data(['remove_photos' => [$old->id]]))->assertRedirect('/factory');
        Storage::disk('local')->assertMissing($old->path);
        $new = $item->photos()->firstOrFail();
        Storage::disk('local')->assertExists($new->path);
        $this->delete('/factory/items/'.$item->id)->assertRedirect();
        Storage::disk('local')->assertMissing($new->path);
        $this->assertDatabaseCount('catalog_photos', 0);
    }

    public function test_category_hierarchy_filters_include_children_and_inactive_parents_hide_content(): void
    {
        $parent = CatalogCategory::first();
        $child = CatalogCategory::create(['parent_id' => $parent->id, 'name_ar' => 'فرعي', 'name_en' => 'Child', 'active' => true]);
        $factory = $this->factory();
        $item = $this->item($factory);
        $item->category_id = $child->id;
        $item->save();
        $this->get('/catalog?category='.$parent->id)->assertOk()->assertSee($item->title);
        $this->get('/catalog?q=not-found')->assertDontSee($item->title);
        $this->get('/catalog?kind=work')->assertDontSee($item->title);
        $parent->active = false;
        $parent->save();
        $this->get('/catalog/items/'.$item->id)->assertNotFound();
        $this->get('/catalog/photos/'.$item->photos()->first()->id)->assertNotFound();
    }

    public function test_public_and_private_pages_render_in_both_languages(): void
    {
        $factory = $this->factory();
        $item = $this->item($factory);
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['site_locale' => $locale]);
            foreach (['/catalog', '/catalog/factories', '/catalog/factories/'.$factory->id, '/catalog/items/'.$item->id, '/factory/login', '/factory/register'] as $url) {
                $this->get($url)->assertOk();
            }
            $this->actingAs($factory, 'factory');
            $this->get('/factory')->assertOk();
            $this->get('/factory/items/create')->assertOk();
            Auth::guard('factory')->logout();
        }
        $this->actingAs(User::factory()->create(), 'web')->get('/admin/catalog')->assertOk();
    }

    public function test_orphan_cleanup_never_deletes_referenced_or_recent_images(): void
    {
        $item = $this->item($this->factory());
        $kept = $item->photos()->first();
        $old = 'catalog/99/'.\Illuminate\Support\Str::uuid().'.webp';
        $recent = 'catalog/99/'.\Illuminate\Support\Str::uuid().'.webp';
        Storage::disk('local')->put($old, 'orphan');
        Storage::disk('local')->put($recent, 'new');
        touch(Storage::disk('local')->path($old), now()->subDays(2)->timestamp);
        touch(Storage::disk('local')->path($kept->path), now()->subDays(2)->timestamp);
        $this->artisan('catalog:prune-images')->assertExitCode(0);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($kept->path);
        Storage::disk('local')->assertExists($recent);
    }
}
