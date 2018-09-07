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

    /**
     * Permission settings for S3
     * For the list of available permissions see: https://docs.aws.amazon.com/AmazonS3/latest/dev/acl-overview.html#permissions
     * @var string
     */
    public $permissions;

    /**
     * S3 storage class
     * Use REDUCED_REDUNDANCY to reduce storage redundancy
     * @var string
     */
    public $storage_class;
}