<?php


namespace Tests\Ip;


use PhpUnit\Helper\TestEnvironment;

class PageTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        TestEnvironment::setupCode();
    }

    public function testIpformatBytes()
    {
        $answer = ipFormatBytes('100', 'test');
        $this->assertEquals('100 B', $answer);

        $answer = ipFormatBytes('1200', 'test');
        $this->assertEquals('1 KB', $answer);

        $answer = ipFormatBytes('1600', 'test');
        $this->assertEquals('2 KB', $answer);

        $answer = ipFormatBytes('1500', 'test', 1);
        $this->assertEquals('1 KB', $answer);  //kilobytes don't use precision

        $answer = ipFormatBytes('1500000', 'test', 1);
        $this->assertEquals('1,4 MB', $answer);  //megabytes uses precision

        $answer = ipFormatBytes('1600000000', 'test');
        $this->assertEquals('1 GB', $answer);  //rounded

    }


    public function testIpFormatPrice()
    {
        $answer = ipFormatPrice(1000, 'USD', 'test');
        $this->assertEquals('$10.00', $answer);

    }

    public function testIpFormatDate()
    {
        $answer = ipFormatDate(1401190316, 'test');
        $this->assertEquals('5/27/14', $answer);

    }

    public function testIpFormatTime()
    {
        $answer = ipFormatTime(1401190316, 'test');
        $this->assertEquals('2:31 PM', $answer);

    }

    public function testIpFormatDateTime()
    {
        $answer = ipFormatDateTime(1401190316, 'test');
        $this->assertEquals('5/27/14 2:31 PM', $answer);

    }


}
