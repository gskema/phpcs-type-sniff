# Changelog

All notable changes to `phpcs-type-sniff` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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
- Added warning for array type inside compound parameter type.

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
- Description "ClassA Constructor." is now ignored and not considered useful.
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
