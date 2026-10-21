<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ShippingZone;
use App\Models\ShippingZoneLocation;
use App\Models\ShippingZoneMethod;
use App\Models\ShippingClass;
use App\Models\ShippingClassRate;
use App\Models\PickupLocation;
use App\Models\Product;

class ShippingController extends Controller
{
    // ১. শিপিং জোন লিস্ট
    public function zonesIndex()
    {
        $zones = ShippingZone::with(['locations', 'methods'])->orderBy('serial', 'asc')->get();
        return view('admin.shipping.zones', compact('zones'));
    }

    public function zoneCreate()
    {
        return view('admin.shipping.zone_create');
    }

    public function zoneStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'districts' => 'required|string',
        ]);

        $zone = ShippingZone::create([
            'name' => $request->name,
            'serial' => $request->serial ?? 0,
        ]);

        ShippingZoneLocation::create([
            'shipping_zone_id' => $zone->id,
            'districts' => trim($request->districts),
        ]);

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping zone created successfully.');
    }

    public function zoneEdit($id)
    {
        $zone = ShippingZone::with(['locations', 'methods'])->findOrFail($id);
        $locationRecord = $zone->locations->first();
        $districtsString = $locationRecord ? $locationRecord->districts : '';

        // জোনের ওয়েট রেট ও ক্লাসগুলো আনা
        $weightRates = ShippingClassRate::where('shipping_zone_id', $zone->id)->get();
        $shippingClasses = ShippingClass::all();

        return view('admin.shipping.zone_edit', compact('zone', 'districtsString', 'weightRates', 'shippingClasses'));
    }

    public function zoneUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'districts' => 'required|string',
        ]);

        $zone = ShippingZone::findOrFail($id);
        $zone->update(['name' => $request->name]);

        ShippingZoneLocation::updateOrCreate(
            ['shipping_zone_id' => $zone->id],
            ['districts' => trim($request->districts)]
        );

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping zone updated successfully.');
    }

    public function zoneDestroy($id)
    {
        ShippingZone::findOrFail($id)->delete();
        return back()->with('success', 'Shipping zone deleted successfully.');
    }

    // ওয়েট স্লাব ও রেট সেভ মেথড
    public function saveWeightRates(Request $request, $zoneId)
    {
        $request->validate([
            'weight_from' => 'required|array',
            'weight_to' => 'required|array',
            'cost' => 'required|array',
            'per_kg_extra' => 'nullable|numeric',
        ]);

        ShippingClassRate::where('shipping_zone_id', $zoneId)->delete();

        foreach ($request->weight_from as $i => $from) {
            ShippingClassRate::create([
                'shipping_zone_id' => $zoneId,
                'shipping_class_id' => $request->shipping_class_id ?? null,
                'weight_from' => $from,
                'weight_to' => $request->weight_to[$i],
                'cost' => $request->cost[$i],
                'per_kg_extra' => $request->per_kg_extra ?? 20.00,
            ]);
        }

        return back()->with('success', 'Weight slabs saved successfully.');
    }

    // মেথড স্টোর ও ডিলিট
    public function methodStore(Request $request, $zoneId)
    {
        $request->validate([
            'method_type' => 'required|in:flat_rate,free_shipping',
            'title' => 'required|string|max:255',
        ]);

        ShippingZoneMethod::create([
            'shipping_zone_id' => $zoneId,
            'method_type' => $request->method_type,
            'title' => $request->title,
            'cost' => $request->cost ?? 0,
            'free_shipping_requires' => $request->free_shipping_requires ?? 'no_requirement',
            'min_amount' => $request->min_amount,
            'is_enabled' => $request->has('is_enabled') ? 1 : 0,
        ]);

        return back()->with('success', 'Shipping method added.');
    }

    public function methodDestroy($id)
    {
        ShippingZoneMethod::findOrFail($id)->delete();
        return back()->with('success', 'Shipping method deleted.');
    }

    // প্রোডাক্ট AJAX সার্চ এবং স্পেসিফিক ডেলিভারি রেট সেট
    public function searchProduct(Request $request)
    {
        $term = $request->term;
        $products = Product::where('title', 'LIKE', '%' . $term . '%')
            ->select('id', 'title', 'weight', 'custom_delivery_charge', 'multiply_qty_delivery_charge')
            ->limit(10)
            ->get();
        return response()->json($products);
    }

    public function updateProductShipping(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'custom_delivery_charge' => 'nullable|numeric',
        ]);

        $prod = Product::findOrFail($request->product_id);
        $prod->update([
            'custom_delivery_charge' => $request->custom_delivery_charge,
            'multiply_qty_delivery_charge' => $request->has('multiply_qty_delivery_charge') ? 1 : 0,
            'weight' => $request->weight ?? $prod->weight,
        ]);

        return response()->json(['success' => true, 'message' => 'Product delivery settings updated!']);
    }

    // ২. গ্লোবাল সেটিংস
    public function settingsIndex()
    {
        $settings = DB::table('settings')->whereIn('key', [
            'shipping_calc_on_cart',
            'shipping_hide_cost_before_address',
            'shipping_hide_rates_when_free',
            'shipping_destination_mode',
            'shipping_debug_mode'
        ])->pluck('value', 'key');

        return view('admin.shipping.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $keys = [
            'shipping_calc_on_cart',
            'shipping_hide_cost_before_address',
            'shipping_hide_rates_when_free',
            'shipping_destination_mode',
            'shipping_debug_mode'
        ];

        foreach ($keys as $key) {
            $val = $request->has($key) ? '1' : ($request->input($key) ?? '0');
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $val, 'updated_at' => now()]);
        }

        return back()->with('success', 'Shipping settings updated.');
    }

    // ৩. ক্লাসেস
    public function classesIndex()
    {
        $classes = ShippingClass::latest()->get();
        return view('admin.shipping.classes', compact('classes'));
    }

    public function classStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shipping_classes,name',
        ]);

        ShippingClass::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Shipping class created.');
    }

    public function classDestroy($id)
    {
        ShippingClass::findOrFail($id)->delete();
        return back()->with('success', 'Shipping class deleted.');
    }

    // ৪. লোকাল পিকআপ
    public function pickupIndex()
    {
        $pickupSettings = DB::table('settings')->whereIn('key', [
            'local_pickup_enabled',
            'local_pickup_title',
            'local_pickup_cost'
        ])->pluck('value', 'key');

        $locations = PickupLocation::latest()->get();
        return view('admin.shipping.pickup', compact('pickupSettings', 'locations'));
    }

    public function pickupUpdate(Request $request)
    {
        DB::table('settings')->updateOrInsert(['key' => 'local_pickup_enabled'], ['value' => $request->has('local_pickup_enabled') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'local_pickup_title'], ['value' => $request->local_pickup_title ?? 'Pickup', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'local_pickup_cost'], ['value' => $request->local_pickup_cost ?? 0, 'updated_at' => now()]);

        return back()->with('success', 'Local pickup settings updated.');
    }
}