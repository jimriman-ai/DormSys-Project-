<?php

declare(strict_types=1);

$j = json_decode(file_get_contents(__DIR__.'/pest-summary.json'), true);
if (! is_array($j)) {
    fwrite(STDERR, "Invalid JSON\n");
    exit(1);
}

echo 'result='.($j['result'] ?? '?').PHP_EOL;
echo 'tests='.($j['tests'] ?? '?').' passed='.($j['passed'] ?? '?').' failed='.($j['failed'] ?? '?').PHP_EOL;
echo 'assertions='.($j['assertions'] ?? '?').PHP_EOL;
echo 'top_keys='.implode(',', array_keys($j)).PHP_EOL;

$fails = $j['failures'] ?? [];
echo 'failures_count='.count($fails).PHP_EOL;

foreach ($fails as $i => $f) {
    echo '---FAIL '.($i + 1).'---'.PHP_EOL;
    if (! is_array($f)) {
        echo 'raw='.substr((string) $f, 0, 800).PHP_EOL;

        continue;
    }
    if ($i === 0) {
        echo 'keys='.implode(',', array_keys($f)).PHP_EOL;
    }
    echo 'test='.($f['test'] ?? '').PHP_EOL;
    echo 'file='.($f['file'] ?? ($f['path'] ?? '')).PHP_EOL;
    $msg = $f['message'] ?? ($f['exception'] ?? ($f['error'] ?? ''));
    if (is_array($msg)) {
        $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
    }
    echo 'message='.substr((string) $msg, 0, 800).PHP_EOL;
    foreach (['trace', 'stack', 'details', 'diff'] as $k) {
        if (! isset($f[$k])) {
            continue;
        }
        $t = is_string($f[$k]) ? $f[$k] : json_encode($f[$k], JSON_UNESCAPED_UNICODE);
        echo $k.'_snip='.substr((string) $t, 0, 600).PHP_EOL;
    }
}

foreach (['risky', 'riskyTests', 'warnings'] as $rk) {
    if (! isset($j[$rk])) {
        continue;
    }
    $risky = $j[$rk];
    echo $rk.'_count='.(is_countable($risky) ? count($risky) : 'n/a').PHP_EOL;
    if (is_array($risky)) {
        foreach (array_slice($risky, 0, 20) as $i => $r) {
            echo '---RISKY '.($i + 1).'---'.PHP_EOL;
            echo is_string($r) ? $r.PHP_EOL : json_encode($r, JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
}
