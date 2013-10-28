<?php
/**
 * ZF2-ExtendedFramework
 *
 * @link      https://github.com/brian978/ZF2-ExtendedFramework
 * @copyright Copyright (c) 2013
 * @license   Creative Commons Attribution-ShareAlike 3.0
 */

namespace LibraryTests\Model\Mapper\Db;

use Library\Model\Db\TableGateway;
use Tests\TestHelpers\AbstractTest;
use Tests\TestHelpers\Model\Entity\MockEntity;
use Tests\TestHelpers\Model\Mapper\Db\DbMockMapper;
use Tests\TestHelpers\Model\Mapper\Db\DbMockMapper2;
use Tests\TestHelpers\Model\Mapper\Db\DbMockMapper3;
use Tests\TestHelpers\Traits\DatabaseCreator;
use Zend\Db\Adapter\Adapter;

/**
 * Class DbMapperTest
 *
 * @skippedTests
 * @package LibraryTests\Model\Mapper\Db
 */
class DbMapperTest extends AbstractTest
{
    use DatabaseCreator;

    /**
     * @expectedException PHPUnit_Framework_SkippedTestError
     */
    public function testCanJoinTablesAndMapObjects()
    {
        $this->markTestSkipped('Design change');

        $testTableMock = new TableGateway(self::$adapter, 'test');

        $baseMapper = new DbMockMapper($testTableMock);
        $mapper2    = new DbMockMapper2(clone $testTableMock);

        $baseMapper->attachMapper($mapper2);
        $mapper2->attachMapper(new DbMockMapper3(clone $testTableMock));

        /** @var $object MockEntity */
        $object = $testTableMock->findById(1);

        $this->assertInstanceOf('\Tests\TestHelpers\Model\Entity\MockEntity', $object);
        $this->assertInstanceOf('\Tests\TestHelpers\Model\Entity\MockEntity', $object->getTestField2());
    }
}
