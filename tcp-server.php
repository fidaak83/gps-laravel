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
                $response = $controller->parse(hex2bin($data), $imei);
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

function parseAvlPacket($hexData) {
    // Ensure the length is even
    if (strlen($hexData) % 2 != 0) {
        $hexData = '0' . $hexData;  // Prepend '0' if the length is odd
    }

    $binaryData = hex2bin($hexData);
    $offset = 0;

    // Preamble (4 bytes)
    $preamble = unpack('H8', substr($binaryData, $offset, 4))[1]; $offset += 4;
    if ($preamble !== '00000000') {
        throw new Exception("Invalid preamble.");
    }

    // Data Length (4 bytes)
    $dataLength = unpack('N', substr($binaryData, $offset, 4))[1]; $offset += 4;
    echo "Data Length: $dataLength\n";

    // Codec ID (1 byte)
    $codecId = unpack('C', substr($binaryData, $offset, 1))[1]; $offset += 1;
    echo "Codec ID: $codecId\n";

    // AVL Items Count (1 byte)
    $avlCount = unpack('C', substr($binaryData, $offset, 1))[1]; $offset += 1;
    echo "AVL Items Count: $avlCount\n";

    // Parse each AVL item
    for ($i = 0; $i < $avlCount; $i++) {
        echo "Parsing AVL Item #$i\n";

        // Timestamp (8 bytes)
        $timestamp = unpack('Q', substr($binaryData, $offset, 8))[1] / 1000; $offset += 8;
        echo "Timestamp: $timestamp\n";

        // Priority (1 byte)
        $priority = unpack('C', substr($binaryData, $offset, 1))[1]; $offset += 1;
        echo "Priority: $priority\n";

        // GPS Longitude (4 bytes)
        $longitude = unpack('N', substr($binaryData, $offset, 4))[1] / 10000000; $offset += 4;
        echo "Longitude: $longitude\n";

        // GPS Latitude (4 bytes)
        $latitude = unpack('N', substr($binaryData, $offset, 4))[1] / 10000000; $offset += 4;
        echo "Latitude: $latitude\n";

        // Speed (2 bytes)
        $speed = unpack('n', substr($binaryData, $offset, 2))[1]; $offset += 2;
        echo "Speed: $speed\n";

        // CRC32 checksum (4 bytes)
        $crc = unpack('N', substr($binaryData, $offset, 4))[1]; $offset += 4;
        echo "CRC: $crc\n";
    }
}

// Example Hex Data
$hexData = '0000000014080102000000000000003608010000016B40D8EA300100000000000000000000000000000105021503010101425E0F01F10000601A014E0000000000000000010000C7CF';
parseAvlPacket($hexData);

