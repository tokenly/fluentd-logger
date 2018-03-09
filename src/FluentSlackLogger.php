<?php

namespace Tokenly\FluentdLogger;

use Exception;
use Fluent\Logger\Entity;
use Fluent\Logger\LoggerInterface;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelEventLog\Facade\EventLog;

/**
 * Class FluentSlackLogger
 */
class FluentSlackLogger
{
    /** @var LoggerInterface */
    protected $logger;

    protected $tag_prefix = null;

    /**
     * FluentSlackLogger constructor.
     *
     * @param LoggerInterface $logger
     * @param null|string     $tag_prefix
     */
    public function __construct(LoggerInterface $logger = null, $tag_prefix = null)
    {
        $this->logger = $logger;
        $this->tag_prefix = $tag_prefix;
    }

    /**
     * @param array $record
     */
    /**
     * Sends to slack
     * @param  string $channel channel name (without the #)
     * @param  string $title   The message summary
     * @param  string $msg     More message details
     */
    public function send($channel, $title, $msg = '')
    {
        try {
            $tag = $this->tag_prefix;

            // build an entity and post it
            $data = [
                'channel' => $channel,
                'title' => $title,
                'message' => $msg,
            ];
            $entity = new Entity($tag, $data);
            $this->logger->post2($entity);
        } catch (Exception $e) {
            EventLog::logError('fluentlog.slack.failed', $e, ['msg' => $msg]);
        }
    }

}
