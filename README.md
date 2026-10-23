# studio-design/studio-auth-php

Auth Service の PHP SDK です。OpenAPI 仕様から自動生成されています。

## ドキュメント

全エンドポイント・スキーマ・エラーレスポンスを網羅した API リファレンスは、社内開発者ポータル（Google IAP 保護・社内メンバー限定）で公開されています。ポータルのトップページから「Auth Service API」を開いてください。

## 要件

- PHP 8.2 以上
- PHP 拡張: `curl`, `json`, `mbstring`, `openssl`

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

OAuth 2.0 のトークンエンドポイントやイントロスペクションは HTTP Basic 認証（`client_id` / `client_secret`）でも認証できます。
**内部サービスには次節の `private_key_jwt` を推奨します**（共有 secret を配らずに済み、鍵は relying party の Cloud KMS から出ません）。

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

### AuthApi（private_key_jwt）— Cloud KMS で署名した client assertion

`token_endpoint_auth_method = private_key_jwt` で登録したクライアントは、Basic 認証の代わりに
RFC 7523 の client assertion を本文で送ります。SDK は assertion の組み立て・署名に使う鍵バージョンの選択・
公開鍵（JWKS）の提供を `Studio\Auth\ClientAssertion` で行います。Cloud KMS を使う場合は
`composer require google/cloud-kms` も必要です。

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\GCECredentials;
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Studio\Auth\ClientAssertion\ClientAssertionBuilder;
use Studio\Auth\ClientAssertion\Kms\KmsClientAdapter;
use Studio\Auth\ClientAssertion\Kms\KmsClientAssertionSigner;
use Studio\Auth\ClientAssertion\Kms\KmsKeyVersionInventory;

// 実行 SA（metadata server）の資格情報を明示する。ADC が鍵ファイルに解決される環境で、
// 鍵ファイルの SA に署名権限を付けないため。
$kms = new KmsClientAdapter(new KeyManagementServiceClient(['credentials' => new GCECredentials()]));

// $cache は PSR-16 CacheInterface（Laravel なら Cache::store()）。鍵バージョン一覧を 5 分キャッシュし、
// KMS 障害時は最後に取れた値を使い続ける。TTL を付けずに保存した項目を消さないストアを渡すこと（下記）。
$inventory = new KmsKeyVersionInventory($kms, 'projects/PROJECT/locations/asia-northeast1/keyRings/RING/cryptoKeys/KEY', $cache);

$builder = new ClientAssertionBuilder(
    clientId: 'YOUR_CLIENT_ID',
    issuer: 'https://auth.studio.design',   // discovery の issuer。aud に入る
    signer: new KmsClientAssertionSigner($kms, $inventory),
);

$config  = Studio\Auth\Configuration::getDefaultConfiguration()->setHost('https://auth.studio.design');
$authApi = new Studio\Auth\Api\AuthApi(new GuzzleHttp\Client(), $config);

