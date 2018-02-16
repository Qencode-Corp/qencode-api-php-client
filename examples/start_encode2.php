<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeException;
use Qencode\Classes\CustomTranscodingParams;
use Qencode\Classes\Format;
use Qencode\Classes\Stream;
use Qencode\Classes\Destination;
use Qencode\Classes\Libx264_VideoCodecParameters;
use Qencode\QencodeApiClient;

// Replace this with your API key
$apiKey = '5a5db6fa5b4c5';

$transcodingProfileId = '5a5db6fa5b8ac';

$video_url = 'https://qa.stagevids.com/static/1.mp4';

$q = new QencodeApiClient($apiKey, 'https://api-qa.qencode.com');

try {

    $task = $q->createTask();
    $params = new CustomTranscodingParams();
    $params->source = $video_url;

    $format = new Format();
    $format->destination = new Destination();
    $format->destination->url = "s3://s3-eu-west-2.amazonaws.com/qencode-test";
    $format->destination->key = "AKIAIKZIPSJ7SDAIWK4A";
    $format->destination->secret = "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";
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
        print_r($response);
        echo "<BR><BR>";
    } while ($response['status'] != 'completed');

    foreach ($response['videos'] as $video) {
        echo $video['user_tag'] . ': ' . $video['url'].'<BR>';
    }
    echo "DONE!";


} catch (QencodeApiException $e) {
    // API response status code was not successful
    echo 'Qencode API Exception: ' . $e->getCode() . ' ' . $e->getMessage();
} catch (QencodeException $e) {
    // API call failed
    echo 'Qencode Exception: ' . $e->getMessage();
    var_export($q->getLastResponseRaw());
}
