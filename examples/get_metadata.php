<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeClientException;
use Qencode\Exceptions\QencodeException;
use Qencode\QencodeApiClient;
use Qencode\Classes\Metadata;

// Replace this with your API key
$apiKey = '1234567890123';
$video_url = 'https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4';

$q = new QencodeApiClient($apiKey);

try {
    log_message('Getting metadata for: '.$video_url.' ...');
    $video_info = $q->getMetadata($video_url);

    list($width, $height) = Metadata::get_video_dimensions($video_info);
    log_message('width, px: '.$width);
    log_message('height, px: '.$height);

    $bitrate = Metadata::get_bitrate($video_info);
    log_message('bitrate, b/s: '.$bitrate);

    $framerate = Metadata::get_framerate($video_info);
    log_message('framerate, fps: '.$framerate);

    $duration = Metadata::get_duration($video_info);
    log_message('duration, sec: '.$duration);

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