<?php

namespace App\Http\Controllers;

use App\Models\ConsumableItem;
use App\Models\InventoryCategory;
use App\Models\School;
use App\Models\SchoolAsset;
use App\Services\SchoolOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    // =========================================================================
    // ASSETS
    // =========================================================================

    public function assetsIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $categoryId = $request->query('category_id');
        $condition = $request->query('condition');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with('category')
            ->when($categoryId, fn ($q) => $q->where('inventory_category_id', $categoryId))
            ->when($condition, fn ($q) => $q->where('condition', $condition))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%{$s}%")
                        ->orWhere('asset_code', 'like', "%{$s}%")
                        ->orWhere('room_location', 'like', "%{$s}%")
                        ->orWhere('brand', 'like', "%{$s}%");
                });
            });

        $assets = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $categories = InventoryCategory::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('type', 'asset')
            ->orderBy('name')
            ->get();

        $totalAssets = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $totalDamaged = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('condition', ['minor_damage', 'heavy_damage'])
            ->count();
        $totalCost = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->sum('purchase_cost');

        return view('operations.inventory.assets.index', compact(
            'assets',
            'categories',
            'categoryId',
            'condition',
            'status',
            'search',
            'totalAssets',
            'totalDamaged',
            'totalCost'
        ));
    }

    public function assetCreate(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $categories = InventoryCategory::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('type', 'asset')
            ->orderBy('name')
            ->get();

        return view('operations.inventory.assets.create', compact('categories'));
    }

    public function assetStore(Request $request, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $validated = $request->validate([
            'inventory_category_id' => ['required', 'exists:inventory_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'room_location' => ['required', 'string', 'max:150'],
            'condition' => ['required', 'in:good,minor_damage,heavy_damage,disposed'],
            'status' => ['required', 'in:in_use,in_storage,maintenance,disposed'],
            'acquisition_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'integer', 'min:0'],
            'responsible_person' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $category = InventoryCategory::where('school_id', $school->id)->findOrFail($validated['inventory_category_id']);
        $assetCode = $service->generateAssetCode($school, $category);

        $asset = DB::transaction(function () use ($school, $validated, $assetCode, $request) {
            $asset = SchoolAsset::create([
                'school_id' => $school->id,
                'inventory_category_id' => $validated['inventory_category_id'],
                'asset_code' => $assetCode,
                'name' => $validated['name'],
                'brand' => $validated['brand'] ?? null,
                'model_number' => $validated['model_number'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'room_location' => $validated['room_location'],
                'condition' => $validated['condition'],
                'status' => $validated['status'],
                'acquisition_date' => $validated['acquisition_date'] ?? null,
                'purchase_cost' => $validated['purchase_cost'] ?? 0,
                'responsible_person' => $validated['responsible_person'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $asset->logs()->create([
                'school_id' => $school->id,
                'action' => 'registered',
                'to_location' => $asset->room_location,
                'to_condition' => $asset->condition,
                'notes' => 'Registrasi aset baru ke dalam sistem inventaris sekolah.',
                'performed_by' => $request->user()->id,
            ]);

            return $asset;
        });

        return redirect()->route('operations.inventory.assets.show', $asset)
            ->with('status', "Aset '{$asset->name}' berhasil didaftarkan dengan kode {$asset->asset_code}.");
    }

    public function assetShow(SchoolAsset $asset): View
    {
        $school = School::current();
        abort_if($asset->school_id !== $school?->id, 403);

        $asset->load(['category', 'logs.performer']);

        return view('operations.inventory.assets.show', compact('asset'));
    }

    public function assetEdit(SchoolAsset $asset): View
    {
        $school = School::current();
        abort_if($asset->school_id !== $school?->id, 403);

        $categories = InventoryCategory::when($school?->id, fn ($q) => $q->where('school_id', $school->id))
            ->where('type', 'asset')
            ->orderBy('name')
            ->get();

        return view('operations.inventory.assets.edit', compact('asset', 'categories'));
    }

    public function assetUpdate(Request $request, SchoolAsset $asset): RedirectResponse
    {
        $school = School::current();
        abort_if($asset->school_id !== $school?->id, 403);

        $validated = $request->validate([
            'inventory_category_id' => ['required', 'exists:inventory_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'room_location' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:in_use,in_storage,maintenance,disposed'],
            'acquisition_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'integer', 'min:0'],
            'responsible_person' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset->update($validated);

        return redirect()->route('operations.inventory.assets.show', $asset)
            ->with('status', "Data aset '{$asset->name}' berhasil diperbarui.");
    }

    public function assetDestroy(SchoolAsset $asset): RedirectResponse
    {
        $school = School::current();
        abort_if($asset->school_id !== $school?->id, 403);

        $asset->delete();

        return redirect()->route('operations.inventory.assets.index')
            ->with('status', 'Data aset berhasil dihapus dari inventaris.');
    }

    public function transferAsset(Request $request, SchoolAsset $asset, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($asset->school_id !== $school?->id, 403);

        $request->validate([
            'new_location' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $service->transferAsset($asset, $request->input('new_location'), $request->input('notes'), $request->user());

        return redirect()->route('operations.inventory.assets.show', $asset)
            ->with('status', "Aset berhasil dimutasikan ke ruangan '{$request->input('new_location')}'.");
    }

    public function updateCondition(Request $request, SchoolAsset $asset, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($asset->school_id !== $school?->id, 403);

        $request->validate([
            'new_condition' => ['required', 'in:good,minor_damage,heavy_damage,disposed'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $service->updateAssetCondition($asset, $request->input('new_condition'), $request->input('notes'), $request->user());

        return redirect()->route('operations.inventory.assets.show', $asset)
            ->with('status', "Kondisi fisik aset berhasil diperbarui menjadi '{$asset->conditionLabel()}'.");
    }

    // =========================================================================
    // CONSUMABLE ITEMS
    // =========================================================================

    public function consumablesIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $items = ConsumableItem::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with('category')
            ->withCount('logs')
            ->orderBy('name')
            ->paginate(15);

        $categories = InventoryCategory::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('type', 'consumable')
            ->orderBy('name')
            ->get();

        $lowStockCount = ConsumableItem::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        return view('operations.inventory.consumables.index', compact('items', 'categories', 'lowStockCount'));
    }

    public function consumableStore(Request $request): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $validated = $request->validate([
            'inventory_category_id' => ['required', 'exists:inventory_categories,id'],
            'item_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:1'],
        ]);

        ConsumableItem::create([
            'school_id' => $school->id,
            'inventory_category_id' => $validated['inventory_category_id'],
            'item_code' => $validated['item_code'],
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'current_stock' => $validated['current_stock'],
            'minimum_stock' => $validated['minimum_stock'],
        ]);

        return redirect()->route('operations.inventory.consumables.index')
            ->with('status', "Barang habis pakai '{$validated['name']}' berhasil ditambahkan ke inventaris.");
    }

    public function consumableAdjust(Request $request, ConsumableItem $item, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($item->school_id !== $school?->id, 403);

        $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $service->adjustConsumableStock(
            $item,
            $request->input('type'),
            (int) $request->input('quantity'),
            $request->input('purpose'),
            $request->user()
        );

        return redirect()->route('operations.inventory.consumables.index')
            ->with('status', "Penyesuaian stok untuk '{$item->name}' berhasil dicatat.");
    }

    public function reports(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $totalAssets = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $conditionStats = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')
            ->get();

        $locationStats = SchoolAsset::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->select('room_location', DB::raw('count(*) as count'))
            ->groupBy('room_location')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $lowStockItems = ConsumableItem::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->get();

        return view('operations.inventory.reports', compact('totalAssets', 'conditionStats', 'locationStats', 'lowStockItems'));
    }
}
