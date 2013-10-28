<?php
/**
 * ZF2-ExtendedFramework
 *
 * @link      https://github.com/brian978/ZF2-ExtendedFramework
 * @copyright Copyright (c) 2013
 * @license   Creative Commons Attribution-ShareAlike 3.0
 */

namespace LibraryTests\Model\Db;

use Library\Model\Db\TableGateway;
use Tests\TestHelpers\AbstractTest;
use Tests\TestHelpers\Traits\DatabaseCreator;
use Zend\EventManager\Event;

class TableGatewayTest extends AbstractTest
{
    use DatabaseCreator;

    public function testCanFindByIdWithoutAMapper()
    {
        $table        = new TableGateway(self::$adapter, 'test');
        $entityObject = $table->findById(2);

        $this->assertEquals(2, $entityObject['id']);

        return $table;
    }

    protected function _removeRecursive($dir)
    {
        if (file_exists($dir)) {
            $dirIt = new \DirectoryIterator($dir);
            foreach ($dirIt as $entry) {
                $fname = $entry->getFilename();
                if ($fname == '.' || $fname == '..') {
                    continue;
                }

                if ($entry->isFile()) {
                    unlink($entry->getPathname());
                } else {
                    $this->_removeRecursive($entry->getPathname());
                }
            }

            rmdir($dir);
        }
    }

    /**
     * @depends testCanFindByIdWithoutAMapper
     *
     * @param \Library\Model\Db\TableGateway $tableMock
     */
    public function testGatewayCanUseAttachedMapper($tableMock)
    {
        /** @var $mapperMock \Library\Model\Mapper\AbstractMapper */
        $mapperMock = $this->getMockBuilder('\Library\Model\Mapper\AbstractMapper')
            ->getMockForAbstractClass();

        // Updating the map in the mapper
        $mapperMock->setEntityClass('\Tests\TestHelpers\Model\Entity\MockEntity')
            ->setMap(array('default' => array('id' => 'id', 'field1' => 'testField1')));

        $tableMock->setMapper($mapperMock);

        $entityObject = $tableMock->findById(2);

        $this->assertEquals(2, $entityObject->getId());
    }

    public function testGatewayCanReturnPaginator()
    {
        $tableMock = new TableGateway(self::$adapter, 'test');

        /** @var $mapperMock \Library\Model\Mapper\AbstractMapper */
        $mapperMock = $this->getMockBuilder('\Library\Model\Mapper\AbstractMapper')
            ->getMockForAbstractClass();

        // Updating the map in the mapper
        $mapperMock->setEntityClass('\Tests\TestHelpers\Model\Entity\MockEntity')
            ->setMap(array('test' => array('id' => 'id', 'field1' => 'testField1')));

        $tableMock->setMapper($mapperMock);

        /** @var $object \Library\Model\Db\ResultProcessor */
        $object = $tableMock->fetch();

        // Changing the map in the paginator
        $object->getEventManager()->attach(
            'changePaginatorMap',
            function (Event $e) use ($tableMock) {
                if ($e->getTarget()->getProcessor()->getDataSource() === $tableMock) {
                    $params = $e->getParams();
                    $map    = & $params['map'];
                    $map    = 'test';
                }
            }
        );

        $paginator = $object->getPaginator()
            ->setItemCountPerPage(1)
            ->setCurrentPageNumber(1);

        /** @var $currentItems \Zend\Db\ResultSet\ResultSet */
        $currentItems = $paginator->getCurrentItems();
        $currentItem  = $currentItems->current();

        $this->assertInstanceOf('\Zend\Paginator\Paginator', $paginator);
        $this->assertInstanceOf('\Tests\TestHelpers\Model\Entity\MockEntity', $currentItem);
        $this->assertNotEquals(0, $currentItem->getId());
        $this->assertEquals(1, $currentItems->count());
    }

    /**
     * @expectedException PHPUnit_Framework_SkippedTestError
     */
    public function testGatewayCanReturnResultSetAndCacheResult()
    {
        $this->markTestSkipped('Cache must be redone');

        // Cleaning up the files first
        if (is_dir('module/Tests/caches')) {
            $this->_removeRecursive('module/Tests/caches');
        }

        mkdir('module/Tests/caches');

        /** @var $cache \Zend\Cache\Pattern\ObjectCache */
        $cache = $this->serviceManager->get('Zend\Cache');
        $table = new TableGateway(self::$adapter, 'test');
        $table->setCache($cache);

        /** @var $mapperMock \Library\Model\Mapper\AbstractMapper */
        $mapperMock = $this->getMockBuilder('\Library\Model\Mapper\AbstractMapper')
            ->getMockForAbstractClass();

        // Updating the map in the mapper
        $mapperMock->setEntityClass('\Tests\TestHelpers\Model\Entity\MockEntity')
            ->setMap(array('id' => 'id', 'field1' => 'testField1'));

        $this->assertInstanceOf('\Zend\Db\ResultSet\ResultSet', $table->fetch()->cache()->getResultSet());
    }

    /**
     * @expectedException PHPUnit_Framework_SkippedTestError
     */
    public function testGatewayCanReturnCachedResultSet()
    {
        $this->markTestSkipped('Cache must be redone');

        /** @var $cache \Zend\Cache\Pattern\ObjectCache */
        $cache = $this->serviceManager->get('Zend\Cache');
        $table = new TableGateway(self::$adapter, 'test');
        $table->setCache($cache);

        /** @var $mapperMock \Library\Model\Mapper\AbstractMapper */
        $mapperMock = $this->getMockBuilder('\Library\Model\Mapper\AbstractMapper')
            ->getMockForAbstractClass();

        // Updating the map in the mapper
        $mapperMock->setEntityClass('\Tests\TestHelpers\Model\Entity\MockEntity')
            ->setMap(array('id' => 'id', 'field1' => 'testField1'));

        /** @var $object \Zend\Db\ResultSet\ResultSet */
        $object = $table->fetch()->cache()->getResultSet();

        $this->assertInstanceOf('\Zend\Db\ResultSet\ResultSet', $object);
        $this->assertEquals(2, $object->count());

        $this->_removeRecursive('module/Tests/caches');
    }
}
