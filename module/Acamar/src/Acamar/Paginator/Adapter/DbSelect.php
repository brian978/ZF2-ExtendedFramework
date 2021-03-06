<?php
/**
 * lida_cleaning
 *
 * @link      https://github.com/brian978/lida_cleaning
 * @copyright Copyright (c) 2013
 * @license   Creative Commons Attribution-ShareAlike 3.0
 */

namespace Acamar\Paginator\Adapter;

use Acamar\Model\Db\ResultProcessor;
use Acamar\Model\Mapper\Map;
use \Zend\Paginator\Adapter\DbSelect as ZendDbSelect;

class DbSelect extends ZendDbSelect
{
    /**
     * @var ResultProcessor
     */
    protected $processor;

    /**
     * @param \Acamar\Model\Db\ResultProcessor $processor
     * @return DbSelect
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * @return \Acamar\Model\Db\ResultProcessor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $select = clone $this->select;
        $select->offset($offset);
        $select->limit($itemCountPerPage);

        if ($this->processor !== null) {
            $map = new Map('default');

            // Shallow copy only (we need to rest of the objects to remain the same
            $processor = clone $this->processor;
            $processor->setSelect($select);

            // Updating the map
            $this->processor->getEventManager()->trigger(
                ResultProcessor::EVENT_CHANGE_MAP,
                $this->processor,
                array($map)
            );

            // Getting the result set
            $resultSet = $processor->getResultSet($map, $select);
        } else {
            $statement = $this->sql->prepareStatementForSqlObject($select);
            $result    = $statement->execute();

            $resultSet = clone $this->resultSetPrototype;
            $resultSet->initialize($result);
        }

        return $resultSet;
    }
}
