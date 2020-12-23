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
use Qencode\Classes\Drm;
use Qencode\QencodeApiClient;

// Replace this with your API key
$apiKey = 'your-api-qencode-key';
$drm_username = 'my.ezdrm@email.com';
$drm_password = 'your-ezdrm-password';

$params = '
{
  "query": {
    "format": [
      {
        "output": "advanced_dash",
        "stream": [
          {
            "video_codec": "libx264",
            "height": 360,
            "audio_bitrate": 128,
            "keyframe": 25,
            "bitrate": 950
          }
        ],
        "cenc_drm" : {cenc_drm}
      }
    ],
    "source": "https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4"
  }
}';

$q = new QencodeApiClient($apiKey, $url='https://api-qa.qencode.com');

try {

    $task = $q->createTask();
    log_message("Created task: ".$task->getTaskToken());

    $drm = new Drm($drm_username, $drm_password);
    $drm_params = $drm->cenc_drm();
    log_message("Drm: ".print_r($drm_params, true));

    $params = str_replace('{cenc_drm}', json_encode($drm_params['data']), $params);
    log_message("Query: ".print_r($params, true));

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