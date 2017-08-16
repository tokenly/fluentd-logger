<?php

namespace Tokenly\FluentdLogger;

use Exception;
use Fluent\Logger\Entity;
use Fluent\Logger\LoggerInterface;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelEventLog\Facade\EventLog;

/**
 * Class FluentEventLogger
 */
class FluentEventLogger
{
    /** @var LoggerInterface */
    protected $logger;

    protected $app_prefix = null;

    /**
     * FluentEventLogger constructor.
     *
     * @param LoggerInterface $logger
     * @param null|string     $tagFormat
     * @param int             $level
     * @param bool            $bubble
     */
    public function __construct(LoggerInterface $logger=null, $app_prefix=null)
    {
        $this->logger     = $logger;
        $this->app_prefix = $app_prefix;
    }

    /**
     * @param array $record
     */
    public function log($event, $data, $tags, $override_time=null)
    {
        try {
            $tag = 
                ($this->app_prefix !== null ? $this->app_prefix.'.' : '').
                $event;

            // build an entity and post it
            $data = $this->formatFluentLogData($data, $tags);
            $entity = new Entity($tag, $data, $override_time);
            $this->logger->post2($entity);
        } catch (Exception $e) {
            EventLog::logError('fluentlog.failed', $e, ['event' => $event]);
        }
    }

    // ------------------------------------------------------------------------

    protected function formatFluentLogData($data, $tags=null) {
        if ($tags === null AND is_string($data)) {
            $data = [$data => 1];
            $tags = [];
        } else if ($tags === null) {
            $tags = [];
        }

        if (!is_array($data)) {
            throw new Exception("Unexpected data type (".gettype($data).") for ".(is_object($data) ? get_class($data) : substr(json_encode($data), 0, 200))."", 1);
        }
        if (!is_array($tags)) {
            throw new Exception("Unexpected tags type (".gettype($tags).") for ".(is_object($tags) ? get_class($tags) : substr(json_encode($tags), 0, 200))."", 1);
        }

        // always include at least one numeric value
        if (!$data) {
            $data['_count'] = 1;
        }

        $formatted_data = [];

        // cast all tags as strings
        foreach($tags as $tag_key => $tag_value) {
            $formatted_data[$tag_key] = (string)$tag_value;
        }
        

        // cast all data as numeric
        foreach($data as $data_key => $data_val) {
            if (is_numeric($data_val)) {
                if (is_float($data_val+0)) {
                    $data_val = floatval($data_val);
                } else {
                    // assume int if not float
                    $data_val = intval($data_val);
                }
                $formatted_data[$data_key] = $data_val;
            } else {
                Log::error("Data measurement $data_key was not numeric.  Value was ".json_encode($data_val));
                $formatted_data[$data_key] = 0;
            }
        }


        return $formatted_data;
    }


}
