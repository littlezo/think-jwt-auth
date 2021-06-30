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

namespace littler\JWTAuth\Service;

use Lcobucci\JWT\Token;
use littler\JWTAuth\Exception\TokenAlreadyEexpired;
use think\App;

class JwtAuth
{
	/**
	 * 应用名称.
	 *
	 * @var App
	 */
	protected $app;

	/**
	 * 应用名称.
	 *
	 * @var string
	 */
	protected $store;

	/**
	 * @var array|object|string
	 */
	protected $user;

	public function __construct(App $app)
	{
		$this->app = $app;

		$this->init();
	}

	public function store(string $store = null): self
	{
		if ($store) {
			$this->store = $store;
		}

		return $this;
	}

	public function getStore()
	{
		return $this->store ?? $this->getDefaultApp();
	}

	/**
	 * 生成 Token.
	 *
	 * @param mixed $identifier
	 */
	public function token($identifier, array $claims = []): Token
	{
		$token = $this->app->get('jwt.token')->make($identifier, $claims);
		// dd($this->app->get('jwt.manager')->login($token));
		$this->app->get('jwt.manager')->login($token);

		return $token;
	}

	/**
	 * 验证 Token.
	 *
	 * @param string $token
	 */
	public function verify(string $token = null): bool
	{
		$service = $this->app->get('jwt.token');
		if (! $token) {
			$token = $service->getRequestToken();
		}

		// 是否存在黑名单
		$this->wasBan($token);

		// 检测合法性
		if ($service->validate($token)) {
			return $service->validateExp($token);
		}

		return false;
	}

	public function logout(string $token = null)
	{
		$service = $this->app->get('jwt.token');
		if (! $token) {
			$token = $service->getRequestToken();
		}

		$token = $service->parse($token);
		$this->app->get('jwt.manager')->logout($token);
	}

	public function user()
	{
		return $this->app->get('jwt.user')->find();
	}

	protected function init()
	{
	}

	protected function getDefaultApp(): string
	{
		return $this->app->http->getName() ?: 'default';
	}

	protected function wasBan($token)
	{
		$token = $this->app->get('jwt.token')->parse($token);
		if ($this->app->get('jwt.manager')->wasBan($token) === true) {
			$config = $this->app->get('jwt.token')->getConfig();

			throw new TokenAlreadyEexpired('token was ban', $config->getReloginCode());
		}
	}
}
