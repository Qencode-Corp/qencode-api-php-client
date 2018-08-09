<?php

namespace Qencode\Classes;

class CustomTranscodingParams {
    /**
     * Source video URI. Can be http(s) url or tus uri
     * @var string
     */
    public $source;

    /**
     * A list of objects, each describing params for a single output video stream (MP4, WEBM, HLS or MPEG-DASH).
     * @var Format
     */
    public $format;
}