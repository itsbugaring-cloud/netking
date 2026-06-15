<?php
$lines = file(__DIR__ . '/../resources/views/admin/maps/index.blade.php');
$head = array_slice($lines, 0, 160);
$scripts = file_get_contents(__DIR__ . '/map_scripts.blade.php');

$output = implode("", $head) . "\n" . $scripts;
file_put_contents(__DIR__ . '/../resources/views/admin/maps/index.blade.php', $output);
echo "Merged successfully.";
