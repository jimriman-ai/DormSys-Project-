<?php

declare(strict_types=1);

$path = $argv[1] ?? 'phpstan-current.json';
$bytes = file_get_contents($path);
if ($bytes === false) {
    fwrite(STDERR, "Cannot read {$path}\n");
    exit(1);
}
if (str_starts_with($bytes, "\xFF\xFE") || (strlen($bytes) > 3 && $bytes[1] === "\x00")) {
    $bytes = mb_convert_encoding($bytes, 'UTF-8', 'UTF-16LE');
}
$j = json_decode($bytes, true);
if (! is_array($j)) {
    fwrite(STDERR, "decode fail\n");
    exit(1);
}
echo 'result='.($j['result'] ?? '?').' errors='.($j['errors'] ?? '?').' general='.count($j['general_errors'] ?? []).PHP_EOL;
foreach (($j['error_details'] ?? []) as $file => $msgs) {
    foreach ($msgs as $m) {
        echo $file."\tL".($m['line'] ?? '?')."\t".($m['message'] ?? '').PHP_EOL;
    }
}
