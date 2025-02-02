<?php
namespace App\Utils;

class Codec8Parser {
    private $buffer;
    private $offset;

    public function __construct($hexData) {
        $this->buffer = hex2bin($hexData);
        $this->offset = 0;
    }

    // Helper function to read bytes from buffer
    private function readBytes($length) {
        $value = substr($this->buffer, $this->offset, $length);
        $this->offset += $length;
        return $value;
    }

    // Helper function to read unsigned integers
    private function readUInt($length) {
        $value = null;
        switch ($length) {
            case 1:
                $value = unpack('C', $this->readBytes(1))[1];
                break;
            case 2:
                $value = unpack('n', $this->readBytes(2))[1];
                break;
            case 4:
                $value = unpack('N', $this->readBytes(4))[1];
                break;
            case 8:
                $hex = bin2hex($this->readBytes(8));
                $value = $this->hexToBigInt($hex);
                break;
        }
        return $value;
    }

    // Helper function to read signed integers
    private function readInt($length) {
        $value = null;
        switch ($length) {
            case 1:
                $value = unpack('c', $this->readBytes(1))[1];
                break;
            case 2:
                $value = unpack('s', $this->readBytes(2))[1];
                break;
            case 4:
                $value = unpack('l', $this->readBytes(4))[1];
                break;
        }
        return $value;
    }

    // Helper function to convert hex to big integer
    private function hexToBigInt($hex) {
        $result = '0';
        for ($i = 0; $i < strlen($hex); $i++) {
            $result = bcadd(bcmul($result, '16'), base_convert($hex[$i], 16, 10));
        }
        return $result;
    }

    // Parse AVL item data
    private function parseAvlItem() {
        $timestamp = $this->readUInt(8) / 1000; // Timestamp
        // $timeString = date('Y-m-d H:i:s', $timestamp); // Timestamp
        $priority = $this->readUInt(1); // Priority

        // GPS Element
        $longitude = $this->readInt(4) / 10000000;
        $latitude = $this->readInt(4) / 10000000;
        $altitude = $this->readInt(2);
        $angle = $this->readInt(2);
        $satellites = $this->readUInt(1);
        $speed = $this->readUInt(2);

        $gpsData = [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'altitude' => $altitude,
            'angle' => $angle,
            'satellites' => $satellites,
            'speed' => $speed
        ];

        // IO Element
        $eventIoId = $this->readUInt(1);
        $totalIo = $this->readUInt(1);

        $readIoElements = function($length) {
            $count = $this->readUInt(1);
            $elements = [];
            for ($i = 0; $i < $count; $i++) {
                $id = $this->readUInt(1);
                $value = $length === 1 ? $this->readUInt(1) : $this->readUInt($length);
                $elements[] = ['id' => $id, 'value' => $value];
            }
            return $elements;
        };

        $io1B = $readIoElements(1);
        $io2B = $readIoElements(2);
        $io4B = $readIoElements(4);
        $io8B = $readIoElements(8);

        $ioData = [
            'eventIoId' => $eventIoId,
            'totalIo' => $totalIo,
            'io1B' => $io1B,
            'io2B' => $io2B,
            'io4B' => $io4B,
            'io8B' => $io8B
        ];

        return [
            'timestamp' => $timestamp,
            // 'timeString' => $timeString,
            'priority' => $priority,
            'gpsData' => $gpsData,
            'ioData' => $ioData
        ];
    }

    // Parse the AVL packet
    public function parseAvlPacket() {
        $preamble = $this->readUInt(4); // Preamble (should be 0x00000000)
        $dataLength = $this->readUInt(4); // Data length
        $codecId = $this->readUInt(1); // Codec ID (should be 8)
        $avlCount = $this->readUInt(1); // Number of AVL items

        $avlItems = [];
        for ($i = 0; $i < $avlCount; $i++) {
            $avlItems[] = $this->parseAvlItem();
        }

        $crc = $this->readUInt(4); // CRC32 checksum

        return [
            'preamble' => $preamble,
            'dataLength' => $dataLength,
            'codecId' => $codecId,
            'avlCount' => $avlCount,
            'avlItems' => $avlItems,
            'crc' => $crc
        ];
    }

    // Convert BigInt values to string in the parsed data
    public static function convertBigIntToString($obj) {
        if (is_string($obj) && ctype_digit($obj)) {
            return $obj; // Already a string representation of a big integer
        }

        // If the object is an array, iterate over and process each element
        if (is_array($obj)) {
            return array_map([self::class, 'convertBigIntToString'], $obj);
        }

        // If the object is an object, process each key-value pair
        if (is_object($obj)) {
            $newObj = new \stdClass();
            foreach ($obj as $key => $value) {
                $newObj->$key = self::convertBigIntToString($value);
            }
            return $newObj;
        }

        // Return the original value if it's not a big integer or an object
        return $obj;
    }
}

// Example usage:
// $hexData = '...'; // Your hex data here
// $parser = new Codec8Parser($hexData);
// $parsedData = $parser->parseAvlPacket();
// $parsedData = Codec8Parser::convertBigIntToString($parsedData);
// print_r($parsedData);

?>