<?php

namespace Qencode\Exceptions;

use Exception;

/**
 * Generic API exception
 */
class QencodeException extends Exception
{
    /**
     * Last response from API that triggered this exception
     *
     * @var string
     */
    public $rawResponse;
}