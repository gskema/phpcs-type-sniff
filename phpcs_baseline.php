<?php // phpcs:ignore SR1.Files.SideEffects.FoundWithSymbols

include __DIR__ . '/scripts/phpcs_baseline_source.php';

$baselineFilePath = $argv[1] ?? null;
$targetFilePath = $argv[2] ?? null;
$trimBasePaths = [$argv[3] ?? null, $argv[4] ?? null];
$trimBasePaths = array_filter($trimBasePaths);

$exitCode = diffBaseline($baselineFilePath, $targetFilePath, $trimBasePaths);

exit($exitCode);
