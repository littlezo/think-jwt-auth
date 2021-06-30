# jwt-auth

## 环境要求

1. php >= ^7.4
2. thinkphp ^6.0.0

## 安装

稳定版

```sh
composer require littler/jwt-auth
```

开发版

```sh
composer require littler/jwt-auth:dev-next
```

## 使用

1. 配置
   `config/jwt.php`

完整配置

```php
<?php

return [
    'stores' => [
        // 单应用时 默认使用此配置
        'default' => [
            'sso' => [
                'enable' => true,
            ],
            'token' => [
                'signer_key'    => 'littler',
                'public_key'    => 'file://path/public.key',
                'private_key'   => 'file://path/private.key',
                'not_before'    => 0,
                'expires_at'    => 3600,
                'refresh_ttL'   => 7200,
                'signer'       => 'HS256',
                'type'         => 'Header',
                'relogin_code'      => 50001,
                'refresh_code'      => 50002,
                'iss'          => 'client.littler',
                'aud'          => 'server.littler',
                'automatic_renewal' => false,
            ],
            'user' => [
                'bind' => true,
                'class'  => null,
            ]
        ],
        // 多应用时 对应应用的配置
        'admin' => [
            'sso' => [
                'enable' => false,
            ],
            'token' => [
                'signer_key'    => 'littler',
                'not_before'    => 0,
                'expires_at'    => 3600,
                'refresh_ttL'   => 7200,
                'signer'       => 'HS256',
                'type'         => 'Header',
                'relogin_code'      => 50001,
                'refresh_code'      => 50002,
                'iss'          => 'client.littler',
                'aud'          => 'server.littler',
                'automatic_renewal' => false,
            ],
            'user' => [
                'bind' => false,
                'class'  => null,
            ]
        ]
    ],
    'manager' => [
        // 缓存前缀
        'prefix' => 'jwt',
        // 黑名单缓存名
        'blacklist' => 'blacklist',
        // 白名单缓存名
        'whitelist' => 'whitelist'
    ]
];

```

## token

- `signer_key` 密钥
- `not_before` 时间前不能使用 默认生成后直接使用
- `refresh_ttL` Token 有效期（秒）
- `signer` 加密算法 目前支持如下三大类型加密方式：RSA,HASH,DSA。再各分 256、384、512 位。

- 默认是 HS256，即 hash 256 位加密。
- 需要修改加密方式，请修改参数：SIGNER，参数选项：

- HS256
  > 备注：hash 256 位
- HS384
  > 备注：hash 384 位
- HS512
  > 备注：hash 512 位
- RS256
  > 备注：rsa 256 位
- RS384
  > 备注：rsa 384 位
- RS512
  > 备注：rsa 512 位
- ES256
  > 备注：dsa 256 位
- ES384
  > 备注：dsa 384 位
- ES512
  > 备注：dsa 512 位

> 重要：RSA 和 DSA 都是非对称加密方式，除了修改参数 SIGNER 外，需要配置：PUBLIC_KEY、PRIVATE_KEY 两个参数，

- `type` 获取 Token 途径
- `relogin_code` Token 过期抛异常 code = 50001
- `refresh_code` Token 失效异常 code = 50002
- `automatic_renewal` [开启过期自动续签](#过期自动续签)

## user

- `bind` 是否注入用户模型(中间件有效)
- `class` 用户模型类文件

## manager

- `prefix` 缓存前缀
- `blacklist` 黑名单缓存名
- `whitelist` 白名单缓存名

以下两个异常都会抛一个 HTTP 异常 StatusCode = 401

- `littler\JWTAuth\Exception\HasLoggedException`
- `littler\JWTAuth\Exception\TokenAlreadyEexpired`

### 缓存支持

- File
- Redis

## Token 生成

```php
namespace app\api\controller\Auth;

use littler\JWTAuth\Facade\Jwt;

public function login()
{
    //...登录判断逻辑

    // 自动获取当前应用下的jwt配置
    return json([
        'token' => Jwt::token($uid, ['params1' => 1, 'params2' => 2])->toString(),
    ]);

    // 自定义用户模型
    return json([
        'token' => Jwt::token($uid, ['model' => CustomMember::class])->toString(),
    ]);
}
```

## Token 验证

自动获取当前应用（多应用下）配置。

### 手动验证

```php
use littler\JWTAuth\Facade\Jwt;
use littler\JWTAuth\Exception\HasLoggedException;
use littler\JWTAuth\Exception\TokenAlreadyEexpired;

class User {

    public function test()
    {
        if (true === Jwt::verify($token)) {
            // 验证成功
        }

        // 验证成功
        // 如配置用户模型文件 可获取当前用户信息
        dump(Jwt::user());
    }
}

```

### 路由验证

```php
use littler\JWTAuth\Middleware\Jwt;

// 自动获取当前应用配置
Route::get('/hello', 'index/index')->middleware(Jwt::class);

// 自定义应用 使用api应用配置
Route::get('/hello', 'index/index')->middleware(Jwt::class, 'api');
```

## Token 自动获取

支持以下方式自动获取

- `Header`
- `Cookie`
- `Param`

赋值方式

|  类型  |     途径      |     标识     |
| :----: | :-----------: | :----------: |
| Header | Authorization | Bearer Token |
| Cookie |    Cookie     |    token     |
| Param  |    Request    |    token     |

```php
# config/jwt.php

<?php

return [

    'stores' => [
        'admin' => [
            'token' => [
                // ...其它配置
                'type' => 'Header',
                // 'type' => 'Cookie',
                // 'type' => 'Param',
                // 支持多种方式获取
                // 'type' => 'Header|Param',
            ]
        ]
    ]

];
```

## 过期自动续签

`app/config/jwt.php`

`automaticRenewal => true`

系统检测到 Token 已过期， 会自动续期并返回以下 header 信息。

- Automatic-Renewal-Token
- Automatic-Renewal-Token-RefreshAt

前端需要接收最新 Token，下次异步请求时，携带此 Token。

## 注销应用 Token(所有)

注销指定应用下缓存的用户 （强制下线 重新登录）

```php

$store = 'api';

app('jwt.manager')->destroyStoreWhitelist($store);
```

## 注销应用 Token(指定某个)

注销指定某个用户（强制下线 重新登录）

```php

$store = 'api';
$uid = '9520';

app('jwt.manager')->destroyToken($id, $store);
```

## 版权信息

更多细节参阅 [MPL V2](LICENSE)
