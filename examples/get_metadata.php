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
$video_url = 'https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4';

$q = new QencodeApiClient($apiKey);

try {

    $metdata = $q->getMetadata($video_url);

    log_message($metdata);

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

function log_message($json) {
    $msg = json_encode($json);
    echo $msg."\n";
}