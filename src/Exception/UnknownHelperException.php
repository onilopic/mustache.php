<?php

declare(strict_types=1);

namespace Mustache\Exception;

use Mustache\Contract\Exception;

/**
 * Unknown helper exception.
 */
class UnknownHelperException extends InvalidArgumentException implements Exception
{
    protected string $helperName;

    /**
     * @param string    $helperName
     * @param ?\Mustache\Contract\Exception $previous
     */
    public function __construct($helperName, \Exception $previous = null)
    {
        $this->helperName = $helperName;
        $message = sprintf('Unknown helper: %s', $helperName);
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, 0, $previous);
        } else {
            parent::__construct($message); // @codeCoverageIgnore
        }
    }

    public function getHelperName()
    {
        return $this->helperName;
    }
}
