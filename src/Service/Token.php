<?php

declare(strict_types=1);

/*
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
 * ## 只要思想不滑稽，方法总比苦难多！
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */

namespace littler\jwt\Service;

use think\App;
use DateTimeZone;
use DateTimeImmutable;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token as JwtToken;
use littler\jwt\Handle\RequestToken;
use littler\jwt\Config\Token as Config;
use littler\jwt\Exception\JWTException;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;

class Token
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $claims;

    /**
     * @var JwtToken
     */
    protected $token;

    /**
     * @var Configuration
     */
    private $jwtConfiguration;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->init();
    }

    public function initJwtConfiguration()
    {
        $this->jwtConfiguration = Configuration::forSymmetricSigner(
            $this->config->getSigner(),
            $this->config->getSignerKey()
        );
    }

    public function getToken()
    {
        return $this->token;
    }

    public function make($identifier, array $claims = []): JwtToken
    {
        $now = new DateTimeImmutable();
        $builder = $this->jwtConfiguration->builder()
            ->permittedFor($this->config->getAud())
            ->issuedBy($this->config->getIss())
            ->identifiedBy((string) $identifier)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($this->getExpiryDateTime($now))
            ->relatedTo((string) $identifier)
            ->withClaim('store', $this->getStore());

        foreach ($claims as $key => $value) {
            $builder->withClaim($key, $value);
        }
        return $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    public function getExpiryDateTime($now): DateTimeImmutable
    {
        $ttl = (string) $this->config->getExpires();

        return $now->modify("{$ttl} sec");
    }

    public function parse(string $token): JwtToken
    {
        $this->token = $this->jwtConfiguration->parser()->parse($token);

        return $this->token;
    }

    /**
     * 效验合法性 Token.
     *
     * @return bool
     */
    public function validate(string $token)
    {
        $token = $this->parse($token);

        $jwtConfiguration = $this->getValidateConfig();

        $jwtConfiguration->setValidationConstraints(
            new SignedWith($jwtConfiguration->signer(), $jwtConfiguration->signingKey())
        );

        $constraints = $jwtConfiguration->validationConstraints();

        return $jwtConfiguration->validator()->validate($token, ...$constraints);
    }

    /**
     * 效验是否过期 Token.
     *
     * @return bool
     */
    public function validateExp(string $token)
    {
        $token = $this->parse($token);

        $jwtConfiguration = $this->getValidateConfig();

        $jwtConfiguration->setValidationConstraints(
            new LooseValidAt(new SystemClock(new DateTimeZone(\date_default_timezone_get()))),
        );

        $constraints = $jwtConfiguration->validationConstraints();

        return $jwtConfiguration->validator()->validate($token, ...$constraints);
    }

    public function login(JwtToken $token)
    {
        $this->app->get('jwt.manager')->login($token);
    }

    public function logout(?string $token): void
    {
        $token = $token ?: $this->getRequestToken();
        $token = $this->parse($token);

        $this->app->get('jwt.manager')->logout($token);
    }

    /**
     * 自动获取请求下的Token.
     */
    public function getRequestToken(): string
    {
        $requestToken = new RequestToken($this->app);

        return $requestToken->get($this->config->getType());
    }

    public function isRefreshExpired(): bool
    {
        if (!$this->token->claims()->has('iat')) {
            return false;
        }
        $now = new DateTimeImmutable();
        $iat = $this->token->claims()->get('iat');
        $refresh_ttl = $this->config->getRefreshTTL();
        $refresh_exp = $iat->modify("{$refresh_ttl} sec");
        return $now->setTimezone(date_timezone_get($iat)) <= $refresh_exp;
    }

    /**
     * @var Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Token 自动续期
     *
     * @param string $token
     * @param int|string $ttl 秒数
     */
    public function automaticRenewalToken(string $token)
    {
        $token = $this->parse($token);

        $claims = $token->claims()->all();
        $jti = $claims['jti'];
        unset($claims['aud'], $claims['iss'], $claims['jti'], $claims['iat'], $claims['nbf'], $claims['exp'], $claims['sub']);

        $token = $this->make($jti, $claims);
        $refreshAt = $this->config->getRefreshTTL();

        header('Access-Control-Expose-Headers:Renewal-Token,Renewal-Token-RefreshAt');
        header('Renewal-Token:' . $token->toString());
        header("Renewal-Token-RefreshAt:{$refreshAt}");

        return $token;
    }

    public function getClaims()
    {
        return $this->token->claims()->all();
    }

    public function getClaim($name)
    {
        return $this->token->claims()->get($name);
    }

    protected function init()
    {
        $this->resolveConfig();
        $this->initJwtConfiguration();
    }

    protected function getStore()
    {
        return $this->app->get('jwt')->getStore();
    }

    protected function resolveConfig()
    {
        $store = $this->getStore();
        $options = $this->app->config->get("jwt.stores.{$store}.token", []);

        if (!empty($options)) {
            $this->config = new Config($options);
        } else {
            throw new JWTException($store . '应用 Token 配置未完整', 500);
        }
    }

    protected function getValidateConfig()
    {
        return Configuration::forSymmetricSigner(
            $this->config->getSigner(),
            $this->config->RSASigner() ? $this->config->getPublicKey() : $this->config->getHamcKey()
        );
    }
}
