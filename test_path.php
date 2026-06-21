<?php
$path = 'D:\xampp\htdocs\scraper\images\Radio_Tahalka_91_9.jpg';
echo "Original: " . $path . "\n";
echo "Exists: " . (file_exists($path) ? 'Yes' : 'No') . "\n";

$forward_path = str_replace('\\', '/', $path);
echo "Forward: " . $forward_path . "\n";
echo "Exists: " . (file_exists($forward_path) ? 'Yes' : 'No') . "\n";
