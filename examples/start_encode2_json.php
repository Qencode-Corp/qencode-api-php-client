<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeClientException;
use Qencode\Exceptions\QencodeException;
use Qencode\Classes\CustomTranscodingParams;
use Qencode\Classes\Format;
use Qencode\Classes\Stream;
use Qencode\Classes\Destination;
use Qencode\Classes\Libx264_VideoCodecParameters;
use Qencode\QencodeApiClient;

// Replace this with your API key
$apiKey = '12345678';
$params = '
{"query": {
  "source": "https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4",
  "encoder_version": 2,
  "format": [
    {
      "output": "mp4",
      "size": "320x240",
      "video_codec": "libx264"
    }
  ]
  }
}';

$q = new QencodeApiClient($apiKey);

try {

    $task = $q->createTask();
    log_message("Created task: ".$task->getTaskToken());

    $task->startCustom($params);

    do {
        sleep(5);
        $response = $task->getStatus();
        if (is_array($response) and array_key_exists('percent', $response)) {
            log_message("Completed: {$response['percent']}%");
        }
    } while ($response['status'] != 'completed');

    foreach ($response['videos'] as $video) {
        log_message($video['user_tag'] . ': ' . $video['url']);
    }
    echo "DONE!";

} catch (QencodeClientException $e) {
    // We got some inconsistent state in client application (e.g. task_token not found when requesting status)
    log_message('Qencode Client Exception: ' . $e->getCode() . ' ' . $e->getMessage());
} catch (QencodeApiException $e) {
    // API response status code was not successful
    log_message('Qencode API Exception: ' . $e->getCode() . ' ' . $e->getMessage());
} catch (QencodeException $e) {
    // API call failed
    log_message('Qencode Exception: ' . $e->getMessage());
    var_export($q->getLastResponseRaw());
}

function log_message($msg) {
    echo $msg."\n";
}
