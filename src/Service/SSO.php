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

use littler\JWTAuth\Config\SSO as Config;
use think\App;

class SSO
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Config
	 */
	protected $config;

	public function __construct(App $app)
	{
		$this->app = $app;

		$this->init();
	}

	/**
	 * @var Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	public function getEnable(): bool
	{
		return $this->config->getEnable();
	}

	protected function init()
	{
		$options = $this->resolveConfig();

		$this->config = new Config($options);
	}

	protected function getStore(): string
	{
		return $this->app->get('jwt')->getStore();
	}

	protected function resolveConfig(): array
	{
		$store = $this->getStore();

		return $this->app->config->get("jwt.stores.{$store}.sso", []);
	}
}
