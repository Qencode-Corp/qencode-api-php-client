<?php

namespace Qencode\Classes;

use Qencode\Exceptions\QencodeException;
use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeClientException;

class TranscodingTask {

    public $api;
    private $taskToken;
    private $statusUrl;
    private $lastStatus;

    /**
     * Video clip start time
     */
    public $start_time;

    /**
     * Video duration
     */
    public $duration;

    /**
     * JSON-encoded string containing a dictionary (key-value pairs) of params for video or image output path.
     */
    public $output_path_variables;

    /**
     * Gets transcoding task token
     * @return string
     */
    public function getTaskToken() {
        return $this->taskToken;
    }

    /**
     * Gets task status url
     * @return string
     */
    public function getStatusUrl() {
        return $this->statusUrl;
    }

    
    /**
     * @param QencodeApiClient $api a reference to QencodeApiClient object
     * @param string $task_token transcoding task token
     */
    public function __construct($api, $task_token) {
        $this->api = $api;
        $this->taskToken = $task_token;
        $this->statusUrl = null;
        $this->start_time = null;
        $this->duration = null;
        $this->output_path_variables = new \stdClass();
        $this->subtitles = null;
    }

    private $stitchVideoItems;
    /**
     * Adds a video for stitching
     * @param string $url Source video URI. Can be http(s) url or tus uri
     * @return StitchVideoItem
     */
    public function AddStitchVideoItem($url) {
        $item = new StitchVideoItem();
        if (!isset($this->stitchVideoItems)) {
            $this->stitchVideoItems = [];
        }
        $item->url = $url;
        $this->stitchVideoItems[] = $item;
        return $item;
    }

    /**
     * Starts transcoding job using specified transcoding profile or list of profiles
     * @param string|array $transcodingProfiles One or several transcoding profile identifiers. Can be comma-separated string or an array
     * @param string $uri a link to input video or TUS uri (ignored in case of stitching)
     * @param string $transferMethod Transfer method identifier
     * @param string $payload Any string data of 1000 characters max length. E.g. you could pass id of your site user uploading the video or any json object.
     * @return array start_encode API method response
     */
    public function start($transcodingProfiles, $uri = null, $transferMethod = null, $payload = null) {
        $params = array(
            'task_token' => $this->taskToken,
            'profiles' => is_array($transcodingProfiles) ? implode(',', $transcodingProfiles) : $transcodingProfiles
        );
        $arrays = null;
        if (is_array($this->stitchVideoItems)) {
            $arrays = array('stitch' => $this->stitchVideoItems);
        }
        else {
            $params['uri'] = $uri;
        }
        if ($transferMethod) {
            $params['transfer_method'] = $transferMethod;
        }
        if ($payload) {
            $params['payload'] = $payload;
        }
        if ($this->start_time) {
            $params['start_time'] = $this->start_time;
        }
        if ($this->duration) {
            $params['duration'] = $this->duration;
        }
        if ($this->output_path_variables) {
            $params['output_path_variables'] = json_encode($this->output_path_variables);
        }
        if ($this->subtitles) {
            $params['subtitles'] = json_encode($this->subtitles);
        }
        $response = $this->api->post('start_encode', $params, $arrays);
        $this->statusUrl = $response['status_url'];
        return $response;
    }

    /**
     * Starts transcoding job using custom params
     * @param CustomTranscodingParams $task_params
     * @param string $payload Any string data of 1000 characters max length. E.g. you could pass id of your site user uploading the video or any json object.
     * @return array start_encode API method response
     */
    public function startCustom($task_params, $payload = null) {

        switch (gettype($task_params)) {
            case 'object':
                if (is_array($this->stitchVideoItems)) {
                    $task_params->stitch = $this->stitchVideoItems;
                }
                $query = array ('query' => $task_params);
                $query_json = json_encode($query);
                break;
            case 'string':
                $query_json = $task_params;
                break;
            default:
                $query_json = '';
        }

        $query_json = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $query_json);
        $params = array(
            'task_token' => $this->taskToken,
            'query' => $query_json
        );
        if ($payload) {
            $params['payload'] = $payload;
        }

        $response = $this->api->post('start_encode2', $params);
        $this->statusUrl = $response['status_url'];
        return $response;
    }

    private function _retryStatus($params) {
        $this->statusUrl = 'https://api.qencode.com/v1/status';
        return $this->api->post($this->statusUrl, $params);
    }

    /**
     * Gets current task status from qencode service
     * @return array status API method response
     */
    public function getStatus() {
        $params = array('task_tokens[]' => $this->taskToken);
        
        try {
            $response = $this->api->post($this->statusUrl, $params);
        }
        // If the post request fails, fallback to the default URL
        catch(QencodeException $qe) {
            $response = $this->_retryStatus($params);
        }
        catch(Exception $e) {
            $response = $this->_retryStatus($params);
        } 
        catch (Throwable $t) {
            $response = $this->_retryStatus($params);
        }

        $this->lastStatus = $response['statuses'][$this->taskToken];
        if ($this->lastStatus == null) {
            throw new QencodeClientException('Task '. $this->taskToken. ' not found!');
        }
        if (array_key_exists('status_url', $this->lastStatus)) {
            $this->statusUrl = $this->lastStatus['status_url'];
        }
        return $this->lastStatus;
    }

    /**
     * Gets last cached task status
     * @return array status API method response
     */
    public function getLastStatus() {
        return $this->lastStatus;
    }

    private $subtitles;
    private function initSubtitles() {
        $this->subtitles = new \stdClass();
        $this->subtitles->sources = array();
        $this->subtitles->copy = 0;
    }

    /**
     * Adds subtitles to a task
     * @param $source Subtitles file URL
     * @param $language Subtitles file language
     */
    public function addSubtitles($source, $language) {
        if ($this->subtitles == null) {
            $this->initSubtitles();
        }
        $sub = new \stdClass();
        $sub->source = $source;
        $sub->language = $language;
        $this->subtitles->sources[] = $sub;
    }

    /**
     * Sets subtitles / closed captions copy mode: 0 - disabled (default), 1 - existing eia608 or eia708 closed captions
     * are copied to output stream
     * @param $value
     */
    public function setSubtitlesCopyMode($value) {
        if ($this->subtitles == null) {
            $this->initSubtitles();
        }
        if ($value) {
            $this->subtitles->copy = 1;
        }
        else {
            $this->subtitles->copy = 0;
        }
    }
}
