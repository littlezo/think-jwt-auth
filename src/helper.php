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

use think\facade\Config;

if (!function_exists('jwt')) {
	function jwt($uid, $options = [])
	{
		return app('jwt')->token($uid, $options)->toString();
	}
}
if (!function_exists('config')) {
	/**
	 * 获取和设置配置参数.
	 *
	 * @param array|string $name  参数名
	 * @param mixed        $value 参数值
	 *
	 * @return mixed
	 */
	function config($name = '', $value = null)
	{
		if (is_array($name)) {
			return Config::set($name, $value);
		}

		return 0 === strpos($name, '?') ? Config::has(substr($name, 1)) : Config::get($name, $value);
	}
}
