<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeException;
use Qencode\QencodeApiClient;

// Replace this with your API key
// API key and params below, such as transcoding profile id and transfer method id
// can be found in your account on https://cloud.qencode.com under Project settings
$apiKey = '5a5db6fa5b4c5';
$transcodingProfileId = '5a5db6fa5b8ac,5a5db6fa5c263';
$transferMethodId = 'abcdefgh';

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

    $task->start($transcodingProfileId, null, $transferMethodId);

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