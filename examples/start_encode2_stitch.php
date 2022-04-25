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
// API key can be found in your account on https://cloud.qencode.com under Project settings
$apiKey = '5a5db6fa5b4c5';

$video1_url = 'https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4';
$video2_url = 'https://nyc3.digitaloceanspaces.com/qencode/manyvids/60_Tinder_hookup_BEFORE_MV_upload.mp';

$q = new QencodeApiClient($apiKey);

try {

    $task = $q->createTask();
    log_message("Created task: ".$task->getTaskToken());

    $task->AddStitchVideoItem($video1_url);
    $videoItem = $task->AddStitchVideoItem($video2_url);
    //set start time (in seconds) in input video to begin transcoding from
    $videoItem->start_time = 30.0;
    //duration of the video fragment (in seconds) to be transcoded
    $videoItem->duration = 10.0;

    $params = new CustomTranscodingParams();

    $format = new Format();

    $format->destination = new Destination();
    // Replace settings below with your destination settings
    $format->destination->url = "s3://s3-your-region.amazonaws.com/your-bucket/folder";
    $format->destination->key = "your-access-key";
    $format->destination->secret = "your-secret-key";
    $format->destination->permissions = "public-read";

    $format->segment_duration = 4;
    $format->output = "advanced_hls";

    $stream = new Stream();
    $stream->size = "1920x1080";
    $stream->audio_bitrate = 128;

    $format->stream = [$stream];
    $params->format = [$format];

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