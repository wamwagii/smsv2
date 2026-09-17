<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$checks = [
    ['App\Models\Staff',        'subjects_taught'],
    ['App\Models\Staff',        'certifications'],
    ['App\Models\FeeStructure', 'payment_plan'],
    ['App\Models\Payment',      'gateway_response'],
    ['App\Models\Result',       'assessment_breakdown'],
];

echo str_repeat('-', 60) . PHP_EOL;

foreach ($checks as [$model, $field]) {
    $instance = $model::whereNotNull($field)->first() ?? $model::first();

    if (!$instance) {
        echo "⚪ {$model}::{$field} — no data" . PHP_EOL;
        continue;
    }

    $value = $instance->{$field};
    $type  = gettype($value);
    $icon  = in_array($type, ['array', 'NULL']) ? '✅' : '🔴';

    echo "{$icon} {$model}::{$field} = {$type}" . PHP_EOL;
}

echo str_repeat('-', 60) . PHP_EOL;