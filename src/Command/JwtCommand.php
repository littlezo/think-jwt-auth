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

namespace littler\JWTAuth\Command;

use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpFile;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * randomKey.
 */
function randomKey()
{
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~0123456789#$%^&';
	$pass = [];
	$alphaLength = strlen($alphabet) - 1;
	for ($i = 0; $i < 64; ++$i) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}

	return implode($pass);
}

class JwtCommand extends Command
{
	/**
	 * configure.
	 */
	protected function configure(): void
	{
		$this->setName('jwt:make')->setDescription('生成一个签名密钥');
	}

	/**
	 * execute.
	 *
	 * @param mixed $input
	 * @param mixed $output
	 *
	 * @return mixed
	 */
	protected function execute(Input $input, Output $output)
	{
		$file = new PhpFile();
		$file->addComment('Jwt 配置');
		$file->setStrictTypes();

		$config = config('jwt');
		$default = $config['default'];

		$config['stores'][$default]['token']['signer_key'] = randomKey();
		$config = 'return ' . Helpers::dump($config) . ';';
		// dd($config);
		file_put_contents($this->app->getConfigPath() . 'jwt.php', $file . $config);
		$output->writeln('> success!');
	}
}
