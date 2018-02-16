<?php
require_once __DIR__ . '/../autoload.php';

use Qencode\Exceptions\QencodeApiException;
use Qencode\Exceptions\QencodeException;
use Qencode\QencodeApiClient;

// Replace this with your API key
$apiKey = '5a5db6fa5b4c5';

$transcodingProfileId = '5a5db6fa5b8ac';

$video_url = 'https://qa.stagevids.com/static/1.mp4';

$q = new QencodeApiClient($apiKey, 'https://api-qa.qencode.com');

try {

    $task = $q->createTask();
    $task->start($transcodingProfileId, $video_url);

    do {
        sleep(5);
        $response = $task->getStatus();
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
    var_export($pf->getLastResponseRaw());
}
