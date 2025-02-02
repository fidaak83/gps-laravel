<?php

require __DIR__.'/vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\SocketServer;
use App\Http\Controllers\Codec8Controller;
use React\Stream\Stream;

// Function to handle a single client connection
function handleClient($conn) {
    $imei = null;

    $conn->on('data', function ($data) use ($conn, &$imei) {
        try {
            if (!$imei) {
                echo 'ImeI: ' . $data;
                // Extract IMEI length (first two bytes) and IMEI from the data buffer
                $imeiLength = unpack('n', substr($data, 0, 2))[1]; // First two bytes represent IMEI length
                $grabImei = substr($data, 2, $imeiLength);  // Slice the buffer to get IMEI bytes

                // Ensure IMEI is valid (15 digits and numeric only)
                if (strlen($grabImei) === 15 && ctype_digit($grabImei)) {
                    echo "Received IMEI: $grabImei\n";
                    $imei = $grabImei;
                    // Send acknowledgment 0x01 for valid IMEI
                    $conn->write(hex2bin('01'));
                    echo "Acknowledgment sent: 0x01\n";
                } else {
                    echo "Invalid IMEI received, sending failure acknowledgment and closing connection...\n";
                    $conn->write(hex2bin('00')); // Send 0x00 for invalid IMEI
                    $conn->end();
                    return; // Disconnect immediately on invalid IMEI
                }
            } else {
                // Process AVL data if IMEI is already set
                echo "Processing AVL data for IMEI: $imei\n";

                // Instantiate the Codec8Controller
                $controller = new Codec8Controller();

                // Parse the AVL data and get the response
                $response = $controller->parse($data, $imei);
                echo 'Data: ' . $data;
                if ($response->status) {
                    // Send acknowledgment with the number of data elements received (4-byte integer)
                    $acknowledgment = pack('N', (int)$response->count);
                    $conn->write($acknowledgment);
                    echo "GPS data stored successfully for IMEI: $imei\n";
                } else {
                    // Failure response, send 0x00 acknowledgment and close connection
                    echo "Error processing AVL data for IMEI $imei. Sending failure acknowledgment...\n";
                    $conn->write(hex2bin('00'));  // Send 0x00 to indicate failure
                    $conn->end(); // Disconnect after error
                }
            }
        } catch (\Exception $e) {
            echo "Error handling client data: " . $e->getMessage() . "\n";
            $conn->write(hex2bin('00'));  // Send 0x00 to indicate failure
            $conn->end(); // Disconnect on error
        }
    });

    $conn->on('close', function () {
         echo "Connection closed\n";
    });

    $conn->on('error', function ($e) {
        echo "Error with connection: " . $e->getMessage() . "\n";
    });
}

// Main server function
function startServer() {
    $loop = Loop::get();
    $server = new SocketServer('0.0.0.0:8081', [], $loop);

    echo "TCP Server running on port 8081\n";

    // Handle new client connections
    $server->on('connection', function ($conn) {
        echo "New connection from: " . $conn->getRemoteAddress() . "\n";
        handleClient($conn); // Handle the client interaction
    });

    // Gracefully shut down on SIGINT
    pcntl_signal(SIGINT, function () use ($server) {
        echo "Shutting down server...\n";
        $server->shutdown();
        echo "Server shutdown complete.\n";
        exit(0); // Exit the process gracefully
    });

    // Run the event loop
    $loop->run();
}

// Start the server
startServer();
