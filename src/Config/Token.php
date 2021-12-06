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

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Ecdsa\Sha256 as ES256;
use Lcobucci\JWT\Signer\Ecdsa\Sha384 as ES384;
use Lcobucci\JWT\Signer\Ecdsa\Sha512 as ES512;
use Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HS256;
use Lcobucci\JWT\Signer\Hmac\Sha384 as HS384;
use Lcobucci\JWT\Signer\Hmac\Sha512 as HS512;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RS256;
use Lcobucci\JWT\Signer\Rsa\Sha384 as RS384;
use Lcobucci\JWT\Signer\Rsa\Sha512 as RS512;
use littler\jwt\Exception\JWTException;

class Token
{
	/**
	 * signer_key.
	 *
	 * @var mixed
	 */
	protected $signer_key;

	/**
	 * not_before.
	 *
	 * @var int
	 */
	protected $not_before = 0;

	/**
	 * expires_at.
	 *
	 * @var int
	 */
	protected $expires_at = 3600;

	/**
	 * refresh_ttL.
	 *
	 * @var int
	 */
	protected $refresh_ttL = 7200;

	/**
	 * signer.
	 *
	 * @var string
	 */
	protected $signer = 'HS256';

	/**
	 * type.
	 *
	 * @var string
	 */
	protected $type = 'Header';

	/**
	 * expires_code.
	 *
	 * @var int
	 */
	protected $expires_code = 904010;

	/**
	 * refresh_code.
	 *
	 * @var int
	 */
	protected $refresh_code = 904011;

	/**
	 * iss.
	 *
	 * @var string
	 */
	protected $iss = 'client.stye.cn';

	/**
	 * aud.
	 *
	 * @var string
	 */
	protected $aud = 'server.stye.cn';

	/**
	 * automatic_renewal.
	 *
	 * @var bool
	 */
	protected $automatic_renewal = false;

	/**
	 * public_key.
	 *
	 * @var string
	 */
	protected $public_key = '';

	/**
	 * private_key.
	 *
	 * @var string
	 */
	protected $private_key = '';

	/**
	 * signers.
	 *
	 * @var array
	 */
	protected $signers = [
		'HS256' => HS256::class,
		'HS384' => HS384::class,
		'HS512' => HS512::class,
		'RS256' => RS256::class,
		'RS384' => RS384::class,
		'RS512' => RS512::class,
		'ES256' => ES256::class,
		'ES384' => ES384::class,
		'ES512' => ES512::class,
	];

	/**
	 * __construct.
	 *
	 * @param mixed $options
	 *
	 * @return mixed
	 */
	public function __construct(array $options)
	{
		if (! empty($options)) {
			foreach ($options as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * getHamcKey.
	 */
	public function getHamcKey(): Key
	{
		if (empty($this->signer_key)) {
			throw new JWTException('config signer_key required.', 500);
		}
		// dd($this->signer_key);
		return InMemory::plainText((string) $this->signer_key);

		return InMemory::base64Encoded((string) $this->signer_key);
	}

	/**
	 * RSASigner.
	 */
	public function RSASigner(): bool
	{
		$signer = $this->getSigner();

		return $signer instanceof Rsa;
	}

	/**
	 * getSignerKey.
	 */
	public function getSignerKey(): Key
	{
		$signer = $this->getSigner();
		if ($this->RSASigner()) {
			return $this->getPrivateKey();
		}
		if ($signer instanceof Hmac) {
			return $this->getHamcKey();
		}
		throw new JWTException('not support.', 500);
	}

	/**
	 * getPublicKey.
	 */
	public function getPublicKey(): Key
	{
		return LocalFileReference::file($this->public_key);
	}

	/**
	 * getPrivateKey.
	 */
	public function getPrivateKey(): Key
	{
		return LocalFileReference::file($this->private_key);
	}

	public function getExpires(): int
	{
		// dd($this->expires_at);
		return (int) $this->expires_at;
	}

	/**
	 * getRefreshTTL.
	 */
	public function getRefreshTTL(): int
	{
		return $this->refresh_ttL;
	}

	/**
	 * getIss.
	 */
	public function getIss(): string
	{
		return $this->iss;
	}

	/**
	 * getAud.
	 */
	public function getAud(): string
	{
		return $this->aud;
	}

	/**
	 * getNotBefore.
	 */
	public function getNotBefore(): int
	{
		return $this->not_before;
	}

	/**
	 * getSigner.
	 */
	public function getSigner(): Signer
	{
		if (! isset($this->signers[$this->signer])) {
			throw new JWTException('Cloud not find ' . $this->signer . ' signer', 500);
		}

		return new $this->signers[$this->signer]();
	}

	/**
	 * getExpiresCode.
	 */
	public function getExpiresCode(): int
	{
		return $this->expires_code;
	}

	/**
	 * getRefreshCode.
	 */
	public function getRefreshCode(): int
	{
		return $this->refresh_code;
	}

	/**
	 * getAutomaticRenewal.
	 */
	public function getAutomaticRenewal(): bool
	{
		return $this->automatic_renewal;
	}

	/**
	 * getType.
	 */
	public function getType(): string
	{
		return $this->type;
	}
}