$result = $authApi->introspectToken(
    'YOUR_TOKEN',
    null,
    'YOUR_CLIENT_ID',
    ClientAssertionBuilder::ASSERTION_TYPE,
    $builder->build(),   // 呼び出しごとに組み立てる（jti は使い捨て、有効期間 70 秒）
);
```

JWKS エンドポイント（認可サーバーに登録する `jwks_uri` の実体）は `$inventory->jwks()` を JSON で返すだけです。
署名に使う鍵バージョンは「公開対象（HSM・EC P-256・非インポート・ENABLED）のうち作成から 24 時間以上たった最新、
無ければ最も古い ENABLED」で、JWKS に載せる集合と同じ規則で選ばれます。
署名開始までの 24 時間（86400 秒）は固定で、設定や環境変数では短縮できません。stg の予行演習も実時間で行います。
ローテーション Job は新しい署名バージョンの作成から 25 時間以降に古い ENABLED を直接破棄予約します。
SDK は KMS 一覧の取得成功時に DESTROY_SCHEDULED を除外します。既定 300 秒のキャッシュ内、または KMS 障害時は最後の取得値を使います。

`KmsKeyVersionInventory` は取得値を TTL 無しで `$cache->set()` し、有効期限（既定 300 秒）は自分で判定します。
PSR-16 は TTL 省略時にストアが既定の有効期限を付けることを認めているため、そうしたストアでは最後の取得値が消え、
KMS 障害時に使い続けられません。TTL 省略時に無期限で保存するストアを渡してください
（Laravel の `Cache::store()` は TTL 省略時に `forever()` で保存します）。

#### KMS 呼び出しの期限（`KmsCallBudget`）

`KmsCallBudget::run()` の中の KMS 関連の呼び出し（metadata server からのトークン取得・鍵バージョン一覧の各ページ・
各公開鍵・署名）は、1 つの期限を共有します。呼び出しごとに残り時間を取り直すので、metadata と KMS をそれぞれ満額待たせません。
GAX のリトライは無効にするため、期限はリトライも含めた KMS の作業全体の上限です。値は呼び出し側が決めます（SDK に既定値はありません）。

- `run()` が対象にするのは同期呼び出しだけです。promise を返して `run()` の外で待つ呼び出しには期限が掛かりません。入れ子の `run()` は外側の期限をそのまま使います
- 期限切れは `Google\ApiCore\ApiException`（`DEADLINE_EXCEEDED`、固定 message・previous なし）です。
  `KmsKeyVersionInventory` は KMS 障害と同じく最後に取れた値で JWKS を返します。署名は失敗します（期限を過ぎたら署名の RPC を送らない）
- `run()` の外で `remainingMillis()` / `callOptions()` / `httpOptions()` を呼ぶと `LogicException` です

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Google\ApiCore\ApiException;
use Google\Auth\Credentials\GCECredentials;
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Google\Rpc\Code;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Studio\Auth\ClientAssertion\ClientAssertionBuilder;
use Studio\Auth\ClientAssertion\Kms\KmsCallBudget;
use Studio\Auth\ClientAssertion\Kms\KmsClientAdapter;
use Studio\Auth\ClientAssertion\Kms\KmsClientAssertionSigner;
use Studio\Auth\ClientAssertion\Kms\KmsKeyVersionInventory;

$budget = new KmsCallBudget(5000);
$http   = new GuzzleHttp\Client(['http_errors' => false]);

// metadata server（トークン取得）と KMS の REST が共有する送信処理。送信の直前に残り時間を timeout にし、
// 通信の失敗と 2xx 以外（3xx を含む）は、本文・headers・previous を持たない固定の ApiException にする。
$send = static function (RequestInterface $request, array $options = []) use ($http, $budget): ResponseInterface {
    try {
        $response = $http->send($request, $budget->httpOptions($options));
    } catch (TransferException) {
        throw new ApiException('KMS transport unavailable', Code::UNAVAILABLE, 'UNAVAILABLE');
    }

    $budget->remainingMillis();   // 期限を過ぎて届いた応答は使わない（DEADLINE_EXCEEDED）

    if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
        throw new ApiException('KMS transport unavailable', Code::UNAVAILABLE, 'UNAVAILABLE');
    }

    return $response;
};

// 一覧の各ページ・各公開鍵・署名の RPC の直前に、残り時間を timeout にしてリトライを無効にした call options を作る。
$kms = new KmsClientAdapter(new KeyManagementServiceClient([
    'credentials'       => new GCECredentials(),
    'credentialsConfig' => ['authHttpHandler' => $send],
    'transport'         => 'rest',
    'transportConfig'   => ['rest' => [
        'httpHandler' => static fn (RequestInterface $request, array $options = []) => Create::promiseFor($send($request, $options)),
    ]],
]), $budget->callOptions(...));

// inventory・signer・builder はこの $kms から作る（期限の無い adapter を持ったものを使い回さない）。
// $cache は前節と同じ、TTL 省略時に無期限で保存する PSR-16 ストア。
$inventory = new KmsKeyVersionInventory($kms, 'projects/PROJECT/locations/asia-northeast1/keyRings/RING/cryptoKeys/KEY', $cache);
$builder   = new ClientAssertionBuilder(
    clientId: 'YOUR_CLIENT_ID',
    issuer: 'https://auth.studio.design',
    signer: new KmsClientAssertionSigner($kms, $inventory),
);

$assertion = $budget->run(fn () => $builder->build());
$jwks      = $budget->run(fn () => $inventory->jwks());
```

HTTP の判定は transport の handler で行います。GAX は handler が返した応答をステータスを見ずに protobuf としてデコードするため、
上の `$send` のように 2xx 以外を例外にしないと、たとえば 503 や 302 の本文 `{}` が「正常な空の一覧」になり、
`KmsKeyVersionInventory` が最後に取れた鍵を空の一覧で上書きします（`http_errors` を有効にした Guzzle でも、
`allow_redirects: false` の 3xx は fulfilled のまま返ります。4xx / 5xx を GAX に変換させると応答本文が例外 message に入ります）。
`KmsKeyVersionInventory` がキャッシュを書くのは全ページと全公開鍵を取り終えたときだけで、途中で失敗したら値も取得時刻も
変えません（キャッシュがあれば前回の完全な一覧、無ければ例外）。

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
- AdminPortalIntent
- AdminPortalSessionCreateRequest
- AdminPortalSessionCreatedResponse
- CheckSsoEnforcementResponse
- Client
- ClientAssertionSigningAlg
- ClientJwkSet
- ClientPublicJwk
- ClientType
- CodeChallengeMethod
- DomainVerificationStatus
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
- JwkKeyType
- JwkKeyUse
- JwkSigningAlg
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
- OrganizationMemberSortBy
- OrganizationMemberUpdateRequest
- OrganizationMemberUser
- OrganizationRole
- OrganizationUpdateRequest
- PaginationMeta
- ProblemDetails
- Prompt
- ResponseType
- RevokeErrorCode
- RevokeErrorResponse
- RsaJsonWebKey
- ScopeValue
- ScreenHint
- ServiceInfoResponse
- SortOrder
- SsoConnectionStatus
- SsoEnforcementErrorResponse
- SubjectType
- TokenEndpointAuthMethod
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
- **対象**: AuthApi の token / introspect / revoke / sso-enforcement エンドポイント（token / introspect / revoke は `private_key_jwt` でも認証できる。上の節を参照）
- **設定**: `$config->setUsername('CLIENT_ID')->setPassword('CLIENT_SECRET')`

## コントリビュート

このリポジトリは OpenAPI 仕様から自動生成される**読み取り専用のリリースミラー**です。ファイルはリリースごとに上書きされるため、Pull Request は受け付けられません。バグ報告・機能要望は [GitHub Issues](https://github.com/studio-design/studio-auth-php/issues) へお願いします。

## ライセンス

MIT License - [LICENSE](LICENSE) を参照してください。

---

このパッケージは [Studio Auth Service の OpenAPI 仕様](https://github.com/studio-design/studio-auth) から自動生成されています（SDK バージョン: `0.7.6-rc.88`）。
