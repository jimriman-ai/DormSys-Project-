<?php

declare(strict_types=1);

$j = json_decode(file_get_contents(__DIR__.'/pest-summary-current.json'), true);
if (! is_array($j)) {
    fwrite(STDERR, "invalid json\n");
    exit(1);
}

echo 'result='.($j['result'] ?? '?')
    .' failed='.($j['failed'] ?? '?')
    .' passed='.($j['passed'] ?? '?')
    .' assertions='.($j['assertions'] ?? '?')
    .' risky='.(is_array($j['risky'] ?? null) ? count($j['risky']) : (string) ($j['risky'] ?? '?'))
    .PHP_EOL;

foreach ($j['failures'] ?? [] as $i => $f) {
    echo '---FAIL '.($i + 1).'---'.PHP_EOL;
    echo 'test='.($f['test'] ?? '').PHP_EOL;
    echo 'file='.($f['file'] ?? '').PHP_EOL;
    $msg = (string) ($f['message'] ?? '');
    echo 'message='.substr(preg_replace('/\s+/', ' ', $msg) ?? $msg, 0, 600).PHP_EOL;
}
