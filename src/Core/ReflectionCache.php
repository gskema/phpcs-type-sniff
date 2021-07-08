<?php

namespace Gskema\TypeSniff\Core;

use ParseError;
use ReflectionClass;
use ReflectionException;

class ReflectionCache
{
    /**
     * @param string $fqcn
     * @param bool   $includeOwn
     *
     * @return string[]
     * @throws ReflectionException
     */
    public static function getMethodsRecursive(string $fqcn, bool $includeOwn): array
    {
        try {
            $classRef = new ReflectionClass($fqcn);
        } catch (ParseError $e) {
            return []; // suppress error popups when editing .php file
        }

        $methodNames = [];
        if ($includeOwn) {
            foreach ($classRef->getMethods() as $methodRef) {
                $methodNames[] = $methodRef->getName();
            }
        }

        $parentClasses = $classRef->getInterfaceNames();
        if ($classRef->getParentClass()) {
            $parentClasses[] = $classRef->getParentClass()->getName();
        }

        foreach ($parentClasses as $parentClass) {
            $parentMethods = static::getMethodsRecursive($parentClass, true);
            $methodNames = array_merge($methodNames, $parentMethods);
        }

        return $methodNames;
    }

    /**
     * @param string $fqcn
     * @param bool   $includeOwn
     *
     * @return string[]
     */
    public static function getPropsRecursive(string $fqcn, bool $includeOwn): array
    {
        try {
            $classRef = new ReflectionClass($fqcn);
        } catch (ParseError $e) {
            return []; // suppress error popups when editing .php file
        }

        $propNames = [];
        if ($includeOwn) {
            foreach ($classRef->getProperties() as $propRef) {
                $propNames[] = $propRef->getName();
            }
        }

        $parentClasses = $classRef->getTraitNames();
        if ($classRef->getParentClass()) {
            $parentClasses[] = $classRef->getParentClass()->getName();
        }

        foreach ($parentClasses as $parentClass) {
            $parentPropNames = static::getPropsRecursive($parentClass, true);
            $propNames = array_merge($propNames, $parentPropNames);
        }

        return $propNames;
    }
}
