# studio-design/studio-auth-php

## [0.5.0](https://github.com/studio-design/studio-auth/compare/sdk-v0.4.0...sdk-v0.5.0) (2026-08-27)


### ⚠ BREAKING CHANGES

* **sdk:** the pending-authentication lookup operation and the PENDING_AUTHENTICATION_TOKEN grant type are gone from the SDK.

### Features

* add the pending-authentication-token grant to /oauth/token ([#1645](https://github.com/studio-design/studio-auth/issues/1645)) ([9cb6583](https://github.com/studio-design/studio-auth/commit/9cb6583b69011d4f3c0abd3a9e575bb9d7f796e4))
* look up organization candidates for a pending authentication token ([#1646](https://github.com/studio-design/studio-auth/issues/1646)) ([7e062e7](https://github.com/studio-design/studio-auth/commit/7e062e794c07184bd5228f2e1114c358f58035e5))
* render the organization picker on the authorization server ([#1647](https://github.com/studio-design/studio-auth/issues/1647)) ([cb7be38](https://github.com/studio-design/studio-auth/commit/cb7be3841190190f9ba47fe85c9448e7ea03eba7))
* **sdk:** add the pending-authentication-token grant to the token spec ([2865361](https://github.com/studio-design/studio-auth/commit/2865361dde9ac5fd6744851e94e714ac41aa0a7d))
* **sdk:** document organization_selection_required on the authorize redirect ([5f97773](https://github.com/studio-design/studio-auth/commit/5f97773573e9b8f985903bfc1ddbe51f12262398))
* **sdk:** document the 429 on the WorkOS webhook path ([fa3760b](https://github.com/studio-design/studio-auth/commit/fa3760b5566088814646a61a52c3bfd3b8608117)), closes [#1646](https://github.com/studio-design/studio-auth/issues/1646)
* **sdk:** document the pending-authentication lookup endpoint ([980ab23](https://github.com/studio-design/studio-auth/commit/980ab2314343aae390619e72854ce5e2d5273364)), closes [#1646](https://github.com/studio-design/studio-auth/issues/1646)
* **sdk:** document the rate limit on the pending-authentication lookup ([8161b8b](https://github.com/studio-design/studio-auth/commit/8161b8b570feb7f0cc1be6297f59e789351544cd)), closes [#1646](https://github.com/studio-design/studio-auth/issues/1646)
* **sdk:** remove the pending-authentication lookup endpoint and grant ([84e557e](https://github.com/studio-design/studio-auth/commit/84e557ee1703d2f66bb241bf27de337ee0758744))
* **sdk:** return the standard interaction_required instead of a custom code ([e9e891d](https://github.com/studio-design/studio-auth/commit/e9e891da8c75163547aaefcad66e051d5a817335))


### Bug Fixes

* **sdk:** correct the Referrer-Policy contract and state the BFF obligations ([142b571](https://github.com/studio-design/studio-auth/commit/142b571df4f59a9e9668e8c870ae9ec436b82e30))

## [0.4.0](https://github.com/studio-design/studio-auth/compare/sdk-v0.3.3...sdk-v0.4.0) (2026-08-07)


### ⚠ BREAKING CHANGES

* **sdk:** reading a response gets strictly easier, but constructing one does not. In the TS SDK type / title / status / detail / instance stop being optional and trace_id / correlation_id / service_tag / occurred_at / log_reference appear as required members, so consumer fixtures, mocks and object literals typed as ProblemDetails no longer compile until every member is supplied; the same holds for OrganizationMemberUser's name / given_name / family_name, which go from `string | null | undefined` to `string | null`. In the PHP SDK the accessors for the newly required non-nullable members narrow from `?string` / `?int` to `string` / `int`, so passing null to a setter now raises a TypeError. The SDK is 0.x, so bump-minor-pre-major lands this as a minor bump.

### Bug Fixes

* **sdk:** correct the token example names and pin invalid_params in the 400 tests ([af6ed9d](https://github.com/studio-design/studio-auth/commit/af6ed9d3e984ee446ab366084e914e322dbf5e9d))
* **sdk:** declare the ProblemDetails members this API always returns ([a5bb57c](https://github.com/studio-design/studio-auth/commit/a5bb57c3f2961539b7321f865b5a822b55976be1))
* **sdk:** document only the 400 shape revoke and introspect can actually return ([e3fd7d5](https://github.com/studio-design/studio-auth/commit/e3fd7d53bc6d59c5a687476247210b3b5b0394ed))
* **sdk:** document the 400 responses six admin operations already return ([705caed](https://github.com/studio-design/studio-auth/commit/705caed4fdec6e50689c7a0df53a97dc8861b3b1)), closes [#1501](https://github.com/studio-design/studio-auth/issues/1501)
* **sdk:** document the problem+json shape the OAuth 400 responses also return ([d0f15bf](https://github.com/studio-design/studio-auth/commit/d0f15bf42612fae430fc3ae4bb34ac2734b9083b)), closes [#1502](https://github.com/studio-design/studio-auth/issues/1502)
* **sdk:** let the PHP SDK accept an explicitly-null required member ([8d253df](https://github.com/studio-design/studio-auth/commit/8d253df1f3987b58704cbca16ba2d7e202ca0b41)), closes [#1521](https://github.com/studio-design/studio-auth/issues/1521)
* **sdk:** 実装が返しているのに spec 未記載だった 400 レスポンスを記載する ([8a3cc3a](https://github.com/studio-design/studio-auth/commit/8a3cc3afe85a85b9e22af582e5101ed5f871bab7))

## [0.3.3](https://github.com/studio-design/studio-auth/compare/sdk-v0.3.2...sdk-v0.3.3) (2026-07-31)


### Features

* register post_logout_redirect_uris via the admin client API ([5d0c6b8](https://github.com/studio-design/studio-auth/commit/5d0c6b8dc6878754a2e7f9f627867841f34867d5))
* **sdk:** add post_logout_redirect_uris to admin client schemas ([0ce4ac7](https://github.com/studio-design/studio-auth/commit/0ce4ac7866d9da708ab4b11357a97518b5dedba2)), closes [#1464](https://github.com/studio-design/studio-auth/issues/1464)


### Bug Fixes

* **sdk:** pass primitive-only oneOf raw values through deserialization ([75a65ca](https://github.com/studio-design/studio-auth/commit/75a65ca54679dd9a78a4c61152699096a3aadbe2)), closes [#1520](https://github.com/studio-design/studio-auth/issues/1520)
* **sdk:** pass primitive-only oneOf raw values through SDK deserialization ([e62d29e](https://github.com/studio-design/studio-auth/commit/e62d29e818b9d6adce7683de4f924340567b256e))

## [0.3.2](https://github.com/studio-design/studio-auth/compare/sdk-v0.3.1...sdk-v0.3.2) (2026-07-22)


### Features

* **ci:** publish the auth API reference to studio-internal on stable SDK releases ([ca3e45c](https://github.com/studio-design/studio-auth/commit/ca3e45c720468a4f14036bebaaaeca35dff76fa3))
* **openapi:** add build:docs script for the static API reference bundle ([285c78d](https://github.com/studio-design/studio-auth/commit/285c78d8459d23d72179f01a9da9e52782d78d2d))


### Bug Fixes

* **sdk:** accept stdClass body and exact-match the problem+json media type ([862da81](https://github.com/studio-design/studio-auth/commit/862da81a90d6d5e93489d9c137f9845c8d453cb4))
* **sdk:** classify authorize/callback error formats per status ([5d561e5](https://github.com/studio-design/studio-auth/commit/5d561e55742ccfbf6b82469265458b581c75df95))
* **sdk:** correct auth examples and expand the SDK READMEs ([657f947](https://github.com/studio-design/studio-auth/commit/657f947c21c54298e1f69d69e6ec1796779fbcfc))
* **sdk:** correct auth examples and expand the SDK READMEs ([a1e9997](https://github.com/studio-design/studio-auth/commit/a1e9997b36f08d72d4354541317a894f51349fad))
* **sdk:** correct Node runtime guidance and error-shape docs ([4ebbc0f](https://github.com/studio-design/studio-auth/commit/4ebbc0fe25f3d5145ea2b5843876d018a6d676bd))
* **sdk:** gate getProblem() on the problem+json content type ([#1459](https://github.com/studio-design/studio-auth/issues/1459)) ([039e7d0](https://github.com/studio-design/studio-auth/commit/039e7d042267d7242556b9d1f27d84369518d0f0))
* **sdk:** gate getProblem() on the problem+json content type ([#1459](https://github.com/studio-design/studio-auth/issues/1459)) ([f2e467e](https://github.com/studio-design/studio-auth/commit/f2e467ee2fa980bbd6f6ffb151122ea29d4085bb))
* **sdk:** handle undefined response and deserialize-failure in error docs ([64a119b](https://github.com/studio-design/studio-auth/commit/64a119b55ed42c427dded20a583d5a922adc83d9))
* **sdk:** limit async ApiException conversion to transport exceptions ([8fcf53a](https://github.com/studio-design/studio-auth/commit/8fcf53a8a927186bc596a962c372ae4dc02ec558))
* **sdk:** make error-handling docs match actual SDK behavior ([a3e7bbc](https://github.com/studio-design/studio-auth/commit/a3e7bbce26ac5aef2770fc103807b3ae14646ff9))
* **sdk:** refer to the internal portal without exposing its hostname ([b7cd0d6](https://github.com/studio-design/studio-auth/commit/b7cd0d654a9ec31320049ca6a6eebd3ac1bfe576))
* **sdk:** reject async ConnectException as the base ApiException ([20192db](https://github.com/studio-design/studio-auth/commit/20192dba0d4b14dd196ce390137413618098e792))
* **sdk:** reject async ConnectException as the base ApiException ([3c25b51](https://github.com/studio-design/studio-auth/commit/3c25b51c94019b851391a04618b4030c5dbbe0dc))
* **sdk:** require a JSON object body before resolving Problem Details ([3bc478c](https://github.com/studio-design/studio-auth/commit/3bc478c7270407c2281dd136c19935388c58f8b2))
* **sdk:** scope OAuth error format to 400/401 in Node docs ([a4c596a](https://github.com/studio-design/studio-auth/commit/a4c596a1280a27fffaf574314a2c53b7d410103d))

## [0.3.1](https://github.com/studio-design/studio-auth/compare/sdk-v0.3.0...sdk-v0.3.1) (2026-06-15)


### Features

* **sdk:** document sso_enforced claim in IntrospectResponse ([4db12d8](https://github.com/studio-design/studio-auth/commit/4db12d8c0e48f43e10324a7f6c7ba606945a4ab1)), closes [#1312](https://github.com/studio-design/studio-auth/issues/1312)
* **sdk:** document the 403 user_disabled callback response ([50920f0](https://github.com/studio-design/studio-auth/commit/50920f043022d90e6b0a032257aa5aa6131b6fe8))
* **sdk:** expose 422 self-role-change-must-be-downgrade on member role PATCH ([d4ee967](https://github.com/studio-design/studio-auth/commit/d4ee9670789613b9f26ab57a61017e9b9effb299))
* **sdk:** expose given_name/family_name on OrganizationMemberUser ([8b445e2](https://github.com/studio-design/studio-auth/commit/8b445e2ec9654bb72aaae4f9d1b08e33823d67c0))
* user offboarding & SSO enforcement (epic [#1309](https://github.com/studio-design/studio-auth/issues/1309)) ([c8dd30a](https://github.com/studio-design/studio-auth/commit/c8dd30a85848c56966bd7a121146007f6331bd1d))

## [0.3.0](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.26...sdk-v0.3.0) (2026-05-20)


### ⚠ BREAKING CHANGES

* **sdk:** PATCH /organizations/{organization_id}/members/{member_id} no longer returns 400 when the caller targets their own membership. Successful self-demotions now return 200, and attempts that would remove the last remaining owner return 409 (LastOwnerRoleCannotBeChanged) instead of 400. Clients that branched on the previous 400 contract must be updated to handle 200/409 accordingly.

### Features

* **sdk:** allow self-demotion on organization member role update ([ffbe6ab](https://github.com/studio-design/studio-auth/commit/ffbe6ab5dd5fefc7dd84d2c1625314ed928b5409))

## [0.2.26](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.25...sdk-v0.2.26) (2026-05-16)


### Features

* **sdk:** auto-bind sole active organization on /oauth/authorize ([#1223](https://github.com/studio-design/studio-auth/issues/1223)) ([80be54a](https://github.com/studio-design/studio-auth/commit/80be54a67684a9067857d8619070f3aedaf3c1dd))

## [0.2.25](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.24...sdk-v0.2.25) (2026-05-07)


### Features

* **sdk:** add domain/sso status fields to getMyOrganization ([a8e890d](https://github.com/studio-design/studio-auth/commit/a8e890db76dc29467af5c040426dfa571dcb16dc))
* **sdk:** extract inline enums to named component schemas ([8f261c2](https://github.com/studio-design/studio-auth/commit/8f261c2abfa02dac8ae14926aed39619047e8abb))
* **sdk:** extract Prompt enum to a named component schema ([5091bb6](https://github.com/studio-design/studio-auth/commit/5091bb6e3b74d8a3ef29c49e81b55f076d38a450))
* **sdk:** refine enum schemas after review, add drift tests ([e710be2](https://github.com/studio-design/studio-auth/commit/e710be28962966ff0d6cf00229f1f12e4f9ff1e0))


### Bug Fixes

* **sdk:** remove duplicate description in MyOrganization schema ([b77e8ef](https://github.com/studio-design/studio-auth/commit/b77e8efa0aa6b60d4f3b2b7be50693f2cd85c07b))

## [0.2.24](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.23...sdk-v0.2.24) (2026-04-22)


### Features

* **sdk:** document user-scope 404 type URI for admin-portal-sessions ([4ec1a31](https://github.com/studio-design/studio-auth/commit/4ec1a31f67bc52c2f858ee354fcdaefe6a17d67c))
* **sdk:** document user-scope 404 type URI on invitations endpoint ([4f5b5b0](https://github.com/studio-design/studio-auth/commit/4f5b5b0e6bb9c6868b3f4934f3f1ed3f830cb3c2))


### Bug Fixes

* **openapi:** keep client authentication required for the token endpoint ([ae15201](https://github.com/studio-design/studio-auth/commit/ae152013e6a57e526053068c6dabae5d10959c56))
* **sdk:** restore JWK inheritance in generated PHP SDK ([bbebd87](https://github.com/studio-design/studio-auth/commit/bbebd87941aacc6c43542293d2ff5c0f260b6508))

## [0.2.23](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.22...sdk-v0.2.23) (2026-04-21)


### Features

* **sdk:** add user-scope POST /organizations/{organization_id}/admin-portal-sessions ([130b13c](https://github.com/studio-design/studio-auth/commit/130b13c1d0dfd203dd085c3adc2d6032ae366adf))
* **sdk:** document 404 response on user-scope admin-portal-sessions and tighten auth description ([6f26a9d](https://github.com/studio-design/studio-auth/commit/6f26a9dd78f0c3546609ad1d1820a917ef78e38a))

## [0.2.22](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.21...sdk-v0.2.22) (2026-04-21)


### Features

* **sdk:** add user-scope PATCH /organizations/{organization_id} spec ([4075aa5](https://github.com/studio-design/studio-auth/commit/4075aa501970cf36e406515f733f26555c3a619a))


### Bug Fixes

* **sdk:** resolve oneOf discriminator correctly in PHP SDK templates ([574e808](https://github.com/studio-design/studio-auth/commit/574e8082d7a56cb808b9b4b525064906abb3e5da))
* **sdk:** throw on unknown discriminator value instead of silently falling back ([10a8440](https://github.com/studio-design/studio-auth/commit/10a8440ffd7f4e0ac028be393675b016b4ddddba))

## [0.2.21](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.20...sdk-v0.2.21) (2026-04-20)


### Features

* **sdk:** document error handling for getMyOrganization in TS SDK ([ffc71da](https://github.com/studio-design/studio-auth/commit/ffc71da1754dfdfff73cd1dae4cfb28419754845))
* **sdk:** document error handling for getMyOrganization in TS SDK ([a1b94fc](https://github.com/studio-design/studio-auth/commit/a1b94fce772698528f208aabd6fdde2f275d3a7f))

## [0.2.20](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.19...sdk-v0.2.20) (2026-04-20)


### Bug Fixes

* **sdk:** remove hardcoded production URL from TS SDK README ([ee97c45](https://github.com/studio-design/studio-auth/commit/ee97c45362a26d88cb07e0057dffde53ea8cc5af))
* **sdk:** TS SDK READMEから本番URLを除去 & CHANGELOG同梱 ([3f26a7d](https://github.com/studio-design/studio-auth/commit/3f26a7d2bb5c862d2259511a214f33bfc19612b9))

## [0.2.19](https://github.com/studio-design/studio-auth/compare/sdk-v0.2.18...sdk-v0.2.19) (2026-04-20)


### Features

* **sdk-ts, sdk-php:** expose user-scope DELETE organization member endpoint ([e47ccf3](https://github.com/studio-design/studio-auth/commit/e47ccf331dae6153a95ec4eaeb31763e3b35c9d4))
* **sdk:** expose user-scope DELETE organization member endpoint ([ca003ea](https://github.com/studio-design/studio-auth/commit/ca003ea695db595f78ac8c9e3d691d9427ccd1bd))

## [0.2.18](https://github.com/studio-design/studio-auth/compare/sdk-ts-v0.2.17...sdk-ts-v0.2.18) (2026-04-20)


### Features

* **ci:** switch SDK publishing to tag-triggered OIDC workflow ([#1088](https://github.com/studio-design/studio-auth/issues/1088)) ([f5404d6](https://github.com/studio-design/studio-auth/commit/f5404d6dd8940462910a5493051063dc7d221ab3)), closes [#1077](https://github.com/studio-design/studio-auth/issues/1077)
* **sdk-ts, sdk-php:** expose user-scope DELETE organization member endpoint ([e47ccf3](https://github.com/studio-design/studio-auth/commit/e47ccf331dae6153a95ec4eaeb31763e3b35c9d4))
* **sdk:** replace Changesets with checksum-based auto-versioning ([6ab4bde](https://github.com/studio-design/studio-auth/commit/6ab4bde07009dab392da557f87f9b77583249e63))


### Bug Fixes

* **ci:** ヘルスチェックに IAM 認証トークンを付与 ([2395215](https://github.com/studio-design/studio-auth/commit/239521502f6dfc59496b3d0c26a90a9eb9043753))

## 0.2.5

### Patch Changes

- 2f4c850: chore(sdk-php): Packagist 公開に向けた SDK テンプレート・CI ワークフロー整備

## 0.2.4

### Patch Changes

- a44aeee: fix(sdk-php): PHP 8.4 互換性修正 — HeaderSelector の型エラーを解消

  `HeaderSelector::getNextWeight()` で `10 ** floor(...)` が `float` を返すため、
  PHP 8.4 の厳密な型チェックで `int` 戻り値型と不一致になる問題を修正。
  `adjustWeight()` のパラメータ型も `float` → `int` に統一。

## 0.2.3

### Patch Changes

- b8c0cbe: Fix PHP SDK version: remove hardcoded artifactVersion from config.json so CLI override works correctly

## 0.2.2

### Patch Changes

- d29fe0a: Initial PHP SDK release

## 0.2.1

### Patch Changes

- fddff51: Artifact Registry 移行に伴うリリースフロー確認

## 0.2.0

### Minor Changes

- d7c5ccb: Remove Workspace endpoints and models from the API

  The following Admin API endpoints have been removed:

  - `POST /admin/workspaces` (createWorkspace)
  - `GET /admin/workspaces` (listWorkspaces)
  - `GET /admin/workspaces/{workspace_id}` (getWorkspace)
  - `PATCH /admin/workspaces/{workspace_id}` (updateWorkspace)
  - `DELETE /admin/workspaces/{workspace_id}` (deactivateWorkspace)

  The following models have been removed:

  - `Workspace`
  - `AdminWorkspaceCreateRequest`
  - `AdminWorkspaceCreatedResponse`
  - `AdminWorkspaceListResponse`
  - `AdminWorkspaceUpdateRequest`

  Workspace management will be handled by client-side APIs instead of the shared auth server.

## 0.1.0

### Minor Changes

- 3e0166b: Initial release of TypeScript SDK for Auth Service API

  - Auto-generated TypeScript types and SDK functions from OpenAPI spec
  - Fetch-based HTTP client with authentication support
  - All API operations available as tree-shakeable functions
