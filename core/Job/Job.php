<?php
namespace Core\Job;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Job
{
    protected static $pushed = 'app.jobs.pushed';

    protected static $sPushed = 'app.jobs.spushed';

    protected static $failed = 'app.jobs.failed';

    public static function push($key, $job, $unique = true)
    {
        $logger = new Logger('job');
        $logger->pushHandler(new RotatingFileHandler(storage_path('logs/job.log'), config('log.job.max_file'), Logger::DEBUG));

        try {
            $redis = new \Redis();
            $redis->connect(config('cache.redis.host'), config('cache.redis.port'));

            $job = self::createPayload($key, $job);
            if ($unique) {
                if (!$redis->sIsMember(self::$sPushed, $key)) {
                    $redis->sAdd(self::$sPushed, $key);  // 这两种方法顺序不可以颠倒
                    $redis->rPush(self::$pushed, $job);  // 否则由于 sAdd 比 blpop 慢会出现问题
                    $logger->info('add Job ' . $key, [$job]);
                }
            } else {
                $redis->sAdd(self::$sPushed, $key);
                $redis->rPush(self::$pushed, $job);
                $logger->info('add Job ' . $key, [$job]);
            }
            $redis->close();

            return true;
        } catch (\Exception $e) {
            $logger->error('add Job Error ' . $e->getMessage());

            return false;
        }
    }

    public static function run()
    {
        $redis = new \Redis();
        $redis->pconnect(config('cache.redis.host'), config('cache.redis.port'));

        $logger = new Logger('job');
        $logger->pushHandler(new RotatingFileHandler(storage_path('logs/job.log'), config('log.job.max_file'), Logger::DEBUG));

        try {
            while ($jobs = $redis->blPop([self::$pushed], 0)) {
                $times = 0;
                $result = false;
                $job = json_decode($jobs[1], true);
                $commandName = $job['data']['commandName'];
                $command = unserialize($job['data']['command']);

                while ($times < $job['maxTries'] && !$result) {
                    $result = call_user_func([$command, 'handler']);
                    $times++;
                }

                $redis->sRem(self::$sPushed, $job['key']);

                if ($result === false) {
                    $failedJob = self::createPayload($job['key'], $command);
                    $redis->rPush(self::$failed, $failedJob);
                    $logger->error('Job Failed ' . $commandName, [$command]);
                } else {
                    $logger->info('Job Success '. $job['key'] . $commandName);
                }
            }
        } catch (\Exception $e) {
            if (isset($job['key'])) {
                $redis->sRem(self::$sPushed, $job['key']);
            }
            $logger->error('Job Filed ' . $e->getMessage());
        }
    }

    public static function flush()
    {
        $redis = new \Redis();
        $redis->connect(config('cache.redis.host'), config('cache.redis.port'));
        $redis->del(self::$pushed, self::$sPushed, self::$failed);
    }

    /**
     * @param $key
     * @param $job
     * @return false|string
     * @throws \Exception
     */
    protected static function createPayload($key, $job)
    {
        $payload = json_encode(self::createObjectPayload($key, $job));

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(
                'Unable to JSON encode payload. Error code: '.json_last_error()
            );
        }

        return $payload;
    }

    protected static function createObjectPayload($key, $job)
    {
        return [
            'key' => $key,
            'maxTries' => $job->tries ?? 1,
            'timeout' => $job->timeout ?? null,
            'data' => [
                'commandName' => get_class($job),
                'command' => serialize(clone $job),
            ],
        ];
    }
}
