<?php
/**
 * Created by PhpStorm.
 * User: zhangkansheng
 * Date: 2019/11/1
 * Time: 14:52
 */

namespace Core\Cache;


interface Cache
{
    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $value
     * @param $ttl
     * @return mixed
     */
    public function put($key, $value, $ttl);

    /**
     * @param $key
     * @param $value
     * @param $tto
     * @return mixed
     */
    public function forever($key, $value);

    /**
     * @param $key
     * @return mixed
     */
    public function forget($key);
}
