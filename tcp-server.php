<?php

// Require the Composer autoloader (this makes sure Laravel classes and dependencies are loaded)
require __DIR__.'/vendor/autoload.php';

// Manually bootstrap the Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use ReactPHP's Event Loop and SocketServer
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use App\Http\Controllers\Codec8Controller;
use Illuminate\Support\Facades\Log;

$loop = Loop::get();
$server = new SocketServer('0.0.0.0:8081', [], $loop);

echo "TCP Server running on port 8081\n";

// Handle new connections
$server->on('connection', function ($conn) {
    echo "New connection from: " . $conn->getRemoteAddress() . "\n";
    // $conn->write("Welcome to Laravel TCP Server!\n");

    $imei = null; // Initialize IMEI variable

    // Handle incoming data from the client
    $conn->on('data', function ($data) use ($conn, &$imei) {
        echo "IME: " . $imei ."\n";
        echo "DD: " . $data ."\n";
        try {
            if (!$imei) {
                // Extract IMEI length (first two bytes)
                $imeiLength = unpack('n', substr($data, 0, 2))[1];
                $grabImei = substr($data, 2, $imeiLength); // Extract the IMEI bytes

                // Validate IMEI (should be exactly 15 digits)
                if (strlen($grabImei) === 15 && ctype_digit($grabImei)) {
                    echo "Received IMEI: $grabImei\n";
                    $imei = $grabImei;

                    // Acknowledge valid IMEI with 0x01
                    $conn->write(hex2bin('01'));
                    echo "Acknowledgment sent: 0x01\n";
                } else {
                    // Invalid IMEI, send failure acknowledgment (0x00) and close connection
                    echo "Invalid IMEI received: $grabImei, disconnecting...\n";
                    $conn->write(hex2bin('00')); // Send 0x00 for failure
                    $conn->end();
                    return;
                }
            } else {
                // Process AVL data if IMEI is already set
                echo "Processing AVL data for IMEI: $imei\n";

                // Instantiate the Codec8Controller
                $controller = new Codec8Controller();

                // Parse the data and get the response
                $response = $controller->parse($data, $imei);

                if ($response->status) {
                    // Ensure avlCount is valid and send acknowledgment
                    $acknowledgment = pack('N', (int)$response->count); // Pack as 32-bit unsigned integer (network byte order)
                    $conn->write($acknowledgment);
                    // echo "Acknowledgment sent: $response->count data elements received for IMEI: $imei\n";
                    echo "GPS data ($response->count) stored successfully for imei $imei";

                } else {
                    // Failure response, send 0x00 acknowledgment and close connection
                    echo "Error processing AVL data for IMEI $imei. Closing connection.\n";
                    $conn->write(hex2bin('00'));  // Send 0x00 to indicate failure
                    $conn->end();
                }
            }
        } catch (\Exception $e) {
            // Log and handle any errors during data processing
            echo "Error processing data for IMEI $imei: " . $e->getMessage() . "\n";
            $conn->write(hex2bin('00'));  // Send 0x00 to indicate failure
            $conn->end();
        }
    });

    // // Handle connection closure
    // $conn->on('close', function () {
    //     echo "Connection closed\n";
    // });
});

// Run the event loop
$loop->run();