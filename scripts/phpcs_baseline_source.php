<?php // phpcs:ignore SR1.Files.SideEffects.FoundWithSymbols

/**
 * @param string   $baselineFilePath
 * @param string   $targetFilePath
 * @param string[] $trimBasePaths
 *
 * @return int
 */
function diffBaseline(
    string $baselineFilePath,
    string $targetFilePath,
    array $trimBasePaths
): int {
    $trimBasePaths = array_map('resolveAbsolutePath', $trimBasePaths);

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
        if (str_contains($line, '<file')) {
            $errorFileLine = $line;
            continue;
        }

        if (!str_contains($line, '<error')) {
            continue;
        }

        if ($violationId = findViolationId($line)) {
            $violationIdMap[$violationId] = null;
        } else {
            $lineHash = getLineHash($errorFileLine, $line, $trimBasePaths);
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

        if (str_contains($line, '<file')) {
            $errorFileLine = $line;
        } elseif (str_contains($line, '</file')) {
            if (!empty($errorLines)) {
                array_push($newFileLines, $errorFileLine, ...$errorLines);
                array_push($newFileLines, $line);
            }
            $errorFileLine = '';
            $errorLines = [];
        } elseif (str_contains($line, '<error')) {
            $violationId = findViolationId($line);
            if (null !== $violationId && key_exists($violationId, $violationIdMap)) {
                $removedWarningCount++;
            } elseif (key_exists(getLineHash($errorFileLine, $line, $trimBasePaths), $lineHashMap)) {
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

/**
 * @param string   $fileLine
 * @param string   $errorLine
 * @param string[] $trimBasePaths
 *
 * @return string
 */
function getLineHash(string $fileLine, string $errorLine, array $trimBasePaths): string
{
    $count = 0;
    foreach ($trimBasePaths as $trimBasePath) {
        $fileLine = str_replace('<file name="' . $trimBasePath, '<file name="', $fileLine, $count);
        if ($count > 0) {
            break;
        }
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
    $absolutePath = str_starts_with($path, '.') ? realpath($path) : $path;
    $absolutePath = $absolutePath ?: $path;

    return rtrim($absolutePath, '/') . '/';
}
