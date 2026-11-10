# Changelog

## [1.7.1](https://github.com/sinemacula/coding-standards-laravel/compare/v1.7.0...v1.7.1) (2026-11-10)


### Bug Fixes

* **deps:** require a released sinemacula/coding-standards ([#143](https://github.com/sinemacula/coding-standards-laravel/issues/143)) ([eeabba7](https://github.com/sinemacula/coding-standards-laravel/commit/eeabba7265088044dc79d335cf0e78ac7c836288))

## [1.7.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.6.3...v1.7.0) (2026-11-10)


### Features

* prefer the queue attributes over the properties they replace ([#130](https://github.com/sinemacula/coding-standards-laravel/issues/130)) ([1c70d93](https://github.com/sinemacula/coding-standards-laravel/commit/1c70d9358b42886cc48de2f2c6822e6f6ded76ac))


### Bug Fixes

* allow service location in the failed() hook of a queued payload ([#140](https://github.com/sinemacula/coding-standards-laravel/issues/140)) ([eb13db0](https://github.com/sinemacula/coding-standards-laravel/commit/eb13db01ac5f5cc5d580eb41117641599060d232))
* clear the method-count and PHPStan findings, and drop redundant qlty config ([#142](https://github.com/sinemacula/coding-standards-laravel/issues/142)) ([c45ae41](https://github.com/sinemacula/coding-standards-laravel/commit/c45ae418e3b9260e1387e7f128deeb3d75e55ede))
* exempt policy abilities and queue settings from the base naming rules ([#129](https://github.com/sinemacula/coding-standards-laravel/issues/129)) ([3336f58](https://github.com/sinemacula/coding-standards-laravel/commit/3336f58fb6fe19652065415a8baa95ec15f9b285))
* keep queued notifications, mailables and listeners out of the job role ([#139](https://github.com/sinemacula/coding-standards-laravel/issues/139)) ([a29e3e8](https://github.com/sinemacula/coding-standards-laravel/commit/a29e3e861ac3f8b3228ff06070b8325486dbbf42))
* leave translation strings out of the line length check ([#132](https://github.com/sinemacula/coding-standards-laravel/issues/132)) ([24a72db](https://github.com/sinemacula/coding-standards-laravel/commit/24a72dbea0afa03a66ce40b920af106dee975a08))
* let API resources resolve from the container ([#131](https://github.com/sinemacula/coding-standards-laravel/issues/131)) ([0e68c0f](https://github.com/sinemacula/coding-standards-laravel/commit/0e68c0fee6c37d464496945c133c702733aed03f))
* permit a model's table and connection to come from config ([#128](https://github.com/sinemacula/coding-standards-laravel/issues/128)) ([88b899d](https://github.com/sinemacula/coding-standards-laravel/commit/88b899d52b13845b4c87e36f73358eed27007a4e))
* read a declaration's docblock tags past its attributes ([#136](https://github.com/sinemacula/coding-standards-laravel/issues/136)) ([a6fb079](https://github.com/sinemacula/coding-standards-laravel/commit/a6fb0799b4263b09b2570f00a78cf4163292206f))
* read model cast types from the casts() body ([#125](https://github.com/sinemacula/coding-standards-laravel/issues/125)) ([2d4bd8f](https://github.com/sinemacula/coding-standards-laravel/commit/2d4bd8f13f5472a0cbea722d7c11b5d6fc6ec363))
* recognise a form request by its ancestry and accept inherited rules ([#133](https://github.com/sinemacula/coding-standards-laravel/issues/133)) ([bf9476a](https://github.com/sinemacula/coding-standards-laravel/commit/bf9476a72325996311b197f89aac9e54de2e7b1a))
* stop reporting Blade templates as files with no code ([#127](https://github.com/sinemacula/coding-standards-laravel/issues/127)) ([9519b7d](https://github.com/sinemacula/coding-standards-laravel/commit/9519b7df083d07b25c68e674bc8fea91e7d38e71))
* treat MorphPivot as a model base wherever Pivot is ([#134](https://github.com/sinemacula/coding-standards-laravel/issues/134)) ([0a302d1](https://github.com/sinemacula/coding-standards-laravel/commit/0a302d19f326a487400792ba6c57afd8735997f7))

## [1.6.3](https://github.com/sinemacula/coding-standards-laravel/compare/v1.6.2...v1.6.3) (2026-11-03)


### Bug Fixes

* limit the default model hooks to the ones that describe a record ([#122](https://github.com/sinemacula/coding-standards-laravel/issues/122)) ([ce8a79d](https://github.com/sinemacula/coding-standards-laravel/commit/ce8a79d85b7bb2784a6855c4db161c550a770a20))

## [1.6.2](https://github.com/sinemacula/coding-standards-laravel/compare/v1.6.1...v1.6.2) (2026-09-26)


### Bug Fixes

* resolve a class role from what it declares before what it carries ([#108](https://github.com/sinemacula/coding-standards-laravel/issues/108)) ([9bae0be](https://github.com/sinemacula/coding-standards-laravel/commit/9bae0be7e1e4e515167ba9f4066bd4d94ef097da))

## [1.6.1](https://github.com/sinemacula/coding-standards-laravel/compare/v1.6.0...v1.6.1) (2026-09-22)


### Bug Fixes

* read declaration names consistently across both code sniffer lines ([#103](https://github.com/sinemacula/coding-standards-laravel/issues/103)) ([c9d7ce9](https://github.com/sinemacula/coding-standards-laravel/commit/c9d7ce91991cdaba69b231a35c3dde45e880e80a))

## [1.6.0](https://github.com/sinemacula/coding-standards-laravel/compare/v1.5.0...v1.6.0) (2026-09-15)


### Features

* limit a model to its schema and its relations ([#93](https://github.com/sinemacula/coding-standards-laravel/issues/93)) ([7e0b2a3](https://github.com/sinemacula/coding-standards-laravel/commit/7e0b2a3e85c33504ec7432aac285e9de12939a63))
* require foreign key columns to be declared from their model ([#88](https://github.com/sinemacula/coding-standards-laravel/issues/88)) ([d456f83](https://github.com/sinemacula/coding-standards-laravel/commit/d456f83106942d88d86967c5d95ef0c78126aeeb))


### Bug Fixes

* declare the analysis parameter the shipped config sets ([#98](https://github.com/sinemacula/coding-standards-laravel/issues/98)) ([b869d2b](https://github.com/sinemacula/coding-standards-laravel/commit/b869d2b4e584b9ae8a22323d4cc371dd985525a6))
* exempt classes the framework constructs from the service location rule ([#89](https://github.com/sinemacula/coding-standards-laravel/issues/89)) ([f0d130f](https://github.com/sinemacula/coding-standards-laravel/commit/f0d130f4a576035ebfb3f3583e8ebedce428dde8))
* identify uninjectable bases by the class they name ([#91](https://github.com/sinemacula/coding-standards-laravel/issues/91)) ([85c50d3](https://github.com/sinemacula/coding-standards-laravel/commit/85c50d3346a53eb2f8d40b8eb80c07072dfba7b1))
* ignore the builder calls larastan reflects as static methods ([#84](https://github.com/sinemacula/coding-standards-laravel/issues/84)) ([c5418b3](https://github.com/sinemacula/coding-standards-laravel/commit/c5418b39171935373eb4fbf1ba2c83f63b2f9a4e))
* make the property rules inheritance-aware across the framework, not just models ([#81](https://github.com/sinemacula/coding-standards-laravel/issues/81)) ([cd4b065](https://github.com/sinemacula/coding-standards-laravel/commit/cd4b065d5feb40e2aef1a27afc3ca628ee5e9910))
* pin the model migration scan to the project being analysed ([#85](https://github.com/sinemacula/coding-standards-laravel/issues/85)) ([4fa7361](https://github.com/sinemacula/coding-standards-laravel/commit/4fa736140b4cefba894a0335cdb0ad2b52cf14a5))
* recognise models through ancestry and leave contract methods alone ([#96](https://github.com/sinemacula/coding-standards-laravel/issues/96)) ([64f3641](https://github.com/sinemacula/coding-standards-laravel/commit/64f36417139267e9aff078beabf911c0c666b336))
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
