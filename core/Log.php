<?php
namespace Core;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Handler\FirePHPHandler;

/**
 * Class Log
 * @package App\Core
 * @method bool error($message, array $context = array())
 * @method bool info($message, array $context = array())
 * @method bool warning($message, array $context = array())
 * @method bool notice($message, array $context = array())
 * @method bool debug($message, array $context = array())
 */

class Log
{
    protected static $instance = null;

    /**
     * @var $logger Logger
     */
    protected static $logger = null;

    protected $log_file;

    protected $levels = [
        'debug'     => Logger::DEBUG,
        'info'      => Logger::INFO,
        'notice'    => Logger::NOTICE,
        'warning'   => Logger::WARNING,
        'error'     => Logger::ERROR,
        'critical'  => Logger::CRITICAL,
        'alert'     => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

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
        self::$logger= new Logger(config('app.name'));
        $this->log_file = STORAGE_PATH . '/logs/app.log';
        $this->configureHandler();
        self::$logger->pushHandler(new FirePHPHandler());
    }

    protected function configureHandler()
    {
        $this->{'configure'.ucfirst($this->handler()).'Handler'}();
    }

    protected function configureSingleHandler()
    {
        self::$logger->pushHandler(new StreamHandler($this->log_file, $this->parseLevel($this->logLevel())));
    }

    protected function configureDailyHandler()
    {
        self::$logger->pushHandler(new RotatingFileHandler($this->log_file, $this->maxFiles(), $this->parseLevel($this->logLevel())));
    }

    protected function handler()
    {
        return config('app.log', 'single');
    }

    protected function logLevel()
    {
        return config('app.log_level', 'debug');
    }

    protected function maxFiles()
    {
        return config('app.log_max_files', 10);
    }

    protected function parseLevel($level)
    {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        return $this->levels['debug'];
    }

    public static function __callStatic($name, $arguments)
    {
        forward_static_call_array([self::$logger, $name], $arguments);
    }
}
