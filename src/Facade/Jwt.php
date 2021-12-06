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

namespace littler\jwt\Facade;

use think\Facade;

/**
 * @see \littler\jwt\Service\Jwt
 * @mixin \littler\jwt\Service\Jwt
 */
class Jwt extends Facade
{
	/**
	 * 获取当前Facade对应类名（或者已经绑定的容器对象标识）.
	 *
	 * @return string
	 */
	protected static function getFacadeClass()
	{
		return \littler\jwt\Service\JwtAuth::class;
	}
}
