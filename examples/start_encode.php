<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeException;
use Qencode\QencodeApiClient;

// Replace this with your API key
// API key and params below, such as transcoding profile id and transfer method id
// can be found in your account on https://cloud.qencode.com under Project settings
$apiKey = 'abcdefgh';
$transcodingProfileId = 'abcdefgh';
$transferMethodId = 'abcdefgh';

//$video_url = 'https://qa.qencode.com/static/bbb_sunflower_1080p_60fps_normal_339mb.mp4';
$video_url = 'https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4';

$q = new QencodeApiClient($apiKey);

try {

    $task = $q->createTask();  // /v1/create_task
    log_message("Created task: ".$task->getTaskToken());

    //set start time (in seconds) in input video to begin transcoding from
    $task->start_time = 30.0;
    //duration of the video fragment (in seconds) to be transcoded
    $task->duration = 10.0;

    //Setting output file name with a custom output path variable.
    //This refers to Output path values specified in transcoding profile for video or image settings
    //See https://portal.qencode.com/docs for more examples
    $task->output_path_variables->filename = 'qencode_test';

    $task->start($transcodingProfileId, $video_url, $transferMethodId);

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