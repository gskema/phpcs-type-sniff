<?php // phpcs:ignore SR1.Files.SideEffects.FoundWithSymbols

$baselineFilePath = $argv[1] ?? null;
$targetFilePath = $argv[2] ?? null;
diffBaseline($baselineFilePath, $targetFilePath);

function diffBaseline(string $baselineFilePath, string $targetFilePath): void
{
    $realBaselineFilePath = getRealFilePath($baselineFilePath);
    $realTargetFilePath = getRealFilePath($targetFilePath);

    $baselineXml = file_get_contents($realBaselineFilePath);

    $matches = [];
    preg_match_all('#\[(\w{16})\]#', $baselineXml, $matches);

    $ignoredHashMap = array_flip($matches[1]);
    unset($matches);

    $remainingWarningCount = 0;
    $removedWarningCount = 0;
    $newFileLines = [];
    $errorFileLine = null;
    $errorLines = [];
    $realTargetFile = fopen($realTargetFilePath, 'r');
    while (!feof($realTargetFile)) {
        $line = fgets($realTargetFile);

        if (false !== strpos($line, '<file')) {
            $errorFileLine = $line;
        } elseif (false !== strpos($line, '</file')) {
            if (!empty($errorLines)) {
                array_push($newFileLines, $errorFileLine, ...$errorLines);
                array_push($newFileLines, $line);
            }
            $errorFileLine = null;
            $errorLines = [];
        } elseif (false !== strpos($line, '<error')) {
            $matches = [];
            preg_match('#\[(\w{16})\]#', $line, $matches);
            $errorHash = $matches[1] ?? null;
            if (null !== $errorHash && key_exists($errorHash, $ignoredHashMap)) {
                $removedWarningCount++;
            } else {
                $remainingWarningCount++;
                $errorLines[] = $line;
            }
        } else {
            $newFileLines[] = $line;
        }
    }
    fclose($realTargetFile);

    echo sprintf('Found %s warning(s) in %s.%s', count($ignoredHashMap), $baselineFilePath, PHP_EOL);
    echo sprintf('Removed %s warning(s) from %s, %s warning(s) remain.%s', $removedWarningCount, $targetFilePath, $remainingWarningCount, PHP_EOL);

    file_put_contents($realTargetFilePath, implode('', $newFileLines));
}

function getRealFilePath(string $filePath): string
{
    if ('.' === $filePath[0]) {
        $realFilePath = realpath(getcwd() . '/' . $filePath);
    } else {
        $realFilePath = $filePath;
    }

    if (!file_exists($realFilePath)) {
        throw new Exception(sprintf('File "%s" does not exist!', $filePath));
    }

    return $realFilePath;
}
