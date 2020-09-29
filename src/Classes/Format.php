<?php

namespace Qencode\Classes;

class Format {

    /**
     * Output video format. Currently supported values are mp4, webm, advanced_hls, advanced_dash. Required.
     * @var string
     */
    public $output;

    /**
     * Output metadata version.
     * @var string
     */
    public $metadata_version;
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
     * @var int
     */
    public $segment_duration;

    /**
     * Contains a list of elements each describing a single view stream (e.g. for HLS format).
     * @var Stream
     */
    public $stream;

    /**
     * Enables/Disables per-title encoding mode. Defaults to 0.
     * @var int
     */
    public $optimize_bitrate;

    /**
     * Limits the lowest CRF (quality) for Per-Title Encoding mode to the specified value.
     * Possible values: from 0 to 51. Defaults to 0
     * @var int
     */
    public $min_crf;

    /**
     * Limits the highest CRF (quality) for Per-Title Encoding mode to the specified value.
     * Possible values: from 0 to 51. Defaults to 0
     * @var int
     */
    public $max_crf;

    /**
     * Adjusts best CRF predicted for each scene with the specified value in Per-Title Encoding mode.
     * Should be integer in range -10..10. Defaults to 0
     * @var int
     */
    public $adjust_crf;

}