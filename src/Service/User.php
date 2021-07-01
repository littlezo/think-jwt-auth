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

use littler\JWTAuth\Config\User as Config;
use littler\JWTAuth\Exception\JWTException;
use littler\User\AuthorizeInterface;
use think\App;

class User
{
	protected $config;

	protected $app;

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

	public function getClass(): string
	{
		$token = $this->app->get('jwt.token')->getToken();

		try {
			// dd($this->config->getClass());
			if (empty($this->config->getClass())) {
				throw new JWTException($this->getStore() . '应用 Token 配置未完整', 500);
			}

			return $token->claims()->get('model', $this->config->getClass());
		} catch (\OutOfBoundsException $e) {
			$store = $this->getStore();
			throw new JWTException("{$store}应用未配置用户模型文件");
		}
	}

	public function getModel()
	{
		return $this->config->getClass();
	}

	public function getBind()
	{
		return $this->config->getBind();
	}

	public function find()
	{
		$class = $this->getClass();
		$token = $this->app->get('jwt.token')->getToken();
		$id = $token->claims()->get('jti');

		$model = new $class();
		if ($model instanceof AuthorizeInterface) {
			return $model->getUserById($id);
		}
		throw new JWTException('implements ' . AuthorizeInterface::class);
	}

	protected function init()
	{
		$options = $this->resolveConfig();
		$this->config = new Config($options);
	}

	protected function getStore()
	{
		return $this->app->get('jwt')->getStore();
	}

	protected function resolveConfig(): array
	{
		$store = $this->getStore();

		return $this->app->config->get("jwt.stores.{$store}.user", []);
	}
}
