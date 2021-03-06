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

namespace littler\jwt\Exception;

use Exception;
use think\exception\HttpException;

/**
 * 验证异常.
 */
class JWTException extends HttpException
{
	// public function __construct(string $message, $code = 0)
	// {
	// 	parent::__construct(401, $message, null, [], $code);
	// }
	protected const HTTP_SUCCESS = 401;

	public function __construct(string $message = '', int $code = 0, $statusCode = 0, array $headers = [], Exception $previous = null)
	{
		parent::__construct($statusCode, $message ?: $this->getMessage(), $previous, $headers, $code);
	}

	public function getStatusCode()
	{
		return self::HTTP_SUCCESS;
	}
}
