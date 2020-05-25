<?php
namespace Core\Cache;

use Carbon\Carbon;
use Exception;

class FileCache implements Cache
{
    protected static $instance = null;

    protected $directory = STORAGE_PATH . '/cache';

    protected function __construct() {}

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->getPayload($key)['data'] ?? null;
    }

    /**
     * 获取缓存内容，过期返回旧内容但不清空  返回时间单位(秒/s)
     * @param $key
     * @return array
     */
    public function getAsync($key)
    {
        $path = $this->path($key);

        try {
            $expire = substr(
                $contents = $this->getContents($path, true), 0, 10
            );
        } catch (Exception $e) {
            return [null, true, 0];
        }

        $past = false;

        if ($this->currentTime() >= $expire) {
            $past = true;
        }

        $data = unserialize(substr($contents, 10));

        $ttl = ($expire - $this->currentTime());

        return [$data, $past, $ttl];
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  int $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $this->ensureCacheDirectoryExists($path = $this->path($key));

        file_put_contents($path, $this->expiration($minutes).serialize($value), true);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        if (file_exists($file = $this->path($key))) {
            return $this->delete($file);
        }

        return false;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        if (! is_dir($this->directory)) {
            return false;
        }

        @rmdir($this->directory);
        @mkdir($this->directory);

        return true;
    }

    protected function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! @unlink($path)) {
                    $success = false;
                }
            } catch (Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param  string  $key
     * @return array
     */
    protected function getPayload($key)
    {
        $path = $this->path($key);

        // If the file doesn't exist, we obviously cannot return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        try {
            $expire = substr(
                $contents = $this->getContents($path, true), 0, 10
            );
        } catch (Exception $e) {
            return $this->emptyPayload();
        }

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if ($this->currentTime() >= $expire) {
            $this->forget($key);

            return $this->emptyPayload();
        }

        $data = unserialize(substr($contents, 10));

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on this cache on a later operation.
        $time = ($expire - $this->currentTime()) / 60;

        return compact('data', 'time');
    }

    protected function emptyPayload()
    {
        return ['data' => null, 'time' => null];
    }

    protected function ensureCacheDirectoryExists($path)
    {
        if (! file_exists(dirname($path))) {
            $this->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    protected function path($key)
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

        return $this->directory.'/'.implode('/', $parts).'/'.$hash;
    }

    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    protected function expiration($minutes)
    {
        $time = time() + ($minutes * 60);

        return $minutes === 0 || $time > 9999999999 ? 9999999999 : (int) $time;
    }

    /**
     * Get the contents of a file.
     *
     * @param  string $path
     * @param  bool $lock
     * @return string
     *
     * @throws Exception
     */
    public function getContents($path, $lock = false)
    {
        if (is_file($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        throw new Exception("File does not exist at path {$path}");
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    public function sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, filesize($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * 设置缓存目录
     * @param $path
     * @return $this
     */
    public function setCacheDir($path)
    {
        $this->directory = $path;

        return $this;
    }

    /**
     * 设置默认 cache 目录
     * @return $this
     */
    public function setDefaultDir()
    {
        $this->directory = STORAGE_PATH . '/cache';

        return $this;
    }

    protected function currentTime()
    {
        return Carbon::now()->getTimestamp();
    }
}
