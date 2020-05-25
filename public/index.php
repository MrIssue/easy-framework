<?php
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));
defined('APP_START') or define('APP_START', microtime(true));
defined('STORAGE_PATH') or define('STORAGE_PATH', ROOT_PATH . '/storage');

require ROOT_PATH . '/vendor/autoload.php';

Core\Core::getInstance()->init();

$routes = require 'routes.php';

require 'functions.php';


if (config('app.debug') == true) {
    $dispatcher = FastRoute\simpleDispatcher($routes);
} else {
    $dispatcher = FastRoute\cachedDispatcher($routes, [
        'cacheFile' => storage_path('cache/route.php')
    ]);
}

// 获取请求的方法和 URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// 去除查询字符串( ? 后面的内容) 和 解码 URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        response('', 404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        response('Method Not Allow', 405);
        break;
    case FastRoute\Dispatcher::FOUND: // 找到对应的方法
        $handler = $routeInfo[1]; // 获得处理函数

        if ($handler instanceof Closure) {
            call_user_func_array($handler, $routeInfo[2]);
            break;
        } else {
            $route_arr = explode('@', $handler);
            $_ = '\\App\\Controllers\\' . $route_arr[0];
            if (class_exists($_)) {
                $request = Core\Http\Request::getInstance();
                Core\Events\Event::onRequest($request);
                $method = $route_arr[1];
                $class = new $_($request);
                call_user_func_array([$class, $method], $routeInfo[2]);
            }
        }
        break;
}
