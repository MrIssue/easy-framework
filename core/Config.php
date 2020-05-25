<?php
namespace Core;

use Core\Plugins\Arr;

class Config
{
    protected static $instance = null;

    protected $items;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        $dotenv = \Dotenv\Dotenv::create(ROOT_PATH);
        $dotenv->load();
        $handler = opendir(ROOT_PATH . '/config');

        while (($filename = readdir($handler)) !== false) {
            if ($filename != '.' && $filename != '..') {
                $config = include_once ROOT_PATH . '/config/' . $filename;
                $this->set([substr($filename, 0, -4) => $config]);
            }
        }

        closedir($handler);

        return $this;
    }

    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }

    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($this->items, $key, $default);
    }

    public function getMany($keys)
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                list($key, $default) = [$default, null];
            }

            $config[$key] = Arr::get($this->items, $key, $default);
        }

        return $config;
    }
}
