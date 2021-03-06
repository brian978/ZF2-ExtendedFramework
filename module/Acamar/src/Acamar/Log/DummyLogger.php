<?php
/**
 * ZF2-ExtendedFramework
 *
 * @link      https://github.com/brian978/ZF2-ExtendedFramework
 * @copyright Copyright (c) 2013
 * @license   Creative Commons Attribution-ShareAlike 3.0
 */

namespace Acamar\Log;

use Zend\Log\Logger as ZendLogger;

/**
 * Class Logger
 *
 * This is just a dummy logger that the models will use by default
 *
 * @package Acamar\Log
 */
class DummyLogger extends ZendLogger
{
    /**
     * Dummy version of the log method
     *
     * @param int   $priority
     * @param mixed $message
     * @param array $extra
     * @return $this|ZendLogger
     */
    public function log($priority, $message, $extra = array())
    {
        unset($priority, $message, $extra);

        return $this;
    }
}
