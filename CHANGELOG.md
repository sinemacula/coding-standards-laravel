# Changelog

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
