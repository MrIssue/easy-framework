<?php

namespace Core;


class Core
{
    protected static $instance = null;

    protected $server;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function init()
    {
        require ROOT_PATH . '/core/helpers.php';
        $this->configInit();
        $this->logInit();
        $this->registerErrorHandler();
        date_default_timezone_set(config('app.timezone'));

        return $this;
    }

    private function configInit()
    {
        Config::getInstance()->init();
    }

    private function logInit()
    {
        Log::getInstance()->init();
    }

    private function registerErrorHandler()
    {
        if (config("app.debug") == true) {
            ini_set("display_errors", "On");
            error_reporting(E_ALL | E_STRICT);
            set_error_handler(function ($errorCode, $description, $file = null, $line = null, $context = null) {
                Log::error($description, [$file, $line, $errorCode, debug_backtrace()]);
            });

            register_shutdown_function(function () {
                $error = error_get_last();
                if (!empty($error)) {
                    Log::error($error['message'], [$this->getErrorType()[$error['type']] ?? 'ERROR',$error['file'], $error['line'], E_ERROR, debug_backtrace()]);
                    //HTTP下，发生致命错误时，原有进程无法按照预期结束链接,强制执行end
                }
            });
        }
    }

    private function getErrorType()
    {
        return [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];
    }
}
