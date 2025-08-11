<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\DriverLocationUpdated;

class DriverLocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $driver = $request->user();
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        DB::table('driver_locations')->insert([
            'driver_id' => $driver->id,
            'location' => DB::raw("ST_MakePoint($longitude, $latitude)"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        broadcast(new DriverLocationUpdated($driver, $latitude, $longitude))->toOthers();

        return response()->json(['message' => 'Location updated successfully']);
    }
}
