<?php

namespace Qencode\Classes;


class StitchVideoItem
{
    /**
     * Source video URI. Can be http(s) url or tus uri
     * @var string
     */
    public $url;

    /**
     * Video clip start time
     */
    public $start_time;

    /**
     * Video clip duration
     */
    public $duration;

}