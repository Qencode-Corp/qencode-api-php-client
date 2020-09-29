<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeException;
use Qencode\QencodeApiClient;
use Qencode\Classes\TranscodingTask;

//This example gets task status from main api endpoint (api.qencode.com)

$api_endpoint = 'https://api.qencode.com';
//replace with your api key
$apiKey = 'acbde123456';
//replace with your task token value
$task_token = 'fd17cc37c84f1233c0f62d6abcde123';
$status_url = $api_endpoint.'/v1/status';
$q = new QencodeApiClient($apiKey, $api_endpoint);

$params = array('task_tokens[]' => $task_token);
$response = $q->post($status_url, $params);
$status = $response['statuses'][$task_token];
if ($status == null) {
    throw new QencodeClientException('Task '. $task_token. ' not found!');
}
if (array_key_exists('status_url', $status)) {
    $status_url = $status['status_url'];
}
print_r($status);
