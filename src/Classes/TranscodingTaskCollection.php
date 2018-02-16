<?php
namespace Qencode\Classes;


class TranscodingTaskCollection {

    private $statusUrlsToTasksMap;
    private $api;

    public function __construct($api) {
        $this->api = $api;
        $this->statusUrlsToTasksMap = array();
    }

    /**
     * Adds transcoding task to collection
     * @param TranscodingTask $transcodingTask
     */
    public function add($transcodingTask) {
        $statusUrl = $transcodingTask->getStatusUrl();
        if (array_key_exists($statusUrl, $this->statusUrlsToTasksMap)) {
            $this->statusUrlsToTasksMap[$statusUrl][] = $transcodingTask;
        }
        else {
            $this->statusUrlsToTasksMap[$statusUrl] = [$transcodingTask];
        }
    }

    /**
     * Removes transcoding task from collection
     * @param TranscodingTask $transcodingTask
     */
    public function remove($transcodingTask) {
        //$token = $transcodingTask->getTaskToken();
        foreach ($this->statusUrlsToTasksMap as $statusUrl => $tasks) {
            if ($key = array_search($transcodingTask, $tasks) !== FALSE) {
                unset($tasks[$key]);
                return;
            }
        }
    }

    /**
     * Gets current tasks status from qencode service
     * @return array status API method response
     */
    public function getStatuses() {
        $result = [];
        foreach ($this->statusUrlsToTasksMap as $statusUrl => $tasks) {
            $response = $this->getStatusForUrl($statusUrl, $tasks);
            $result = array_merge($result, $response);
        }
        return $result;
    }

    private function getStatusForUrl($url, $tasks) {
        $params = array();
        foreach ($tasks as $task) {
            $params[] = 'task_tokens[]='.$task->getTaskToken();
        }
        $params = implode('&', $params);

        //TODO: fallback to /v1/status
        $response = $this->api->post($url, $params);
        return $response;
    }


}