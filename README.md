# PHPCS Type Sniff

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

- Enforces usage of PHP 7 type declarations
- Enforces documenting array types with more accurate types
- Checks for useless PHPDoc blocks

## Install

Via Composer

```bash
$ composer require --dev gskema/phpcs-type-sniff
```

## Usage

This is a standalone sniff file, you need to add it to your `phpcs.xml` file.

### Usage With Reflection

With reflection enabled, this sniff can assert if `@inheritoc` tag
is needed. Inspections for extended/implemented methods are skipped.
Reflections need to load actual classes, which is why we need to include
the autoloader.

```xml
<ruleset name="your_ruleset">
    <!-- your configuration -->
    <arg name="bootstrap" value="./vendor/autoload.php"/>
    <rule ref="./vendor/gskema/phpcs-type-sniff/src/Sniffs/CompositeCodeElementSniff.php">
        <properties>
            <property name="useReflection" value="true"/>
        </properties>
    </rule>
</ruleset>
```

### Usage Without Reflection

Inspections for methods with `@inheritdoc` tag are skipped.
If a method does not have this tag, it is inspected.

```xml
<ruleset name="your_ruleset">
    <!-- your configuration -->
    <rule ref="./vendor/gskema/phpcs-type-sniff/src/Sniffs/CompositeCodeElementSniff.php"/>
</ruleset>
```

## Configuration

Sniffs are registered and saved by their short class name.
This allows easily specifying configuration options for a specific code element sniff,
e.g. `FqcnMethodSniff.usefulTags`. All custom code sniff classes must have unique
short class names.

String `true/false` values are automatically converted to booleans.

```xml
<ruleset name="your_ruleset">
    <!-- your configuration -->

    <!-- Includes an autoloader which is needed when using reflection API -->
    <!-- or custom code element sniff(s) -->
    <arg name="bootstrap" value="./vendor/autoload.php"/>

    <!-- Includes a standalone sniff to your custom coding standard -->
    <rule ref="./vendor/gskema/phpcs-type-sniff/src/Sniffs/CompositeCodeElementSniff.php">
        <properties>

            <!-- Enables usage of reflection API when inspecting extended classes. -->
            <!-- Autoloader is needed. -->
            <property name="useReflection" value="true"/>

            <!-- Disables one of the default code element sniffs -->
            <property name="FqcnConstSniff.enabled" value="false" />
            <property name="FqcnMethodSniff.enabled" value="false" />
            <property name="FqcnPropSniff.enabled" value="false" />
            <property name="FqcnDescriptionSniff.enabled" value="false" />

            <!-- Adds additional useful PHPDoc tags for asserting useful DocBlock(s) -->
            <property name="FqcnMethodSniff.usefulTags" type="array">
                <element value="@someTag1"/>
                <element value="@someTag2"/>
            </property>

            <!-- Custom pattern and tags for asserting useless FQCN descriptions -->
            <property name="FqcnDescriptionSniff.invalidPatterns" type="array">
                <element value="^Nothing.+Useful$"/>
            </property>
            <property name="FqcnDescriptionSniff.invalidTags" type="array">
                <element value="@api"/>
            </property>

            <!-- Your own custom code element sniff(s). Autoloader is needed. -->
            <!-- These classes implement CodeElementSniffInterface -->
            <property name="sniffs" type="array">
                <element value="\Acme\CustomCodeElementSniff" />
                <element value="\Acme\AnotherCustomMethodSniff" />
            </property>

            <!-- Configuration options for custom code element sniffs -->
            <property name="CustomCodeElementSniff.opt1" value="str1" />
            <!-- Specifying element key(s) will create an associative array -->
            <property name="AnotherCustomMethodSniff.arrayOpt1" type="array">
                <element key="key1" value="str1"/>
                <element key="key2" value="str2"/>
            </property>

        </properties>
    </rule>
</ruleset>
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/gskema/phpcs-type-sniff.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/gskema/phpcs-type-sniff/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/gskema/phpcs-type-sniff.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/gskema/phpcs-type-sniff.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/gskema/phpcs-type-sniff.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/gskema/phpcs-type-sniff
[link-travis]: https://travis-ci.org/gskema/phpcs-type-sniff
[link-scrutinizer]: https://scrutinizer-ci.com/g/gskema/phpcs-type-sniff/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/gskema/phpcs-type-sniff
[link-downloads]: https://packagist.org/packages/gskema/phpcs-type-sniff
