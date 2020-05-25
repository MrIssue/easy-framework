<?php
defined('ROOT_PATH') or define('ROOT_PATH', __DIR__);
defined('APP_START') or define('APP_START', microtime(true));
defined('STORAGE_PATH') or define('STORAGE_PATH', ROOT_PATH . '/storage');

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/public/functions.php';

Core\Core::getInstance()->init();

$key = $argv[1];
$value = file_cache()->get($key);

var_dump($value);