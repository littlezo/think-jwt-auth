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

namespace littler\jwt;

use littler\jwt\Command\JwtCommand;
use littler\jwt\Service\JwtAuth;
use littler\jwt\Service\Manager;
use littler\jwt\Service\SSO;
use littler\jwt\Service\Token;
use littler\jwt\Service\User;

class Service extends \think\Service
{
	public function register(): void
	{
		$this->app->bind('jwt', JwtAuth::class);
		$this->app->bind('jwt.manager', Manager::class);
		$this->app->bind('jwt.token', Token::class);
		$this->app->bind('jwt.sso', SSO::class);
		$this->app->bind('jwt.user', User::class);
	}

	public function boot(): void
	{
		$this->commands(JwtCommand::class);
	}
}
