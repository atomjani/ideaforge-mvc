<?php
$dirs = [
    'app',
    'app/core',
    'app/controllers', 
    'app/models',
    'logs',
    'storage',
    'storage/cache'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Created: $dir\n";
        } else {
            echo "Failed: $dir\n";
        }
    } else {
        echo "Exists: $dir\n";
    }
}

echo "Done!\n";
