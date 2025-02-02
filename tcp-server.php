<?php

// Include the necessary libraries and set up ReactPHP
require __DIR__.'/vendor/autoload.php';
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Illuminate\Support\Facades\Log;

// Set up event loop and server
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
            // If IMEI is not set, process the IMEI first
            if (!$imei) {
                // Extract IMEI length (first two bytes)
                $imeiLength = unpack('n', substr($data, 0, 2))[1];
                $grabImei = substr($data, 2, $imeiLength); // Extract the IMEI bytes

                // Validate IMEI (should be exactly 15 digits)
                if (strlen($grabImei) === 15 && ctype_digit($grabImei)) {
                    echo "Received IMEI: $grabImei\n";
                    $imei = $grabImei;

                    // Send acknowledgment: 0x01 for valid IMEI
                    echo "Sending acknowledgment...\n";
                    $conn->write(hex2bin('01'));  // Acknowledge valid IMEI
                    echo "Acknowledgment sent: 0x01\n";
                } else {
                    echo "Invalid IMEI received: $grabImei, sending failure acknowledgment...\n";
                    $conn->write(hex2bin('00')); // Send 0x00 for failure
                    return; // End the current processing
                }
            } else {
                // After IMEI is received, process AVL data packet
                echo "Processing AVL data for IMEI: $imei\n";

                // Example: Extract AVL Data Packet header, codec ID, and number of data elements
                $header = substr($data, 0, 4); // Four zero bytes
                $length = unpack('N', substr($data, 4, 4))[1]; // Data length (e.g., 0x000000FE)
                $codecId = unpack('C', substr($data, 8, 1))[1]; // Codec ID (e.g., 0x08)
                $dataCount = unpack('C', substr($data, 9, 1))[1]; // Number of data elements (e.g., 0x02)

                // Log received AVL data details
                echo "Received AVL Data: Length = $length, Codec ID = $codecId, Data Count = $dataCount\n";

                // Send acknowledgment for received data elements (4 bytes)
                $acknowledgment = pack('N', $dataCount); // Pack as 32-bit unsigned integer (network byte order)
                $conn->write($acknowledgment);
                echo "Acknowledgment sent for $dataCount data elements\n";
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
