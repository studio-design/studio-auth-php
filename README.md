# studio-design/studio-auth-php

Auth Service の PHP SDK です。OpenAPI 仕様から自動生成されています。

## ドキュメント

全エンドポイント・スキーマ・エラーレスポンスを網羅した API リファレンスは、社内開発者ポータル（Google IAP 保護・社内メンバー限定）で公開されています。ポータルのトップページから「Auth Service API」を開いてください。

## 要件

- PHP 8.2 以上
- PHP 拡張: `curl`, `json`, `mbstring`

## インストール

### 安定版

```bash
composer require studio-design/studio-auth-php
```

### Snapshot 版（リリース前検証用）

`main` にマージされた未リリースの SDK 変更を staging で検証する用途で、Composer の prerelease 制約で snapshot タグを取得できます。

```bash
composer require "studio-design/studio-auth-php:^X.Y.Z@rc"
```

`X.Y.Z` は次回リリース予定のバージョン（直近 stable パッチ + 1）です。最新の snapshot タグは [studio-auth-php の Tags](https://github.com/studio-design/studio-auth-php/tags) を参照してください。

⚠️ snapshot 版は staging / CI 検証用です。本番デプロイには安定版（`composer require studio-design/studio-auth-php` または `:^X.Y.Z`）を使用してください。

## 使い方

### ホスト設定（必須）

SDK にはデフォルトのホスト URL が設定されていません。利用前に必ず `setHost()` で接続先を指定してください。

```php
$config = Studio\Auth\Configuration::getDefaultConfiguration()
    ->setHost('https://your-auth-server.example.com');
```

### AdminApi（BearerAuth）— 組織一覧取得

Admin 系のエンドポイントは Bearer トークン（JWT）で認証します。

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = Studio\Auth\Configuration::getDefaultConfiguration()
    ->setHost('https://your-auth-server.example.com')
    ->setAccessToken('YOUR_ACCESS_TOKEN');

$adminApi = new Studio\Auth\Api\AdminApi(
    new GuzzleHttp\Client(),
    $config,
);

try {
    $result = $adminApi->listOrganizations();
    print_r($result);
} catch (Studio\Auth\ApiException $e) {
    echo 'Exception: ', $e->getMessage(), PHP_EOL;
}
```

### AuthApi（ClientBasicAuth）— トークンイントロスペクション

OAuth 2.0 のトークンエンドポイントやイントロスペクションは HTTP Basic 認証（`client_id` / `client_secret`）で認証します。

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = Studio\Auth\Configuration::getDefaultConfiguration()
    ->setHost('https://your-auth-server.example.com')
    ->setUsername('YOUR_CLIENT_ID')
    ->setPassword('YOUR_CLIENT_SECRET');

$authApi = new Studio\Auth\Api\AuthApi(
    new GuzzleHttp\Client(),
    $config,
);

try {
    $result = $authApi->introspectToken('YOUR_TOKEN');
    print_r($result);
} catch (Studio\Auth\ApiException $e) {
    echo 'Exception: ', $e->getMessage(), PHP_EOL;
}
```

### アクセストークンの取得

Bearer 認証で使うアクセストークンは、Authorization Code + PKCE フロー（`/oauth/authorize` → `/oauth/token`）で発行されます。有効期限切れ時は `refresh_token` グラントで再発行します。フローの詳細は社内ポータルの API リファレンスを参照してください。`client_id` / `client_secret` は Auth Service 管理者がアプリケーションごとに発行します。

### エラーハンドリング

API エラーは HTTP ステータスに応じた `Studio\Auth\ApiException` のサブクラスとして送出されます（同期・非同期メソッドで共通。未マップのステータスや接続エラーは基底の `ApiException` のまま）。非同期メソッドの場合は、返り値の promise がこれらの例外で reject されます:

| HTTP ステータス | 例外クラス |
| --- | --- |
| 400 | `Studio\Auth\Exception\BadRequestException` |
| 401 | `Studio\Auth\Exception\UnauthorizedException` |
| 403 | `Studio\Auth\Exception\ForbiddenException` |
| 404 | `Studio\Auth\Exception\NotFoundException` |
| 409 | `Studio\Auth\Exception\ConflictException` |
| 422 | `Studio\Auth\Exception\UnprocessableEntityException` |
| 429 | `Studio\Auth\Exception\RateLimitException` |
| 5xx | `Studio\Auth\Exception\ServerException` |

`ApiException::getProblem()` は、`Content-Type: application/problem+json` のエラーボディが正しくデシリアライズできたときだけ RFC 9457 Problem Details（`getType()` / `getTitle()` / `getStatus()` / `getDetail()`）を返します。ボディが空のとき、`application/problem+json` でないとき（OAuth の `{"error": ...}` 形式や userinfo の RFC 6750 エラーを含む）、またはデシリアライズに失敗したときは `null` を返します。**エラー種別の判定には、従来どおり例外のサブクラス（`NotFoundException` 等）または `getCode()`（HTTP ステータスコード）を使ってください**。

```php
use Studio\Auth\ApiException;
use Studio\Auth\Exception\NotFoundException;

try {
    $organization = $adminApi->getOrganization('org_xxxxx');
} catch (NotFoundException $e) {
    // 404: 指定した組織が存在しない
    echo $e->getProblem()?->getTitle(), PHP_EOL;
} catch (ApiException $e) {
    // その他の API エラー（未マップのステータス・接続エラー含む）
    echo $e->getCode(), ': ', $e->getMessage(), PHP_EOL;
}
```

## API エンドポイント一覧

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*APIApi* | **checkSsoEnforcement** | **GET** /api/sso-enforcement/check | SSO 強制判定エンドポイント
*AdminApi* | **createAdminPortalSession** | **POST** /admin/organizations/{organization_id}/admin-portal-sessions | Admin Portal セッション生成
*AdminApi* | **createClient** | **POST** /admin/clients | クライアント登録
*AdminApi* | **createOrganization** | **POST** /admin/organizations | 組織登録
*AdminApi* | **createOrganizationInvitation** | **POST** /admin/organizations/{organization_id}/invitations | メンバー招待
*AdminApi* | **deleteClient** | **DELETE** /admin/clients/{client_id} | クライアント削除
*AdminApi* | **getClient** | **GET** /admin/clients/{client_id} | クライアント詳細取得
*AdminApi* | **getOrganization** | **GET** /admin/organizations/{organization_id} | 組織詳細取得
*AdminApi* | **listClients** | **GET** /admin/clients | クライアント一覧取得
*AdminApi* | **listOrganizationInvitations** | **GET** /admin/organizations/{organization_id}/invitations | 招待一覧取得
*AdminApi* | **listOrganizationMembers** | **GET** /admin/organizations/{organization_id}/members | 組織メンバー一覧取得
*AdminApi* | **listOrganizations** | **GET** /admin/organizations | 組織一覧取得
*AdminApi* | **removeOrganizationMember** | **DELETE** /admin/organizations/{organization_id}/members/{member_id} | メンバー削除
*AdminApi* | **revokeOrganizationInvitation** | **DELETE** /admin/organizations/{organization_id}/invitations/{invitation_id} | 招待取消
*AdminApi* | **updateClient** | **PATCH** /admin/clients/{client_id} | クライアント更新
*AdminApi* | **updateOrganization** | **PATCH** /admin/organizations/{organization_id} | 組織更新
*AdminApi* | **updateOrganizationMemberRole** | **PATCH** /admin/organizations/{organization_id}/members/{member_id} | メンバーロール変更
*AuthApi* | **endSession** | **GET** /oauth/logout | ログアウトエンドポイント
*AuthApi* | **endSessionPost** | **POST** /oauth/logout | ログアウトエンドポイント (POST)
*AuthApi* | **getUserinfo** | **GET** /oauth/userinfo | ユーザー情報の取得
*AuthApi* | **handleIdpCallback** | **GET** /oauth/callback | IdP コールバック処理
*AuthApi* | **initiateAuthorization** | **GET** /oauth/authorize | 認可フローの開始
*AuthApi* | **introspectToken** | **POST** /oauth/introspect | トークンイントロスペクションエンドポイント
*AuthApi* | **issueTokens** | **POST** /oauth/token | トークンエンドポイント
*AuthApi* | **lookupPendingAuthentication** | **POST** /oauth/pending-authentication/lookup | 保留中認証トークンの組織候補ルックアップ
*AuthApi* | **postUserinfo** | **POST** /oauth/userinfo | ユーザー情報の取得 (POST)
*AuthApi* | **revokeToken** | **POST** /oauth/revoke | トークン無効化エンドポイント
*OrganizationApi* | **createMyAdminPortalSession** | **POST** /organizations/{organization_id}/admin-portal-sessions | 自組織のAdmin Portal セッション生成（組織メンバー向け）
*OrganizationApi* | **getMemberMe** | **GET** /organizations/{organization_id}/members/me | 自分自身の組織メンバー情報取得（組織メンバー向け）
*OrganizationApi* | **getMyOrganization** | **GET** /organizations/{organization_id} | 自分が所属する組織情報取得（組織メンバー向け）
*OrganizationApi* | **listMembers** | **GET** /organizations/{organization_id}/members | 組織メンバー一覧取得（組織メンバー向け）
*OrganizationApi* | **removeMember** | **DELETE** /organizations/{organization_id}/members/{member_id} | メンバー削除（組織メンバー向け）
*OrganizationApi* | **sendInvitation** | **POST** /organizations/{organization_id}/invitations | 組織Adminによるメンバー招待
*OrganizationApi* | **updateMemberRole** | **PATCH** /organizations/{organization_id}/members/{member_id} | メンバーロール変更（組織メンバー向け）
*OrganizationApi* | **updateMyOrganization** | **PATCH** /organizations/{organization_id} | 自分が所属する組織情報更新（組織メンバー向け）
*SystemApi* | **getHealthStatus** | **GET** /health | ヘルスチェック
*SystemApi* | **getJwks** | **GET** /jwks | JSON Web Key Set の取得
*SystemApi* | **getOpenIDConfiguration** | **GET** /.well-known/openid-configuration | OpenID Connect Discovery
*SystemApi* | **getServiceInfo** | **GET** / | サービス情報


## モデル一覧

- AdminClientCreateRequest
- AdminClientCreatedResponse
- AdminClientListResponse
- AdminClientUpdateRequest
- AdminOrganizationCreateRequest
- AdminOrganizationCreatedResponse
- AdminOrganizationInvitationCreateRequest
- AdminOrganizationInvitationCreatedResponse
- AdminOrganizationInvitationListResponse
- AdminOrganizationListResponse
- AdminOrganizationMemberListResponse
- AdminOrganizationMemberUpdateRequest
- AdminOrganizationUpdateRequest
- AdminPortalSessionCreateRequest
- AdminPortalSessionCreatedResponse
- CheckSsoEnforcementResponse
- Client
- CodeChallengeMethod
- EcJsonWebKey
- GrantType
- HealthStatus
- IdpAuthorizeErrorCode
- InlineObject
- InlineObject1
- IntrospectErrorCode
- IntrospectErrorResponse
- IntrospectResponse
- IntrospectResponseAud
- InvalidParam
- JsonWebKey
- JwksResponse
- MemberDomainTypeCounts
- MemberRoleCounts
- MyOrganization
- OauthAuthorizeErrorCode
- OidcAuthorizeErrorCode
- OpenIDProviderMetadataResponse
- Organization
- OrganizationInvitation
- OrganizationInvitationCreateRequest
- OrganizationInvitationCreatedResponse
- OrganizationMember
- OrganizationMemberDomainType
- OrganizationMemberListResponse
- OrganizationMemberUpdateRequest
- OrganizationMemberUser
- OrganizationRole
- OrganizationUpdateRequest
- PaginationMeta
- PendingAuthenticationLookupRequest
- PendingAuthenticationLookupResponse
- PendingAuthenticationOrganizationSummary
- ProblemDetails
- Prompt
- ResponseType
- RevokeErrorCode
- RevokeErrorResponse
- RsaJsonWebKey
- ServiceInfoResponse
- SsoEnforcementErrorResponse
- TokenErrorCode
- TokenErrorResponse
- TokenResponse
- TokenType
- TokenTypeHint
- UserinfoResponse


## 認証方式

### BearerAuth

- **種別**: Bearer 認証（JWT）
- **対象**: AdminApi の全エンドポイント、AuthApi の userinfo エンドポイント
- **設定**: `$config->setAccessToken('YOUR_ACCESS_TOKEN')`

### ClientBasicAuth

- **種別**: HTTP Basic 認証
- **対象**: AuthApi の token / introspect / revoke エンドポイント
- **設定**: `$config->setUsername('CLIENT_ID')->setPassword('CLIENT_SECRET')`

## コントリビュート

このリポジトリは OpenAPI 仕様から自動生成される**読み取り専用のリリースミラー**です。ファイルはリリースごとに上書きされるため、Pull Request は受け付けられません。バグ報告・機能要望は [GitHub Issues](https://github.com/studio-design/studio-auth-php/issues) へお願いします。

## ライセンス

MIT License - [LICENSE](LICENSE) を参照してください。

---

このパッケージは [Studio Auth Service の OpenAPI 仕様](https://github.com/studio-design/studio-auth) から自動生成されています（SDK バージョン: `0.4.1-rc.39`）。
