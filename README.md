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

This is a standalone sniff, you need to add it to your `phpcs.xml` file.

### Usage With Reflection

With reflection enabled, this sniff can assert if @inheritoc tag
is needed. Inspections for extended/implemented methods are skipped.
Reflections need to load actual classes, which is why we need to include
the autoloader.

```xml
<ruleset name="your_ruleset">
    <!-- your configuration -->
    <arg name="bootstrap" value="./vendor/autoload.php"/>
    <rule ref="./vendor/gskema/phpcs-type-sniff/src/Sniffs/CompositeCodeElementSniff.php"/>
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
