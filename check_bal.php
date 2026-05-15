<?php
$c = file_get_contents('C:\laragon\www\print-server\print-server.php');
$lines = explode("\n", $c);
$bal = 0;
for ($i = 0; $i < count($lines); $i++) {
    $o = substr_count($lines[$i], '{');
    $cl = substr_count($lines[$i], '}');
    $bal += $o - $cl;
    if ($bal < 0) {
        echo "NEGATIVE at line " . ($i+1) . " (bal=$bal): " . trim($lines[$i]) . "\n";
        break;
    }
}
echo "Final: $bal\n";
