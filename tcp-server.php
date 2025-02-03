<?php

// Require the Composer autoloader (this makes sure Laravel classes and dependencies are loaded)
require __DIR__ . '/vendor/autoload.php';

// Manually bootstrap the Laravel application
$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use ReactPHP's Event Loop and SocketServer
use App\Http\Controllers\Codec8Controller;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

$loop   = Loop::get();
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
            if (! $imei) {
                // Extract IMEI length (first two bytes)
                $imeiLength = unpack('n', substr($data, 0, 2))[1];
                $grabImei   = substr($data, 2, $imeiLength); // Extract the IMEI bytes

                // Validate IMEI (should be exactly 15 digits)
                if (strlen($grabImei) === 15 && ctype_digit($grabImei)) {
                    echo "Received IMEI: $grabImei\n";
                    $imei = $grabImei;

                    // Decision logic: Check if this IMEI should be accepted
                    // For this example, let's assume we are accepting all valid IMEIs.
                    $accept = true; // Change this logic based on your actual acceptance criteria.

                    if ($accept) {
                        // Send acknowledgment 0x01 to accept
                        $conn->write(hex2bin('01')); // Binary 0x01 for acceptance
                        echo "Acknowledgment sent: 0x01\n";
                    } else {
                        // Send acknowledgment 0x00 to reject
                        $conn->write(hex2bin('00')); // Binary 0x00 for rejection
                        echo "Acknowledgment sent: 0x00\n";
                        // No $conn->end() here, so connection remains open even if rejected
                        return; // We just return and don't close the connection
                    }
                } else {
                    // Invalid IMEI, send failure acknowledgment (0x00)
                    echo "Invalid IMEI received: $grabImei, rejecting...\n";
                    $conn->write(hex2bin('00')); // Send 0x00 for failure
                    // No $conn->end() here, so connection stays open
                    return; // We just return and don't close the connection
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
                    $acknowledgment = pack('N', (int) $response->count); // Pack as 32-bit unsigned integer (network byte order)
                    $conn->write($acknowledgment);
                    echo "GPS data ($response->count) stored successfully for imei $imei";
                } else {
                    // Failure response, send 0x00 acknowledgment
                    echo "Error processing AVL data for IMEI $imei.\n";
                    $conn->write(hex2bin('00')); // Send 0x00 to indicate failure
                    // No $conn->end() here, so connection stays open after failure
                }
            }
        } catch (\Exception $e) {
            // Log and handle any errors during data processing
            echo "Error processing data for IMEI $imei: " . $e->getMessage() . "\n";
            $conn->write(hex2bin('00')); // Send 0x00 to indicate failure
            // No $conn->end() here, so connection stays open after error
        }
    });

    // Handle connection closure
    $conn->on('close', function () use ($imei) {
        echo "Connection closed for IMEI: $imei\n";  // Echo IMEI on close
    });
});

// Run the event loop
$loop->run();
