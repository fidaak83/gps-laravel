<?php

// Include the necessary libraries and set up ReactPHP
require __DIR__ . '/vendor/autoload.php';
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
    static $buffer = ''; // Accumulate incoming data

    // Handle incoming data from the client
    $conn->on('data', function ($data) use ($conn, &$imei, &$buffer) {
        // Append incoming data to buffer
        $buffer .= $data;

        // Log raw data in hex format to debug
        echo "Raw data received: " . bin2hex($data) . "\n";

        // Step 1: Extract IMEI length (first two bytes)
        if (strlen($buffer) >= 2) {  // Minimum size to check IMEI length
            $imeiLength = unpack('n', substr($buffer, 0, 2))[1];
            echo "IMEI Length: $imeiLength\n";
            
            // Ensure that the buffer contains the full IMEI
            if (strlen($buffer) >= 2 + $imeiLength) {
                $grabImei = substr($buffer, 2, $imeiLength);
                echo "Received IMEI: $grabImei\n";

                // Step 2: Validate IMEI (must be 15 digits long)
                if (strlen($grabImei) === 15 && ctype_digit($grabImei)) {
                    echo "Valid IMEI, sending acknowledgment...\n";
                    $conn->write(hex2bin('01'));  // Send 0x01 for valid IMEI
                } else {
                    echo "Invalid IMEI received, sending failure acknowledgment...\n";
                    $conn->write(hex2bin('00'));  // Send 0x00 for invalid IMEI
                }

                // Clear the buffer after processing IMEI
                $buffer = substr($buffer, 2 + $imeiLength);
            }
        }

        // Step 2: Process AVL data if IMEI is already set
        if ($imei && strlen($buffer) >= 4) { // We expect at least 4 bytes for AVL data header
            // Extract AVL data header
            $header = substr($buffer, 0, 4);  // First 4 bytes are header (e.g., 0x00000000)
            $length = unpack('N', substr($buffer, 4, 4))[1];  // Data length (e.g., 0x000000FE)
            $codecId = unpack('C', substr($buffer, 8, 1))[1];  // Codec ID (e.g., 0x08)
            $dataCount = unpack('C', substr($buffer, 9, 1))[1];  // Number of data elements (e.g., 0x02)

            echo "Processing AVL Data: Length = $length, Codec ID = $codecId, Data Count = $dataCount\n";

            // Step 3: Send acknowledgment for number of data elements (4 bytes)
            $acknowledgment = pack('N', $dataCount); // Pack as 32-bit unsigned integer (network byte order)
            $conn->write($acknowledgment);
            echo "Acknowledgment sent for $dataCount data elements\n";

            // Clear the buffer after processing AVL data
            $buffer = substr($buffer, 4 + 4 + 1 + 1);  // Adjust buffer based on header size (4 + 4 + 1 + 1)
        }
    });

    // Handle connection closure
    $conn->on('close', function () {
        echo "Connection closed\n";
    });
});

// Run the event loop
$loop->run();

?>
