<?php

declare(strict_types=1);

$j = json_decode(file_get_contents(__DIR__.'/pest-final.json'), true);
echo json_encode([
    'result' => $j['result'] ?? null,
    'tests' => $j['tests'] ?? null,
    'passed' => $j['passed'] ?? null,
    'failed' => $j['failed'] ?? 0,
    'errors' => $j['errors'] ?? 0,
    'risky' => $j['risky'] ?? 0,
    'skipped' => $j['skipped'] ?? 0,
    'assertions' => $j['assertions'] ?? null,
    'failures_count' => count($j['failures'] ?? []),
    'error_details_count' => count($j['error_details'] ?? []),
], JSON_PRETTY_PRINT).PHP_EOL;

if (! empty($j['failures'])) {
    foreach ($j['failures'] as $i => $f) {
        echo 'FAIL '.($i + 1).': '.($f['test'] ?? '').PHP_EOL;
        echo '  '.substr(preg_replace('/\s+/', ' ', (string) ($f['message'] ?? '')) ?? '', 0, 200).PHP_EOL;
    }
}
if (! empty($j['error_details'])) {
    foreach ($j['error_details'] as $i => $f) {
        echo 'ERR '.($i + 1).': '.($f['test'] ?? '').' :: '.($f['message'] ?? '').PHP_EOL;
    }
}

$xmlPath = __DIR__.'/junit-final.xml';
if (! is_file($xmlPath)) {
    echo "no junit\n";
    exit(0);
}

$xml = simplexml_load_file($xmlPath);
$zero = [];
foreach ($xml->xpath('//testcase') ?: [] as $tc) {
    $assertions = (int) $tc['assertions'];
    if ($assertions === 0) {
        $zero[] = ((string) $tc['classname']).'::'.((string) $tc['name']);
    }
}
echo 'zero_assertion_count='.count($zero).PHP_EOL;
foreach (array_slice($zero, 0, 40) as $z) {
    echo 'ZERO: '.$z.PHP_EOL;
}
