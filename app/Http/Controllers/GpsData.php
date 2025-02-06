<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Events\VehicleLocationUpdated;
use Illuminate\Support\Facades\Log;

class GpsData extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $iodata = $data['data'][0]['ioData'];

        // Correct ignition check: defaulting to null if not found, then setting 0 or 1
        $ignition = Arr::first($iodata, function ($io) {
            return $io['id'] === 239 ? $io['value'] : null;
        });

        // Set ignition to 1 if found and true, otherwise 0
        $ignitionValue = (intval($ignition) == 1) ? 1 : 0;

        // Prepare the result array
        $result = [
            "imei" => $data['imei'],
            "timestamp" => $data['data'][0]['timestamp'],
            "longitude" => $data['data'][0]['gpsData']['longitude'],
            "latitude"  => $data['data'][0]['gpsData']['latitude'],
            "speed"     => $data['data'][0]['gpsData']['speed'],
            "ignition"  => $ignitionValue,
        ];

        // Log before broadcasting (ensure sensitive info like IMEI is logged properly or excluded)
        Log::info('Broadcasting Vehicle Location:', $result);

        // Dispatch the event (uses the Laravel event system)
        event(new VehicleLocationUpdated($result));

        // Return a JSON response to confirm successful reception
        return response()->json(['status' => 'success']);
    }
}
