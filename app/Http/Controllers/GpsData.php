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

        // Correct ignition check
        $ignition = Arr::first($iodata, function ($io) {
            return $io['id'] === 239 ? $io['value'] : null;
        });

        $result1 = [
            "imei" => $data['imei'],
            "timestamp" => $data['data'][0]['timestamp'],
            "longitude" => $data['data'][0]['gpsData']['longitude'],
            "latitude"  => $data['data'][0]['gpsData']['latitude'],
            "speed"     => $data['data'][0]['gpsData']['speed'],
            "ignition"  => $ignition ? 1 : 0,
        ];


        // Hard-coded data for testing
        $result = [
            "imei" => "864636067519823",
            "timestamp" => 1738668334,
            "longitude" => 46.9073116,
            "latitude" => 24.48566,
            "speed" => 64,
            "ignition" => 1,
        ];

        // Log the result before broadcasting
        Log::info('Broadcasting Vehicle Location:', $result);

        // Broadcast the event
        // broadcast(new VehicleLocationUpdated($result))->toOthers();
        // Example of triggering the event from a controller
        broadcast(new \App\Events\VehicleLocationUpdated([
            'imei' => '864636067519823',
            'timestamp' => time(),
            'longitude' => 46.9073116,
            'latitude' => 24.48566,
            'speed' => 64,
            'ignition' => 1,
        ]))->toOthers();

        return response()->json(['status' => 'success']);
    }
}
