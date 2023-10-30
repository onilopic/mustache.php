<?php

namespace Mustache\Tests\Logger;

/**
 * @group unit
 */
class AbstractLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testEverything()
    {
        $logger = new \Mustache\Test\Logger\TestLogger();

        $logger->emergency('emergency message');
        $logger->alert('alert message');
        $logger->critical('critical message');
        $logger->error('error message');
        $logger->warning('warning message');
        $logger->notice('notice message');
        $logger->info('info message');
        $logger->debug('debug message');

        $expected = array(
            array(\Mustache\Logger::EMERGENCY, 'emergency message', array()),
            array(\Mustache\Logger::ALERT, 'alert message', array()),
            array(\Mustache\Logger::CRITICAL, 'critical message', array()),
            array(\Mustache\Logger::ERROR, 'error message', array()),
            array(\Mustache\Logger::WARNING, 'warning message', array()),
            array(\Mustache\Logger::NOTICE, 'notice message', array()),
            array(\Mustache\Logger::INFO, 'info message', array()),
            array(\Mustache\Logger::DEBUG, 'debug message', array()),
        );

        $this->assertEquals($expected, $logger->log);
    }
}


