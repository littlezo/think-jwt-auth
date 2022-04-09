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

namespace littler\jwt\Config;

class Manager
{
	/**
	 * @var string
	 */
	protected $prefix = 'jwt';

	/**
	 * @var string
	 */
	protected $blacklist = 'blacklist';

	/**
	 * @var string
	 */
	protected $whitelist = 'whitelist';

	public function __construct(array $options = [])
	{
		if (!empty($options)) {
			foreach ($options as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * getPrefix.
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	/**
	 * getBlacklist.
	 */
	public function getBlacklist(): string
	{
		return $this->blacklist;
	}

	/**
	 * getWhitelist.
	 */
	public function getWhitelist(): string
	{
		return $this->whitelist;
	}
}
