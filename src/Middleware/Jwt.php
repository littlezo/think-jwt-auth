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
use think\facade\Event;
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
		if ('OPTIONS' == $request->method(true)) {
			return Response::create()->code(204);
		}
		$ignore_verify = $request->rule()->getOption('ignore_verify') ?? false;
		if ($ignore_verify) {
			return $next($request);
		}
		try {
			if (true === $this->app->get('jwt')->store($store)->verify()) {
				$this->bind($request, $store);

				return $next($request);
			}
			throw new JWTException('Token 验证不通过', 401);
		} catch (\Throwable $e) {
			if (true === $this->app->get('jwt')->store($store)->isRefreshExpired()) {
				$this->app->get('jwt')->store($store)->refreshToken();
				$this->bind($request, $store);

				return $next($request);
			} else {
				throw new JWTException($e->getMessage(), 401);
			}
		}
	}

	public function bind($request, $store)
	{
		if ($this->app->get('jwt.user')->getBind()) {
			if ($user = $this->app->get('jwt.user')->find()) {
				// 路由注入
				unset($user->pay_passwd, $user->payment, $user->pay_password, $user->passwd, $user->password);
				$request->user = $user;
				// 绑定当前用户模型
				$class = $this->app->get('jwt.user')->getClass();
				$this->app->bind($class, $user);
				// 绑定用户后一些业务处理
				Event::trigger('UserAuthAfter', $user);
			} else {
				if (true === $this->app->get('jwt')->store($store)->isRefreshExpired()) {
					$this->app->get('jwt')->store($store)->refreshToken();
				} else {
					throw new JWTException('登录校验已失效, 请重新登录', 401);
				}
			}
		}
	}
}
