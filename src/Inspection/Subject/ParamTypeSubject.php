<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\Func\FunctionParam;

class ParamTypeSubject extends AbstractTypeSubject
{
    public static function fromParam(FunctionParam $param, ?ParamTag $tag, DocBlock $docBlock, string $id): static
    {
        return new static(
            $tag?->getType(),
            $param->getType(),
            $param->getValueType(),
            $tag ? $tag->getLine() : $param->getLine(),
            $param->getLine(),
            sprintf('parameter $%s', $param->getName()),
            $docBlock,
            $param->getAttributeNames(),
            $id,
        );
    }
}
