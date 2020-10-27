<?php

declare(strict_types=1);

namespace Yansongda\Supports;

/**
 * @method static void emergency($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void log($message, array $context = [])
 */
class Log extends Logger
{
    /**
     * instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private static $instance;

    /**
     * Bootstrap.
     */
    private function __construct()
    {
    }

    /**
     * __call.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Exception
     */
    public function __call(string $method, array $args): void
    {
        call_user_func_array([self::getInstance(), $method], $args);
    }

    /**
     * __callStatic.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Exception
     */
    public static function __callStatic(string $method, array $args): void
    {
        forward_static_call_array([self::getInstance(), $method], $args);
    }

    /**
     * getInstance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return \Yansongda\Supports\Logger
     */
    public static function getInstance(): Logger
    {
        if (is_null(self::$instance)) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    /**
     * setInstance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param \Yansongda\Supports\Logger $logger
     *
     * @throws \Exception
     */
    public static function setInstance(Logger $logger): void
    {
        self::$instance = $logger;
    }
}