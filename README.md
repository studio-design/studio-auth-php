# studio-design/auth-sdk-php

Auth Service の PHP SDK です。OpenAPI 仕様から自動生成されています。

## 要件

- PHP 8.2 以上
- PHP 拡張: `curl`, `json`, `mbstring`

## インストール

```bash
composer require studio-design/auth-sdk-php
```

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
*AuthApi* | **postUserinfo** | **POST** /oauth/userinfo | ユーザー情報の取得 (POST)
*AuthApi* | **revokeToken** | **POST** /oauth/revoke | トークン無効化エンドポイント
*SystemApi* | **getHealthStatus** | **GET** /health | ヘルスチェック
*SystemApi* | **getJwks** | **GET** /jwks | JSON Web Key Set の取得
*SystemApi* | **getOpenIDConfiguration** | **GET** /.well-known/openid-configuration | OpenID Connect Discovery


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
- HealthStatus
- InlineObject
- InlineObject1
- IntrospectErrorResponse
- IntrospectResponse
- IntrospectResponseAud
- JsonWebKey
- JwksResponse
- OpenIDProviderMetadataResponse
- Organization
- OrganizationInvitation
- OrganizationMember
- OrganizationMemberUser
- OrganizationRole
- PaginationMeta
- ProblemDetails
- RevokeErrorResponse
- SsoEnforcementErrorResponse
- TokenErrorResponse
- TokenResponse
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

## ライセンス

MIT License - [LICENSE](LICENSE) を参照してください。
