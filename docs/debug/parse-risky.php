<?php

declare(strict_types=1);

$j = json_decode(file_get_contents(__DIR__.'/pest-summary.json'), true);
$risky = $j['risky'] ?? null;
echo 'risky_type='.gettype($risky).PHP_EOL;
if (is_array($risky)) {
    echo 'risky_count='.count($risky).PHP_EOL;
    echo 'risky_json='.json_encode($risky, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
} else {
    echo 'risky_raw='.var_export($risky, true).PHP_EOL;
}

// Also dump fail 20/21 messages fully (architecture)
foreach (($j['failures'] ?? []) as $i => $f) {
    if ($i < 19) {
        continue;
    }
    echo '---FULL FAIL '.($i + 1).'---'.PHP_EOL;
    echo ($f['test'] ?? '').PHP_EOL;
    echo ($f['message'] ?? '').PHP_EOL.PHP_EOL;
}
