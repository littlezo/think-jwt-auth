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

namespace littler\jwt\Middleware;

use littler\jwt\Exception\JWTException;
use think\App;
use think\Response;

/**
 * 中间件.
 */
class Jwt
{
	/**
	 * app.
	 *
	 * @var mixed
	 */
	protected $app;

	/**
	 * __construct.
	 *
	 * @param mixed $app
	 */
	public function __construct(App $app)
	{
		$this->app = $app;
	}

	/**
	 * handle.
	 *
	 * @param mixed $request
	 * @param mixed $next
	 * @param mixed $store
	 */
	public function handle($request, \Closure $next, $store = null)
	{
		if ($request->method(true) == 'OPTIONS') {
			return Response::create()->code(204);
		}
		$ignore_verify = $request->rule()->getOption('ignore_verify')??false;
		// $ignore_verify = true;
		if ($ignore_verify) {
			return $next($request);
		}
		// return $this->app->get('jwt.token')->automaticRenewalToken();
		try {
			if ($this->app->get('jwt')->store($store)->verify() === true) {
				if ($this->app->get('jwt.user')->getBind()) {
					if ($user = $this->app->get('jwt.user')->find()) {
						// 路由注入
						unset($user->pay_passwd,$user->pay_password,$user->passwd,$user->password);
						$request->user = $user;
						// 绑定当前用户模型
						$class = $this->app->get('jwt.user')->getClass();
						$this->app->bind($class, $user);
						// 绑定用户后一些业务处理
						$this->bindUserAfter($request);
					} else {
						throw new JWTException('登录校验已失效, 请重新登录', 401);
					}
				}
				return $next($request);
			}
			throw new JWTException('Token 验证不通过', 401);
		} catch (\Throwable $e) {
			throw new JWTException('Token 验证不通过', 401);
		}
	}

	/**
	 * bindUserAfter.
	 *
	 * @param mixed $request
	 */
	protected function bindUserAfter($request): void
	{
		// 当前用户
		// $request->user
	}
}
