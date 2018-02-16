<?php

namespace Qencode\Classes;

class Format {
    /**
     * Output video format. Currently supported values are mp4, webm, advanced_hls, advanced_dash. Required.
     * @var string
     */
    public $output;

    /**
     * Output video file extension (for MP4 - defaults to '.mp4', for WEBM - defaults to '.webm').
     * @var string
     */
    public $file_extension;

    /**
     * URI to store output video in. In case this value is not specified, video is temporarily stored on Qencode servers.
     * @var Destination
     */
    public $destination;

    /**
     * Segment duration to split media (in seconds). Defaults to 8.
     * @var string
     */
    public $segment_duration;

    /**
     * Contains a list of elements each describing a single view stream (e.g. for HLS format).
     * @var string
     */
    public $stream;
}