# Changelog

## [1.6.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.5.0...v1.6.0) (2026-09-04)


### Features

* limit a model to its schema and its relations ([#93](https://github.com/sinemacula/coding-standards-laravel/issues/93)) ([7e0b2a3](https://github.com/sinemacula/coding-standards-laravel/commit/7e0b2a3e85c33504ec7432aac285e9de12939a63))
* require foreign key columns to be declared from their model ([#88](https://github.com/sinemacula/coding-standards-laravel/issues/88)) ([d456f83](https://github.com/sinemacula/coding-standards-laravel/commit/d456f83106942d88d86967c5d95ef0c78126aeeb))


### Bug Fixes

* exempt classes the framework constructs from the service location rule ([#89](https://github.com/sinemacula/coding-standards-laravel/issues/89)) ([f0d130f](https://github.com/sinemacula/coding-standards-laravel/commit/f0d130f4a576035ebfb3f3583e8ebedce428dde8))
* identify uninjectable bases by the class they name ([#91](https://github.com/sinemacula/coding-standards-laravel/issues/91)) ([85c50d3](https://github.com/sinemacula/coding-standards-laravel/commit/85c50d3346a53eb2f8d40b8eb80c07072dfba7b1))
* ignore the builder calls larastan reflects as static methods ([#84](https://github.com/sinemacula/coding-standards-laravel/issues/84)) ([c5418b3](https://github.com/sinemacula/coding-standards-laravel/commit/c5418b39171935373eb4fbf1ba2c83f63b2f9a4e))
* make the property rules inheritance-aware across the framework, not just models ([#81](https://github.com/sinemacula/coding-standards-laravel/issues/81)) ([cd4b065](https://github.com/sinemacula/coding-standards-laravel/commit/cd4b065d5feb40e2aef1a27afc3ca628ee5e9910))
* pin the model migration scan to the project being analysed ([#85](https://github.com/sinemacula/coding-standards-laravel/issues/85)) ([4fa7361](https://github.com/sinemacula/coding-standards-laravel/commit/4fa736140b4cefba894a0335cdb0ad2b52cf14a5))
* report a lagging Laravel floor instead of hiding the model attributes ([#83](https://github.com/sinemacula/coding-standards-laravel/issues/83)) ([45e32f2](https://github.com/sinemacula/coding-standards-laravel/commit/45e32f217fd5ffef530bb56be3d030d60d564f2f))
* stop documenting a PHPStan level that never applies ([#86](https://github.com/sinemacula/coding-standards-laravel/issues/86)) ([f8c62c8](https://github.com/sinemacula/coding-standards-laravel/commit/f8c62c806de184fb1f8a4ab88f1ecd8d87e85ba6))
* stop flagging the print_r form that returns instead of printing ([#87](https://github.com/sinemacula/coding-standards-laravel/issues/87)) ([b47392c](https://github.com/sinemacula/coding-standards-laravel/commit/b47392cfd8153a6b9b62a0b9a9b891b49cae0f43))

## [1.5.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.4.0...v1.5.0) (2026-07-25)


### Features

* treat Laravel container and validation exceptions as unchecked ([#75](https://github.com/sinemacula/coding-standards-laravel/issues/75)) ([2145451](https://github.com/sinemacula/coding-standards-laravel/commit/21454510bcac2180a9f9ea123d960f03d7402479))


### Bug Fixes

* silence false dead-catch on composer/semver constraint parsing ([#76](https://github.com/sinemacula/coding-standards-laravel/issues/76)) ([bfbdbed](https://github.com/sinemacula/coding-standards-laravel/commit/bfbdbed06e5652d16404080e44235cba14a4ede5))

## [1.4.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.3.2...v1.4.0) (2026-07-25)


### Features

* fillable-casts rule and resource route collapse sniff ([#70](https://github.com/sinemacula/coding-standards-laravel/issues/70)) ([2472b29](https://github.com/sinemacula/coding-standards-laravel/commit/2472b29fb8ace658ab87fadd690e6a2e04e6382d))

## [1.3.2](https://github.com/sinemacula/coding-standards-laravel/compare/v1.3.1...v1.3.2) (2026-07-22)


### Bug Fixes

* resolve role identity collisions and exemption gaps ([#65](https://github.com/sinemacula/coding-standards-laravel/issues/65)) ([8965762](https://github.com/sinemacula/coding-standards-laravel/commit/89657622bfbae338cb2e093f457acee5622ed6ca))

## [1.3.1](https://github.com/sinemacula/coding-standards-laravel/compare/v1.3.0...v1.3.1) (2026-07-22)


### Bug Fixes

* resolve braced namespaces and comment-separated instantiations ([#62](https://github.com/sinemacula/coding-standards-laravel/issues/62)) ([9f70a8d](https://github.com/sinemacula/coding-standards-laravel/commit/9f70a8d9ec17f0315267a0a11d7cc11e49c860bd))

## [1.3.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.2.1...v1.3.0) (2026-06-23)


### Features

* **architecture:** refine DisallowServiceLocation exemptions ([#36](https://github.com/sinemacula/coding-standards-laravel/issues/36)) ([93183e5](https://github.com/sinemacula/coding-standards-laravel/commit/93183e5fd078eb176676635b5c305a673cfa9d6d))
* **classes:** exempt Eloquent models from readonly-public-property rule ([#37](https://github.com/sinemacula/coding-standards-laravel/issues/37)) ([2e88a8b](https://github.com/sinemacula/coding-standards-laravel/commit/2e88a8bac2990cd6a773f9817ac6f44a7b664d07))


### Bug Fixes

* **phpstan:** exempt leading-underscore meta-keys from resource field naming ([#38](https://github.com/sinemacula/coding-standards-laravel/issues/38)) ([08565b0](https://github.com/sinemacula/coding-standards-laravel/commit/08565b001404c1722e7bdd98f9720fae191ad20e))
* **typehints:** exempt non-private trait method params from ParameterTypeHint ([#34](https://github.com/sinemacula/coding-standards-laravel/issues/34)) ([4428769](https://github.com/sinemacula/coding-standards-laravel/commit/44287698ea70a5417f3978e3797f752231edbb2a))

## [1.2.1](https://github.com/sinemacula/coding-standards-laravel/compare/v1.2.0...v1.2.1) (2026-06-23)


### Bug Fixes

* **typehints,phpstan:** complete magic-property set + gate castsProperty ([#32](https://github.com/sinemacula/coding-standards-laravel/issues/32)) ([a8fe6e7](https://github.com/sinemacula/coding-standards-laravel/commit/a8fe6e76578305eee0631fd0d44210a0ded7f651))

## [1.2.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.1.0...v1.2.0) (2026-06-22)


### Features

* **typehints:** Laravel-aware native type-hint sniffs ([#30](https://github.com/sinemacula/coding-standards-laravel/issues/30)) ([797ae22](https://github.com/sinemacula/coding-standards-laravel/commit/797ae22353cc61cf394ae080cef40523bca8ac35))

## [1.1.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.0.1...v1.1.0) (2026-06-22)


### Features

* **configuration:** exempt test code from DisallowEnvOutsideConfig ([#21](https://github.com/sinemacula/coding-standards-laravel/issues/21)) ([ac30ab9](https://github.com/sinemacula/coding-standards-laravel/commit/ac30ab965030f785a392482055747c565c2e108f))
* **controllers:** judge only genuine route actions in DisallowNonRestActions ([#22](https://github.com/sinemacula/coding-standards-laravel/issues/22)) ([69d7eea](https://github.com/sinemacula/coding-standards-laravel/commit/69d7eeac2a769793f5a1906f3b7f5eeab032cc9e))
* **eloquent:** flag only genuine model accessors in DisallowLegacyAttributeAccessor ([#23](https://github.com/sinemacula/coding-standards-laravel/issues/23)) ([6f5add7](https://github.com/sinemacula/coding-standards-laravel/commit/6f5add72db7494865c524efe6066b626accc53d2))
* **phpstan:** make the modelAttribute 13.2 attributes version-aware ([#26](https://github.com/sinemacula/coding-standards-laravel/issues/26)) ([2beb1f8](https://github.com/sinemacula/coding-standards-laravel/commit/2beb1f861dc26c194c8681db2adb2acf221906d0))
* **phpstan:** make the modelAttribute mandated set configurable ([#24](https://github.com/sinemacula/coding-standards-laravel/issues/24)) ([1fa50a5](https://github.com/sinemacula/coding-standards-laravel/commit/1fa50a55dff42d69d46accae8731016c73c857d3))
* **phpstan:** refine massAssignment - exempt tests, recognise attribute form ([#25](https://github.com/sinemacula/coding-standards-laravel/issues/25)) ([f74ec29](https://github.com/sinemacula/coding-standards-laravel/commit/f74ec29499e3efc3fe662476d2f38f91e220b51d))
* **phpstan:** require snake_case field names in API resources ([#28](https://github.com/sinemacula/coding-standards-laravel/issues/28)) ([f912dcd](https://github.com/sinemacula/coding-standards-laravel/commit/f912dcd5365af39d2ae3232f1262f1ce4d4df192))
* **phpstan:** require snake_case table and column names in migrations ([#27](https://github.com/sinemacula/coding-standards-laravel/issues/27)) ([ec40702](https://github.com/sinemacula/coding-standards-laravel/commit/ec407025aa17a024935572e210aad46e8e4d8058))
* **structure:** rebuild role sniffs around class identity ([#19](https://github.com/sinemacula/coding-standards-laravel/issues/19)) ([6b55729](https://github.com/sinemacula/coding-standards-laravel/commit/6b557297d81176397dd8c311ebfacb4d4234bf92))

## [1.0.1](https://github.com/sinemacula/coding-standards-laravel/compare/v1.0.0...v1.0.1) (2026-06-21)


### Bug Fixes

* resolve qualified names under PHP_CodeSniffer 4.x ([#15](https://github.com/sinemacula/coding-standards-laravel/issues/15)) ([0c8cd19](https://github.com/sinemacula/coding-standards-laravel/commit/0c8cd19057b95e09132b7b16112ccdd3b8144b30))

## 1.0.0 (2026-06-21)


### Features

* blade placement, method attributes, form-request rules, factory timestamps ([#12](https://github.com/sinemacula/coding-standards-laravel/issues/12)) ([8c4177e](https://github.com/sinemacula/coding-standards-laravel/commit/8c4177e26883838a9fc37e3f78f5d7b93b238bf6))
* first rule chunk - debug, env, Eloquent properties + accessors ([#2](https://github.com/sinemacula/coding-standards-laravel/issues/2)) ([c070789](https://github.com/sinemacula/coding-standards-laravel/commit/c07078923d9ef30837247816acd0a08ff404478a))
* scaffold the Laravel coding-standards package ([#1](https://github.com/sinemacula/coding-standards-laravel/issues/1)) ([6e5108e](https://github.com/sinemacula/coding-standards-laravel/commit/6e5108e31aab144c7411f8c9360a59cfcf0dadc5))
