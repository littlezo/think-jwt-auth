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

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Plain;
use littler\jwt\Config\Manager as Config;
use think\App;

class Manager
{
	/**
	 * @var array|object
	 */
	protected $cache;

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

	public function login(Token $token): void
	{
		if ($this->app->get('jwt.sso')->getEnable()) {
			$this->handleSSO($token);
		}
		$this->pushWhitelist($token);
	}

	public function logout(Token $token): void
	{
		$this->pushBlacklist($token);
	}

	public function wasBan(Token $token): bool
	{
		return $this->getBlacklist($token) === $token->toString();
	}

	public function destroyStoreWhitelist($store): void
	{
		$this->clearStoreWhitelist($store);
	}

	public function destroyStoreBlacklist($store): void
	{
		$this->clearStoreBlacklist($store);
	}

	public function destroyToken($id, $store): void
	{
		$type = $this->config->getWhitelist();
		$tag = $store . '-' . $type;
		$keys = $this->app->cache->getTagItems($tag);
		foreach ($keys as $key) {
			$handle = strtolower($this->app->config->get('cache.default'));
			if ($handle == 'file') {
				$token = unserialize($this->decodeFileCache($key)['content']);
			} elseif ($handle == 'redis') {
				$token = $this->app->cache->get($key);
			}

			// dd($token);
			if (! $token) {
				continue;
			}
			$token = $this->app->get('jwt.token')->parse($token);
			if ($token->claims()->has('jti') && $token->claims()->get('jti') == $id) {
				$this->pushBlacklist($token);
			}
		}
	}

	protected function init()
	{
		$this->resloveConfig();
	}

	protected function resloveConfig()
	{
		$options = $this->app->config->get('jwt.manager', []);

		$this->config = new Config($options);
	}

	protected function handleSSO(Plain $token): void
	{
		$jti = $token->claims()->get('jti');
		$store = $token->claims()->get('store');
		// dd($store);
		$this->destroyToken($jti, $store);
	}

	protected function pushWhitelist(Plain $token): void
	{
		$jti = $token->claims()->get('jti');
		$store = $token->claims()->get('store');

		$now = time();
		$exp = $token->claims()->get('exp');

		$ttl = $exp->getTimestamp() - $now;
		$tag = $store . '-' . $this->config->getWhitelist();

		$key = $this->makeKey($store, $this->config->getWhitelist(), $jti, $token);
		$this->setCache($tag, $key, $token, $ttl);
	}

	protected function pushBlacklist(Plain $token): void
	{
		$jti = $token->claims()->get('jti');
		$store = $token->claims()->get('store');

		$now = time();
		$exp = $token->claims()->get('exp');
		$ttl = $this->app->get('jwt.token')->getConfig()->getRefreshTTL();
		$exp = $exp->modify("+{$ttl} sec");
		$ttl = $exp->getTimestamp() - $now;
		$tag = $store . '-' . $this->config->getBlacklist();
		$key = $this->makeKey($store, $this->config->getBlacklist(), $jti, $token);

		$this->setCache($tag, $key, $token, $ttl);
	}

	protected function getBlacklist(Plain $token)
	{
		$jti = $token->claims()->get('jti');
		$store = $token->claims()->get('store');

		return $this->getCache($store, $this->config->getBlacklist(), $jti, $token);
	}

	protected function decodeFileCache($filename)
	{
		$content = @file_get_contents($filename);
		if ($content !== false) {
			$expire = (int) substr($content, 8, 12);

			$content = substr($content, 32);

			return is_string($content) ? ['content' => $content, 'expire' => $expire] : null;
		}
	}

	protected function clearStoreWhitelist($store): void
	{
		$this->clearTag($store . '-' . $this->config->getWhitelist());
	}

	protected function clearStoreBlacklist($store): void
	{
		$this->clearTag($store . '-' . $this->config->getBlacklist());
	}

	private function makeKey($store, $type, $uid, Token $token): string
	{
		return implode(':', [$this->config->getPrefix(), $store, $type, $uid, md5($token->toString())]);
	}

	private function clearTag($tag): void
	{
		$this->app->cache->tag($tag)->clear();
	}

	private function setCache($tag, $key, Token $token, $exp): void
	{
		$this->app->cache->tag($tag)->set($key, $token->toString(), $exp);
	}

	private function getCache($store, $type, $jti, $token)
	{
		$key = $this->makeKey($store, $type, $jti, $token);

		return $this->app->cache->get($key);
	}
}
