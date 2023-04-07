# Changelog

All notable changes to `phpcs-type-sniff` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 80.2.0 - 2023-04-07
### Changed
- Remove upper bound PHP requirement so package can be installed on higher PHP versions even if locked.

## 80.1.0 - 2022-07-28
### Changed
- Change package PHP requirements to allow current PHP version + 1 next major version for forward migration.

## 80.0.0 - 2022-07-15
### Added
- Support for PHP8: promoted constructor properties, static type, mixed type, false type, null type, union type.
- Option `inspectPromotedConstructorPropertyAs`.
- Warning for nullable shorthand syntax, e.g. `?int` over `int|null`.

## 74.1.2 - 2022-07-04
### Fixed
- `trim(null)` deprecation warnings for PHP 8.1+

## 74.1.1 - 2022-01-18
### Fixed
- Option description in README

## 74.1.0 - 2022-01-18
### Changed
- Default value of `addViolationId` to true.
- `phpcs-subtract-baseline`: removed 3rd and 4th parameters.
- `phpcs-subtract-baseline`: added `--trim-basepath` multi value option. Basepaths are trimmed for both target and baseline files.
- `phpcs-subtract-baseline`: changed argument order.
- `phpcs_baseline.php`: both 3rd and 4th parameters are always used as trim basepaths for both target and baseline files.
### Deprecated
- `phpcs_baseline.php` script.

## 74.0.5 - 2021-12-16
### Fixed
- Useless prop PHPDoc check when `@var` is not present.

## 74.0.4 - 2021-07-12
### Fixed
- `@var` tag parsing with `array<...>` notation.

## 74.0.3 - 2021-07-12
### Changed
- Default value of setting `FqcnMethodSniff.reportMissingTags` to `false`.

## 74.0.2 - 2021-07-08
### Fixed
- Removed required type decl. warning for extended class properties. Detected by lazy reflection.

## 74.0.1 - 2021-06-18
### Added
- Base path args for `phpcs-subtract-baseline` script for diffing reports build on different environments.

## 74.0.0 - 2021-05-10
## Added
- Support for typed properties + warnings.
- Warnings for nullable types (e.g. `?int`) in PHPDoc.
### Removed
- Setting `FqcnPropSniff.reportUninitializedProp`.

## 0.18.0 - 2021-05-10
### Fixed
- Return tag parsing with `array<...>` notation.
## Added
- Support for `ArrayShape` attribute using single line `#` comment.
### Changed
- Minimum `squizlabs/php_codesniffer` version to `3.6.0`

## 0.17.0 - 2021-04-26
### Added
- Baseline support for tracking warnings from other sniffs. `filename + line + column + message` hash is used.
- Bin executable `phpcs-subtract-baseline`

## 0.16.1 - 2021-03-08
### Changed
- Exit code of CLI script `phpcs_baseline.php`

## 0.16.0 - 2021-03-08
### Added
- Setting `addViolationId`
- Script `phpcs_baseline.php` to subtract old warnings (baseline) from `phpcs` XML reports.

## 0.15.0 - 2021-01-04
### Changed
- Autoload file configuration examples in README
### Added
- Setting `reportType`
- Setting `FqcnConstSniff.reportType`
- Setting `FqcnDescriptionSniff.reportType`
- Setting `FqcnMethodSniff.reportType`
- Setting `FqcnPropSniff.reportType`

## 0.14.1 - 2020-04-14
### Fixed
- Doc type `resource` cannot be type declaration, do not require
- Nullable basic getter warning when no dock block does not contain tags

## 0.14.0 - 2020-02-26
### Added
- Warning for uninitialized class/trait property. Default value and assignments in `__construct()` are checked. Adding null doc type is suggested.
- Warning for nullable return types for basic getters based on property doc type.
- Setting `FqcnPropSniff.reportUninitializedProp`
- Setting `FqcnPropSniff.reportNullableBasicGetter`

