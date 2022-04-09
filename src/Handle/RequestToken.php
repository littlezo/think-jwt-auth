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

namespace littler\jwt\Handle;

use littler\jwt\Exception\JWTException;
use think\App;

class RequestToken
{
    protected $handles = ['Header', 'Param', 'Cookie'];

    protected $token;

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 获取请求Token.
     *
     * @param array|string $handle
     */
    public function get($handle): string
    {
        if (is_string($handle)) {
            $handles = explode('|', $handle);
        }

        foreach ($handles as $handle) {
            if (in_array($handle, $this->handles, true)) {
                $namespace = '\\littler\\jwt\Handle\\' . $handle;
                $token = (new $namespace($this->app))->handle();
                if ($token) {
                    $this->token = $token;
                    break;
                }
                continue;
            }
            throw new JwtException('不支持此方式获取.', 406);
        }

        if (!$this->token) {
            throw new JwtException('获取Token失败.', 412);
        }

        return $this->token;
    }
}
