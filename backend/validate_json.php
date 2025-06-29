<?php

$json = file_get_contents('NextWave_Task_API.postman_collection.json');
$decoded = json_decode($json);

if (json_last_error() === JSON_ERROR_NONE) {
    echo "JSON is valid!\n";
} else {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    echo "Error code: " . json_last_error() . "\n";
}
