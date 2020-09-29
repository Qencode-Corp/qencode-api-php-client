<?php

namespace Qencode\Classes;

use Qencode\Exceptions\QencodeClientException;

class Metadata extends TranscodingTask {
    function __construct($api, $task_token)
    {
        // вызов конструктора базового класс
        parent::__construct($api, $task_token);
    }
    /**
     * Gets Metadata vidoe
     * @return object
     */
    public function get(string $video_url) {
        $params = new CustomTranscodingParams();
        $format = new Format();
        $format->output = "metadata";
        $format->metadata_version = "4.1.5";
        $params->source = $video_url;
        $params->format = [$format];
        parent::startCustom($params);
        do {
            sleep(5);
            $response =parent::getStatus();
            if (is_array($response) and array_key_exists('percent', $response)) {
                log_message("Completed: {$response['percent']}%");
            }
        } while ($response['status'] != 'completed');

        $urlMet = '';
        foreach ($response['videos'] as $video) {
            $urlMet = $video['url'];
        }
        $response = $this->api->get($urlMet);
        return $response;
    }
}