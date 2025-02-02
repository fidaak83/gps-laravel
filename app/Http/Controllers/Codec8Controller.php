<?php
namespace App\Http\Controllers;

use App\Models\GpsData;
use App\Utils\Codec8Parser;
use Carbon\Carbon;

class Codec8Controller extends Controller
{
    public $imei    = null;
    public $gpsData = [];

    public function parse($data, $imei)
    {
        try {
            // Get IMEI from the request
            $this->imei = $imei;

            // Get hex data from the request
            $hexData = $data;

            // Instantiate the parser
            $parser = new Codec8Parser($hexData);

            // Parse the AVL packet
            $parsedData = $parser->parseAvlPacket();

            // Convert BigInt values to strings
            $parsedData = Codec8Parser::convertBigIntToString($parsedData);

            if (isset($parsedData['avlCount']) && $parsedData['avlCount'] > 0) {
                return $this->arangeData($parsedData['avlItems'], $parsedData['avlCount']);
            } else {
                return (object) ['status' => false];
            }

        } catch (\Throwable $th) {
            return (object) ['status' => false];
        }
    }

    private function arangeData($data, $count)
    {
        $gpsDataToInsert = []; // Collect all the GPS data to insert in one go

        foreach ($data as &$entry) {
            // Merge io1B, io2B, io4B, and io8B into a single array
            $entry['ioElements'] = array_merge(
                $entry['ioData']['io1B'],
                $entry['ioData']['io2B'],
                $entry['ioData']['io4B'],
                $entry['ioData']['io8B']
            );

            // Optionally, remove unnecessary data
            unset($entry['ioData'], $entry['priority'], $entry['gpsData']['altitude'], $entry['gpsData']['angle'], $entry['gpsData']['satellites']);

            // Prepare the data for batch insertion
            $gpsDataToInsert[] = [
                'imei'        => $this->imei,
                'gpsdata'     => json_encode($entry['gpsData']),
                'ioelements'  => json_encode($entry['ioElements']),
                'recorded_at' => Carbon::createFromTimestamp($entry['timestamp']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // Insert the collected data in a single batch
        if (! empty($gpsDataToInsert)) {
            try {
                GpsData::insert($gpsDataToInsert); // Bulk insert
                $count = count($gpsDataToInsert);  // Update the count to reflect the number of entries inserted
            } catch (\Throwable $th) {
                Log::error('Error in batch insert for GPS data: ' . $th->getMessage());
            }
        }

        return (object) ['status' => true, 'count' => $count];
    }

}
