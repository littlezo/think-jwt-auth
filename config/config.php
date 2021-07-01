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

return [
	'stores' => [
		// 单应用
		'default' => [
			'sso' => [
				'enable' => false,
			],
			'token' => [
				'signer_key' => 'littler',
				'public_key' => 'file://path/public.key',
				'private_key' => 'file://path/private.key',
				'not_before' => 0,
				'expires_at' => 3600,
				'refresh_ttL' => 7200,
				'signer' => 'HS256',
				'type' => 'Header',
				'expires_code' => 904010,
				'refresh_code' => 904011,
				'iss' => 'client.littler',
				'aud' => 'server.littler',
				'automatic_renewal' => false,
			],
			'user' => [
				'bind' => false,
				'class' => null,
			],
		],
	],
	'manager' => [
		// 缓存前缀
		'prefix' => 'jwt',
		// 黑名单缓存名
		'blacklist' => 'blacklist',
		// 白名单缓存名
		'whitelist' => 'whitelist',
	],
];
