<?php

namespace Gskema\TypeSniff\Core\DocBlock;

use PHP_CodeSniffer\Files\File;
use RuntimeException;
use Gskema\TypeSniff\Core\DocBlock\Tag\GenericTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Gskema\TypeSniff\Core\DocBlock\Tag\TagInterface;
use Gskema\TypeSniff\Core\DocBlock\Tag\VarTag;
use Gskema\TypeSniff\Core\Type\TypeFactory;

/**
 * @see DocBlockParserTest
 */
class DocBlockParser
{
    public static function fromTokens(File $file, int $docOpenPtr, int $docClosePtr): DocBlock
    {
        $rawDocBlock = $file->getTokensAsString($docOpenPtr, $docClosePtr - $docOpenPtr + 1);
        $startLineNum = $file->getTokens()[$docOpenPtr]['line'];

        return static::fromRaw($rawDocBlock, $startLineNum);
    }

    public static function fromRaw(string $rawDocBlock, int $startLineNum): DocBlock
    {
        // This regex always matches, not need to check for 0|false
        preg_match_all('#^[ \t]*\/*\**\**[ \t]?(.*?)[ \t]*\**\/*$#m', $rawDocBlock, $matches);

        $descLines = [];
        $tagLineNum = null;
        $rawTags = [];

        $lineNum = $startLineNum;
        foreach ($matches[1] ?? [] as $rawLine) {
            $ch0 = $rawLine[0] ?? null;
            $ch1 = $rawLine[1] ?? null;
            if ('@' === $ch0 || ('{' === $ch0 && '@' === $ch1)) {
                $tagLineNum = $lineNum;
            }

            if (null !== $tagLineNum) {
                $rawTags[$tagLineNum] = ($rawTags[$tagLineNum] ?? '').' '.trim($rawLine);
            } elseif (!empty($rawLine) || !empty($descLines)) {
                // Always append non-empty lines, but also append empty lines if we have
                // non-empty lines already. More non-empty lines might follow.
                $descLines[$lineNum] = $rawLine;
            }

            $lineNum++;
        }

        end($descLines);
        while (null !== key($descLines) && empty(current($descLines))) {
            $key = key($descLines);
            prev($descLines);
            unset($descLines[$key]);
        }

        $tags = [];
        foreach ($rawTags as $lineNum => $rawTag) {
            $tags[] = static::parseTag($lineNum, trim($rawTag));
        }

        return new DocBlock($descLines, $tags);
    }

    protected static function parseTag(int $line, string $rawTag): TagInterface
    {
        $tag = null;
        if (preg_match('#^@param\s+(.*?)\s*(\.\.\.)?\$(\w+)\s*(.*)$#', $rawTag, $matches)) {
            $type = TypeFactory::fromRawType($matches[1] ?? '');
            // $isVariableLength = !empty($matches[2]);
            // $isPassedByReference = ? // @TODO
            $paramName = $matches[3];
            $description = $matches[4] ?? null;
            $tag = new ParamTag($line, $type, $paramName, $description);
        } elseif (preg_match('#^@var\s*([^ ]*)\s*(\$\w*)?\s*(.*)$#', $rawTag, $matches)) {
            // Allows malformed @var tag without any body
            $type = TypeFactory::fromRawType($matches[1] ?? '');
            $paramName = !empty($matches[2]) ? substr($matches[2], 1) : null;
            $description = $matches[3] ?? null;
            $tag = new VarTag($line, $type, $paramName, $description);
        } elseif (preg_match('#^@return\s+([^\s]+)\s*(.*)$#', $rawTag, $matches)) {
            $type = TypeFactory::fromRawType($matches[1] ?? '');
            $description = $matches[2] ?? null;
            $tag = new ReturnTag($line, $type, $description);
        } elseif (preg_match('#^{?@([\w\(\)]+)}?(?:$|\s*(.*)$)#', $rawTag, $matches)) {
            $tagName = strtolower($matches[1]);
            $content = $matches[2] ?? null;
            $tag = new GenericTag($line, $tagName, $content);
        } else {
            throw new RuntimeException('Cannot parse DocBlock tag');
        }

        return $tag;
    }
}
