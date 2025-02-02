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
    $conn->write("Welcome to Laravel TCP Server!\n");

    $imei = null; // Initialize IMEI variable

    // Handle incoming data from the client
    $conn->on('data', function ($data) use ($conn, &$imei) {
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
                    echo "Sending acknowledgment...\n";
                    $conn->write(hex2bin('01'));
                    echo "Acknowledgment sent: 0x01\n";
                    
                    // Check connection status
                    echo "Connection state: " . ($conn->isWritable() ? "Writable" : "Not Writable") . "\n";
    
                } else {
                    echo "Invalid IMEI received: $grabImei, sending failure acknowledgment...\n";
                    $conn->write(hex2bin('00')); // Send 0x00 for failure
                    return; // End the current processing
                }
            } else {
                // Process AVL data if IMEI is already set
                echo "Processing AVL data for IMEI: $imei\n";
    
                // Instantiate the Codec8Controller
                $controller = new Codec8Controller();
    
                // Parse the AVL data and get the response
                $response = $controller->parse($data, $imei);
                echo json_encode($response);
    
                if ($response->status) {
                    // Ensure avlCount is valid and send acknowledgment
                    $acknowledgment = pack('N', (int)$response->count); // Pack as 32-bit unsigned integer (network byte order)
                    $conn->write($acknowledgment);
                    echo "GPS data ($response->count) stored successfully for IMEI: $imei\n";
                } else {
                    echo "Error processing AVL data for IMEI $imei. Sending failure acknowledgment...\n";
                    $conn->write(hex2bin('00'));  // Send 0x00 to indicate failure
                }
            }
        } catch (\Exception $e) {
            echo "Error processing data for IMEI $imei: " . $e->getMessage() . "\n";
            $conn->write(hex2bin('00'));  // Send 0x00 to indicate failure
        }
    });

    // Handle connection closure
    $conn->on('close', function () {
        echo "Connection closed\n";
    });
});

// Run the event loop
$loop->run();
