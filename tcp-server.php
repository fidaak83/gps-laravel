<?php

// Define the server IP and port
$host = '0.0.0.0';
$port = 8081;

// Create a TCP stream socket
$server = stream_socket_server("tcp://$host:$port", $errno, $errstr);

if (!$server) {
    echo "Error creating server: $errstr ($errno)\n";
    exit(1);
}

echo "TCP Server running on port 8081\n";

// Store IMEI by connection
$imeiStore = [];

// Listen for incoming connections
while ($conn = stream_socket_accept($server)) {
    $connHash = (int) $conn;  // Unique connection ID based on the connection resource
    echo "New connection from: " . stream_socket_get_name($conn, true) . "\n";
    
    // Initialize IMEI for this connection
    $imeiStore[$connHash] = null;

    // Send a welcome message to the client
    fwrite($conn, "Welcome to PHP TCP Server!\n");

    // Handle incoming data from the client
    while ($data = fread($conn, 1024)) {
        try {
            // Retrieve IMEI for this connection
            $imei = &$imeiStore[$connHash];

            // If IMEI hasn't been received yet
            if (!$imei) {
                // Extract IMEI length (first two bytes)
                $imeiLength = unpack('n', substr($data, 0, 2))[1];
                $grabImei = substr($data, 2, $imeiLength); // Extract the IMEI bytes

                // Validate IMEI (should be exactly 15 digits)
                if (strlen($grabImei) === 15 && ctype_digit($grabImei)) {
                    echo "Received IMEI: $grabImei\n";
                    $imei = $grabImei;

                    // Send acknowledgment 0x01 (accept)
                    fwrite($conn, pack('C', 0x01));
                    echo "Acknowledgment sent: 0x01\n";
                } else {
                    // Invalid IMEI, send failure acknowledgment (0x00)
                    fwrite($conn, pack('C', 0x00)); // Send 0x00 for failure
                    echo "Invalid IMEI received: $grabImei, rejecting...\n";
                    fclose($conn); // Close the connection after invalid IMEI
                    unset($imeiStore[$connHash]); // Remove IMEI for this connection
                    break; // Exit the current connection loop
                }
            } else {
                // If IMEI is valid, process the AVL data
                echo "Processing AVL data for IMEI: $imei\n";

                // Parse the data (you can replace this with your actual data parsing logic)
                $response = parseAvlData($data, $imei);

                if ($response['status']) {
                    // Send acknowledgment with the count of successfully processed data
                    fwrite($conn, pack('N', $response['count'])); // Send as 32-bit unsigned integer
                } else {
                    // Failure response, send 0x00 acknowledgment
                    fwrite($conn, pack('C', 0x00)); // Send 0x00 to indicate failure
                    echo "Error processing AVL data for IMEI $imei\n";
                }
            }
        } catch (\Exception $e) {
            // Log and handle any errors during data processing
            echo "Error processing data for IMEI $imei: " . $e->getMessage() . "\n";
            fwrite($conn, pack('C', 0x00)); // Send 0x00 to indicate failure
        }
    }

    // Handle connection closure
    fclose($conn);
    echo "Connection closed for IMEI: $imei\n";
    unset($imeiStore[$connHash]); // Clean up the IMEI store for this connection
}

// Function to simulate AVL data parsing (you should replace this with your own parsing logic)
function parseAvlData($data, $imei)
{
    // Example of parsing AVL data
    // In real use case, you'd replace this with actual parsing logic

    echo $data . '\n';
    echo $imei . '\n';
    
    
}

?>
