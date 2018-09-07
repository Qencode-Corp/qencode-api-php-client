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
$apiKey = 'abcdefgh';
$video_url = 'https://qa.qencode.com/static/1.mp4';

$q = new QencodeApiClient($apiKey);

try {

    $task = $q->createTask();
    log_message("Created task: ".$task->getTaskToken());

    $params = new CustomTranscodingParams();
    $params->source = $video_url;

    $format = new Format();
    $format->destination = new Destination();
    $format->destination->url = "s3://s3-your-region.amazonaws.com/your-bucket/folder";
    $format->destination->key = "your-access-key";
    $format->destination->secret = "your-secret-key";
    $format->destination->permissions = "public-read";
    $format->destination->storage_class = "REDUCED_REDUNDANCY";
    $format->segment_duration = 4;
    $format->output = "advanced_hls";

    $stream = new Stream();
    $stream->size = "1920x1080";
    $stream->audio_bitrate = 128;
    $vcodec_params = new Libx264_VideoCodecParameters();
    $vcodec_params->vprofile = "baseline";
    $vcodec_params->level = 31;
    $vcodec_params->coder = 0;
    $vcodec_params->flags2 = "-bpyramid+fastpskip-dct8x8";
    $vcodec_params->partitions = "+parti8x8+parti4x4+partp8x8+partb8x8";
    $vcodec_params->directpred = 2;

    $stream->video_codec_parameters = $vcodec_params;

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