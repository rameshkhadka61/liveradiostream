<?php
$json = '{"image": "D:\\xampp\\htdocs\\scraper\\images\\Radio_Tahalka_91_9.jpg"}';
echo "Original JSON: " . $json . "\n";
$decoded = json_decode($json, true);
echo "Decoded without stripslashes: " . $decoded['image'] . "\n";

$stripped = stripslashes($json);
echo "Stripped JSON: " . $stripped . "\n";
$decoded_stripped = json_decode($stripped, true);
echo "Decoded with stripslashes: " . (isset($decoded_stripped['image']) ? $decoded_stripped['image'] : 'NULL') . "\n";
