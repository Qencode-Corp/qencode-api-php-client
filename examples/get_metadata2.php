<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeClientException;
use Qencode\Exceptions\QencodeException;
use Qencode\QencodeApiClient;
use Qencode\Classes\Metadata;

// Replace this with your API key
$apiKey = '5a2a846a26ace';
$video_url = 'https://sinpartyapi.com/storage/bd6c4463-8c01-42fc-b86f-c19369565ae9-64663ea162a716.638146551684422305.mov';

$url = 'https://prod-europe-west1-d-1-api-gcp.qencode.com/';
//$url = 'https://prod-us-central1-a-1-api-gcp.qencode.com/';

$q = new QencodeApiClient($apiKey, $url);

try {
    //log_message('Getting metadata for: '.$video_url.' ...');
    $video_info = $q->getMetadata($video_url);
    echo "\nVideo info";
    list($width, $height) = Metadata::get_video_dimensions($video_info);
    log_message('width, px: '.$width);
    log_message('height, px: '.$height);

    $bitrate = Metadata::get_bitrate($video_info);
    log_message('bitrate, b/s: '.$bitrate);

    $framerate = Metadata::get_framerate($video_info);
    log_message('framerate, fps: '.$framerate);

    $duration = Metadata::get_duration($video_info);
    log_message('duration, sec: '.$duration);
    //echo "DONE!";

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