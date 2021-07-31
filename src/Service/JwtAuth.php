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

use Lcobucci\JWT\Token;
use littler\BaseModel;
use littler\exceptions\LoginFailedException;
use littler\JWTAuth\Exception\JWTException;
use littler\JWTAuth\Exception\TokenAlreadyEexpired;
use littler\user\AuthorizeInterface;
use think\App;

class JwtAuth
{
	/**
	 * 应用名称.
	 *
	 * @var App
	 */
	protected $app;

	/**
	 * 应用名称.
	 *
	 * @var string
	 */
	protected $store;

	/**
	 * @var array|object|string
	 */
	protected $user;

	/**
	 * model.
	 *
	 * @var BaseModel
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $username= 'username';

	/**
	 * @var string
	 */
	protected $password = 'password';

	/**
	 * @var bool
	 */
	protected $verifyPassword = true;

	public function __construct(App $app)
	{
		$this->app = $app;

		$this->init();
	}

	public function username(string $username = null): self
	{
		if ($username) {
			$this->username = $username;
		}

		return $this;
	}

	public function password(string $password = null): self
	{
		if ($password) {
			$this->password = $password;
		}

		return $this;
	}

	/**
	 * 忽略密码认证
	 *
	 * @return $this
	 */
	public function ignorePasswordVerify(): self
	{
		$this->verifyPassword = false;
		return $this;
	}

	public function store(string $store = 'default'): self
	{
		if ($store) {
			$this->store = $store;
		}

		return $this;
	}

	public function getStore()
	{
		return $this->store ?? $this->getDefaultApp();
	}

	/**
	 * 生成 Token.
	 *
	 * @param mixed $identifier
	 */
	public function token($identifier, array $claims = []): Token
	{
		$token = $this->app->get('jwt.token')->make($identifier, $claims);
		$this->app->get('jwt.manager')->login($token);

		return $token;
	}

	/**
	 * 验证 Token.
	 *
	 * @param string $token
	 */
	public function verify(string $token = null): bool
	{
		$service = $this->app->get('jwt.token');
		if (! $token) {
			$token = $service->getRequestToken();
		}

		// 是否存在黑名单
		$this->wasBan($token);

		// 检测合法性
		if ($service->validate($token)) {
			return $service->validateExp($token);
		}

		return false;
	}

	public function login(array $args): string
	{
		$this->user($args);
		$user = $this->user;

		if (! $user) {
			throw new LoginFailedException('登录失败，请检查用户名或密码', 900900);
		}
		if ($user->status == $user::$disable) {
			throw new LoginFailedException('该用户已被禁用|' . $user->username ?? null, 900901);
		}
		if ($this->verifyPassword && ! password_verify($args['password'], $this->user->getOrigin()['password'])) {
			throw new LoginFailedException('登录失败,密码错误', 900902);
		}
		unset($user->pay_passwd,$user->pay_password,$user->passwd,$user->password,$user->{$this->password});
		return $this->token($user->{$user->getAutoPk()}, $user->toArray())->toString();
	}

	public function user($args)
	{
		$this->getModel();
		$condition = $this->filter($args);
		if (! $condition||$this->verifyPassword &&  ! isset($args['password'])) {
			throw new LoginFailedException('登录失败，参数错误', 900900);
		}
		if ($this->model->hasUser($condition)) {
			$this->user = $this->model->getUser($condition);
		}
		return $this;
	}

	public function logout(string $token = null)
	{
		$service = $this->app->get('jwt.token');
		if (! $token) {
			$token = $service->getRequestToken();
		}

		$token = $service->parse($token);
		$this->app->get('jwt.manager')->logout($token);
	}

	// Todo 等待优化

	/**
	 * @param $condition
	 */
	protected function filter($condition): array
	{
		$this->getModel();
		$where = [];
		$fields = array_keys($this->model->getFields());
		foreach ($condition as $field => $value) {
			if (in_array($field, $fields, true) && $field === $this->username) {
				$where[$field] = $value;
			}
		}
		// dd($where);
		return $where;
	}

	protected function init()
	{
	}

	protected function getModel()
	{
		try {
			$class = $this->app->get('jwt.user')->getModel();
			// dd($class);
			$model = new $class();
			if ($model instanceof AuthorizeInterface) {
				$this->model =  $model;
				return $this;
			}
			throw new JWTException('implements ' . AuthorizeInterface::class);
		} catch (JWTException $e) {
			throw new JWTException($e->getMessage());
		} catch (\Exception $e) {
			$store = $this->getStore();
			throw new JWTException("{$store}应用未配置用户模型文件");
		}
	}

	protected function getDefaultApp(): string
	{
		return $this->app->http->getName() ?: 'default';
	}

	protected function wasBan($token)
	{
		$token = $this->app->get('jwt.token')->parse($token);
		if ($this->app->get('jwt.manager')->wasBan($token) === true) {
			$config = $this->app->get('jwt.token')->getConfig();

			throw new TokenAlreadyEexpired('token was ban', $config->getExpiresCode());
		}
	}
}
