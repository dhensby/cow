<?php

namespace SilverStripe\Cow\Tests\Model\Modules;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use SilverStripe\Cow\Model\Modules\Library;
use SilverStripe\Cow\Model\Release\LibraryRelease;
use SilverStripe\Cow\Model\Release\Version;

class LibraryTest extends PHPUnit_Framework_TestCase
{
    public function testSerialisePlanSavesPriorVersion()
    {
        /** @var Library|PHPUnit_Framework_MockObject_MockObject $library */
        $library = $this->getMockBuilder(Library::class)
            ->disableOriginalConstructor()
            ->setMethods(['getComposerData'])
            ->getMock();

        $library->expects($this->any())->method('getComposerData')->willReturn([
            'name' => 'testrepo',
        ]);

        $version = new Version('1.2.3');
        $priorVersion = new Version('1.1.0');
        $plan = new LibraryRelease($library, $version, $priorVersion);

        $result = $library->serialisePlan($plan);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('testrepo', $result);
        $this->assertSame('1.1.0', $result['testrepo']['PriorVersion']);
    }
}
