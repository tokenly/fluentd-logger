<?php

namespace Tokenly\FluentdLogger;

use Fluent\Logger\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class FluentMonologHandler
 */
class FluentMonologHandler extends AbstractProcessingHandler
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * FluentMonologHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param null|string     $tagFormat
     * @param int             $level
     * @param bool            $bubble
     */
    public function __construct(LoggerInterface $logger, $tag, $level = Logger::DEBUG, $bubble = true)
    {
        $this->logger = $logger;
        $this->tag    = $tag;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $this->logger->post(
            $this->tag,
            [
                'level'   => $record['level_name'],
                'message' => $record['message'],
                'mt'      => intval(round(microtime(true) * 1000)),
            ]
        );
    }


}