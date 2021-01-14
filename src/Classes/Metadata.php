<?php

namespace Qencode\Classes;

use Qencode\Exceptions\QencodeClientException;

class Metadata extends TranscodingTask {
    function __construct($api, $task_token)
    {
        parent::__construct($api, $task_token);
    }
    /**
     * Gets Metadata vidoe
     * @return object
     */
    public function get($video_url) {
        $params = new CustomTranscodingParams();
        $format = new Format();
        $format->output = "metadata";
        $format->metadata_version = "4.1.5";
        $params->source = $video_url;
        $params->format = [$format];
        parent::startCustom($params);
        do {
            sleep(5);
            $response =parent::getStatus();
        } while ($response['status'] != 'completed');

        $urlMet = '';
        foreach ($response['videos'] as $video) {
            $urlMet = $video['url'];
        }
        $response = $this->api->get($urlMet);
        return $response;
    }

    public static function get_a_stream_by_codec_type($video_info, $codec_type) {
        if (! array_key_exists('streams', $video_info)) {
            return null;
        }
        foreach ($video_info['streams'] as $stream) {
            if ($stream['codec_type'] == $codec_type) {
                return $stream;
            }
        }
        return null;
    }

    public static function get_video_stream_info($video_info) {
        return self::get_a_stream_by_codec_type($video_info, 'video');
    }

    public static function get_audio_stream_info($video_info) {
        return self::get_a_stream_by_codec_type($video_info, 'audio');
    }

    public static function get_video_dimensions($video_info) {
        $video_stream = self::get_video_stream_info($video_info);
        if (! $video_stream)
            return null;
        return [$video_stream['width'], $video_stream['height']];
    }

    public static function get_bitrate($video_info, $stream_type = 'video') {
        $stream = null;
        if ($stream_type == 'video') {
            $stream = self::get_video_stream_info($video_info);
        }
        if ($stream_type == 'audio') {
            $stream = self::get_audio_stream_info($video_info);
        }
        if (is_array($stream) and array_key_exists('bit_rate', $stream)) {
            $bitrate = $stream['bit_rate'];
        }
        else {
            $bitrate = null;
        }
        return $bitrate;
    }

    public static function get_framerate($video_info) {
        $stream = self::get_video_stream_info($video_info);
        $avg_frame_rate = $stream['avg_frame_rate'];
        if (strpos($avg_frame_rate, '/') !== FALSE) {
            $buf = explode('/', $avg_frame_rate);
            $framerate = floatval($buf[0]) / floatval($buf[1]);
        }
        else {
            $framerate = floatval($avg_frame_rate);
        }
        return round($framerate, 2);
    }

    public static function get_duration($video_info) {
        return $video_info['format']['duration'];
    }

}