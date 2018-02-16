<?php

namespace Qencode\Classes;

class Destination {
    /**
     * Destination bucket url, e.g. s3://example.com/bucket
     * @var string
     */
    public $url;

    /**
     * Access key
     * @var string
     */
    public $key;

    /**
     * Access secret
     * @var string
     */
    public $secret;
}