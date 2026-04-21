# studio-design/studio-auth-php

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