## 0.13.1 - 2020-01-17
### Added
- Warning for undefined type typed array, e.g. []|null
### Fixed
- Parsing of undefined type typed array, e.g. [][]

## 0.13.0 - 2019-12-09
### Removed
- Setting `FqcnMethodSniff.usefulTags`
### Added
- Parsing of alternative PHPDoc array types (array shapes, object-like arrays) as mixed[]
- Setting `FqcnMethodSniff.invalidTags`. Specifies which methods tags should be removed.
- Setting `FqcnMethodSniff.reportMissingTags`.
### Fixed
- Parsing of compound typed array types, e.g. (int[]|string)[]
- Parsing of generic tags with parentheses, no longer part of tag name, Dynamic content is now tag content.
- All tags except `param`, `return` make method PHPDoc useful.

## 0.12.4 - 2019-11-21
### Fixed
- Useless PHPDoc for const is no longer reported when doc type is incomplete (e.g. array)

## 0.12.3 - 2019-11-15
### Fixed
- Fix PHPDoc tag parsing which are prefaced with multiple spaces

## 0.12.2 - 2019-08-07
### Fixed
- FqcnConstSniff now checks for other tags before declaring useless PHPDoc.

## 0.12.1 - 2019-07-31
### Fixed
- TypedArrayType is now covers FqcnType, e.g. @return Collection|Image[] -> :Collection

## 0.12.0 - 2019-07-29
### Added
- Detection for "parent" type
- Warning when only using null parameter doc type, compound type is suggested
- Warning when using null return type, removal or compound type is suggested
- Warning for doc types that are incompatible with declared function types
- More accurate warning for missing doc types (detected from function type)
- Nullable function type is suggested from null doc type
- Warning when using return void tag when type declaration is specified
- Warning for incomplete type array types, e.g. array[]
- Warning for wrong, missing types in const, PHPDoc tags.
- Useless FQCN description warning
- Useless __construct description warning
- Detection of function parameter default value type
- Warning for redundant doc types, e.g. float|double
- Warning to use nullable parameter type instead of type + null default value
- Warning for useless const PHPDoc
### Changed
- self, typed array type examples when for missing function type warnings
- FqcnMethodSniff logic. Removed dead end inspection paths, code duplication
- Updated warnings texts
### Fixed
- ResourceType is now DocBlock type, because cannot be used as PHP type declaration
- If @param is missing, doc block is not deemed useless, but needs to be fixed

## 0.11.1 - 2019-07-12
### Fixed
- Typed array prop, const check when compound type is used

## 0.11.0 - 2019-07-10
### Added
- Default value type for prop elements
- Value type for const elements
- Array type warnings for const elements, prop elements
### Fixed
- ParseError is now ignored when using reflection
- Trait prop detection
- Sniff toggling using "*.enabled" config option
### Removed
- Variable name check for const elements

## 0.10.5 - 2019-06-14
### Added
- Added warning for array type inside compound parameter type

## 0.10.4 - 2019-06-09
### Fixed
- Parameter default value detection when self, double colon is used

## 0.10.3 - 2019-05-27
### Fixed
- Useless doc block detection when comparing `CompoundType` and `NullableType`, raw types are now sorted.

## 0.10.2 - 2019-05-27
### Fixed
- Usage of FqcnMethodSniff.usefulTags config option

## 0.10.1 - 2019-05-27
### Fixed
- Added support for tags with parentheses, e.g. @SmartTemplate()

## 0.10.0 - 2019-05-27
### Added
- Description "ClassA Constructor." is now ignored and not considered useful
- Configuration for `CompositeCodeElementSniff`
- Ability to add and configure custom `CodeElementSniffInterface` sniffs
- Option `useReflection`
- Option `sniffs`
- Option `FqcnMethodSniff.usefulTags`
### Fixed
- ReturnTag::getDescription()
- FunctionParam::getParam()
- FunctionParam::hasParam()
- SelfType::__construct()

## 0.9.0 - 2019-05-06
### Added
- Initial release
