<?php // phpcs:ignore SR1.Files.SideEffects.FoundWithSymbols

$baselineFilePath = $argv[1] ?? null;
$targetFilePath = $argv[2] ?? null;
$targetBasePath = $argv[3] ?? null; // optional
$baselineBasePath = $argv[4] ?? null; // optional

$exitCode = diffBaseline($baselineFilePath, $targetFilePath, $targetBasePath, $baselineBasePath);

exit($exitCode);

function diffBaseline(
    string $baselineFilePath,
    string $targetFilePath,
    ?string $targetBasePath,
    ?string $baselineBasePath
): int {
    $targetBasePath = $targetBasePath ? resolveAbsolutePath($targetBasePath) : null;
    $baselineBasePath = $baselineBasePath ? resolveAbsolutePath($baselineBasePath) : null;

    $realBaselineFilePath = getRealFilePath($baselineFilePath);
    $realTargetFilePath = getRealFilePath($targetFilePath);

    // Collect violationIds from baseline file, put them into a map for faster lookup
    // If violationId is not found, the use single filename + line + error hash. This works for simple errors e.g. PSR-12
    $errorFileLine = '';
    $violationIdMap = [];
    $lineHashMap = [];
    $baselineFile = fopen($realBaselineFilePath, 'r');
    while (!feof($baselineFile)) {
        $line = fgets($baselineFile);
        if (false !== strpos($line, '<file')) {
            $errorFileLine = $line;
            continue;
        }

        if (false === strpos($line, '<error')) {
            continue;
        }

        if ($violationId = findViolationId($line)) {
            $violationIdMap[$violationId] = null;
        } else {
            $lineHash = getLineHash($errorFileLine, $line, $baselineBasePath);
            $lineHashMap[$lineHash] = null;
        }
    }
    fclose($baselineFile);

    // Iterate over report file, keep errors only if they are not in the ignored error maps.
    // If a file segment contains new errors, then keep file segment with those new errors.
    // In the end, write collected file (with errors) segments to the same file.
    $remainingWarningCount = 0;
    $removedWarningCount = 0;
    $newFileLines = [];
    $errorFileLine = '';
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
            $errorFileLine = '';
            $errorLines = [];
        } elseif (false !== strpos($line, '<error')) {
            $violationId = findViolationId($line);
            if (null !== $violationId && key_exists($violationId, $violationIdMap)) {
                $removedWarningCount++;
            } elseif (key_exists(getLineHash($errorFileLine, $line, $targetBasePath), $lineHashMap)) {
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

    echo sprintf('Found %s warning(s) in %s.%s', count($violationIdMap) + count($lineHashMap), $baselineFilePath, PHP_EOL);
    echo sprintf('Removed %s warning(s) from %s, %s warning(s) remain.%s', $removedWarningCount, $targetFilePath, $remainingWarningCount, PHP_EOL);

    if ($removedWarningCount > 0) {
        file_put_contents($realTargetFilePath, implode('', $newFileLines));
    }

    return $remainingWarningCount > 0 ? 1 : 0;
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

function getLineHash(string $fileLine, string $errorLine, ?string $basePath): string
{
    if (null !== $basePath) {
        $fileLine = str_replace('<file name="' . $basePath, '<file name="', $fileLine);
    }

    return md5(trim($fileLine) . trim($errorLine));
}

function findViolationId(string $line): ?string
{
    $matches = [];
    preg_match('#\[(\w{16})\]#', $line, $matches);

    return $matches[1] ?? null;
}

function resolveAbsolutePath(string $path): string
{
    $absolutePath = false !== strpos($path, '.') ? realpath($path) : $path;
    if (empty($absolutePath)) {
        throw new Exception(sprintf('Cannot resolve path to absolute: %s', $path));
    }

    return rtrim($absolutePath, '/') . '/';
}
