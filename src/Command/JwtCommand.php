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

namespace littler\jwt\Command;

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;
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
		$this->setName('jwt:make')->setDescription('生成一个应用签名密钥 ');
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
		$dumper = new Dumper();
		$file->addComment('Jwt 配置');
		$file->setStrictTypes();
		$stores = '';
		if (! $stores) {
			while (true) {
				$stores = $output->ask($input, '请输入应用名称 如 api') ?: 'member';
				if ($stores) {
					break;
				}
			}
		}
		$class = '';
		if (! $class) {
			while (true) {
				$class = $output->ask($input, '请输入模型类 完整命名空间  如 little\member\model\Member') ?: 'little\member\model\Member';
				if ($class) {
					break;
				}
			}
		}

		$sso_answer = strtolower($this->output->ask($this->input, '是否单点登录 (Y/N)  默认是: ') ?: 'y');
		$sso = false;
		if ($sso_answer === 'y' || $sso_answer === 'yes') {
			$sso = true;
		}

		$bind_answer = strtolower($this->output->ask($this->input, '是否注入用户模型 (Y/N)  默认是: ') ?: 'y');
		$bind = false;
		if ($bind_answer === 'y' || $bind_answer === 'yes') {
			$bind = true;
		}
		$auto_answer = strtolower($this->output->ask($this->input, '开启过期自动续签 (Y/N)  默认是: ') ?: 'y');
		$auto = false;
		if ($auto_answer === 'y' || $auto_answer === 'yes') {
			$auto = true;
		}
		$expires_at = '';
		if (! $expires_at) {
			while (true) {
				$expires_at = $this->output->ask($this->input, 'Token 有效期(秒)  默认 1天 86400秒: ') ?: 86400;
				if ($expires_at) {
					break;
				}
			}
		}
		$signer = '';
		if (! $signer) {
			while (true) {
				$signer = strtoupper($this->output->ask($this->input, '加密算法 默认是 HS256，即 hash 256 位加密: ') ?: 'hs256');
				if ($signer) {
					break;
				}
			}
		}
		$type ='';
		if (! $type) {
			while (true) {
				$type = $this->output->ask($this->input, '获取 Token 途径 Header Param Cookie |分割 默认 Header|Param: ') ?: 'Header|Param';
				if ($type) {
					break;
				}
			}
		}
		$default_template= include dirname(dirname(__DIR__)) . '/config/config.php';
		$default_config = $default_template['stores']['default'];
		$default_config['sso']['enable'] = $sso;
		$default_config['token']['signer_key'] = randomKey();
		$default_config['token']['expires_at'] = $expires_at;
		$default_config['token']['refresh_ttL'] = $expires_at+86400;
		$default_config['token']['signer'] = $signer;
		$default_config['token']['type'] = $type;
		$default_config['token']['automatic_renewal'] = $auto;
		$default_config['user']['bind'] = $bind;
		$default_config['user']['class'] = $class;
		// dd($default_config);
		$config['stores'][$stores] = $default_config;

		$reflector = new ReflectionClass($class);
		$fn = $reflector->getFileName();
		$module_file = dirname(dirname($fn)) . '/config/config.php';
		$module_config = [];
		if (file_exists($module_file)) {
			$module_config = include $module_file;
		}
		$module_config['jwt'] = $config;
		$config = 'return ' . $dumper->dump($module_config) . ';';
		// echo $config;
		// dd();
		file_put_contents($module_file, $file . $config);
		$output->writeln('> success!');
	}
}
