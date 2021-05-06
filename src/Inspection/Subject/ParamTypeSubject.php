<?php

namespace Gskema\TypeSniff\Inspection\Subject;

use Gskema\TypeSniff\Core\DocBlock\DocBlock;
use Gskema\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Gskema\TypeSniff\Core\Func\FunctionParam;

class ParamTypeSubject extends AbstractTypeSubject
{
    /**
     * @param FunctionParam $param
     * @param ParamTag|null $tag
     * @param DocBlock      $docBlock
     * @param string        $id
     *
     * @return static
     */
    public static function fromParam(FunctionParam $param, ?ParamTag $tag, DocBlock $docBlock, string $id)
    {
        return new static(
            $tag ? $tag->getType() : null,
            $param->getType(),
            $param->getValueType(),
            $tag ? $tag->getLine() : $param->getLine(),
            $param->getLine(),
            sprintf('parameter $%s', $param->getName()),
            $docBlock,
            $param->getAttributeNames(),
            $id
        );
    }
}
