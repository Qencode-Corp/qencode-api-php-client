<?php
/**
 * Receives a callback from Qencode when a job is completed.
 * Prints out job status and output video URL(s)
 */

$logfile = date('Y-m-d_H-i-s').'.log';
$job_info = json_decode($_POST['status']);
$log = "Job status: {$job_info->status}\n";
if ($job_info->status == 'completed') {
    foreach ($job_info->videos as $video) {
        $log .= "URL: {$video->url}\n";
    }

}
print $log;
file_put_contents($logfile, $log);
