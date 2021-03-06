<?php
/**
 * ZF2-ExtendedFramework
 *
 * @link      https://github.com/brian978/ZF2-ExtendedFramework
 * @copyright Copyright (c) 2013
 * @license   Creative Commons Attribution-ShareAlike 3.0
 */

namespace Tests\TestHelpers\Traits;

use Zend\Db\Adapter\Adapter;

trait DatabaseCreator
{
    /**
     * @var string
     */
    protected static $sqlitePaths = 'module/Tests/database';

    /**
     * @var string
     */
    protected static $dbFile = '';

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected static $adapter;

    public static function setUpBeforeClass()
    {
        static::createDb();
    }

    public static function tearDownAfterClass()
    {
        static::destroyDb();
    }

    protected static function createDb()
    {
        if (empty(static::$dbFile)) {
            static::$dbFile = static::$sqlitePaths . '/database_mapper.sq3';
        }

        // Setting up the adapter
        static::$adapter = new Adapter(
            array(
                'driver' => 'Pdo_Sqlite',
                'database' => static::$dbFile
            )
        );

        // Creating the first schema
        static::$adapter->query(
            file_get_contents(static::$sqlitePaths . '/schema.sqlite.sql'),
            Adapter::QUERY_MODE_EXECUTE
        );

        // Creating the second schema
        static::$adapter->query(
            file_get_contents(static::$sqlitePaths . '/schema2.sqlite.sql'),
            Adapter::QUERY_MODE_EXECUTE
        );

        // Inserting the data line by line
        $handle = @fopen(static::$sqlitePaths . '/data.sqlite.sql', "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                static::$adapter->query($buffer, Adapter::QUERY_MODE_EXECUTE);
            }
            fclose($handle);
        }

    }

    protected static function destroyDb()
    {
        // Removing the test database (connection to db needs to be closed)
        if (static::$adapter !== null) {
            $connection = static::$adapter->getDriver()->getConnection();
            while ($connection->isConnected() == true) {
                $connection->disconnect();
            }
        }

        $tried   = 0;
        $removed = false;
        while ($removed == false) {
            $tried++;
            $removed = unlink(static::$dbFile);

            if ($tried >= 5) {
                break;
            }
        }
    }
}
