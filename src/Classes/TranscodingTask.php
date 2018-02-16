<?php

namespace Qencode\Classes;

class TranscodingTask {

    private $api;
    private $taskToken;
    private $statusUrl;
    private $lastStatus;

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
    }

    /**
     * Starts transcoding job using specified transcoding profile or list of profiles
     * @param string|array $transcodingProfiles One or several transcoding profile identifiers. Can be comma-separated string or an array
     * @param string $uri a link to input video or TUS uri
     * @param string $transferMethod Transfer method identifier
     * @param string $payload Any string data of 1000 characters max length. E.g. you could pass id of your site user uploading the video or any json object.
     * @return array start_encode API method response
     */
    public function start($transcodingProfiles, $uri, $transferMethod = null, $payload = null) {
        $params = array(
            'task_token' => $this->taskToken,
            'uri' => $uri,
            'profiles' => is_array($transcodingProfiles) ? implode(',', $transcodingProfiles) : $transcodingProfiles
        );
        if ($transferMethod) {
            $params['transfer_method'] = $transferMethod;
        }
        if ($payload) {
            $params['payload'] = $payload;
        }

        $response = $this->api->post('start_encode', $params);
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
        $query = array ('query' => $task_params);
        $query_json = json_encode($query);
        $query_json = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $query_json);
        echo $query_json."<br><br>";
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

    /**
     * Gets current task status from qencode service
     * @return array status API method response
     */
    public function getStatus() {
        $params = array('task_tokens[]' => $this->taskToken);
        //TODO: fallback to /v1/status
        $response = $this->api->post($this->statusUrl, $params);
        $this->lastStatus = $response['statuses'][$this->taskToken];

        return $this->lastStatus;
    }

    /**
     * Gets last cached task status
     * @return array status API method response
     */
    public function getLastStatus() {
        return $this->lastStatus;
    }
}