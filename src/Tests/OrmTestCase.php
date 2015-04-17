<?php /*

SLO Cloud - A Cloud-Based SLO Reporting Tool for Higher Education

This is a peer-reviewed, open-source, public project made possible by the Open Innovation in Higher Education project.

Copyright (C) 2015 Jesse Lawson, San Bernardino Community College District

Contributors:
Jesse Lawson
Jason Brady

THIS PROJECT IS LICENSED UNDER GPLv2. YOU MAY COPY, DISTRIBUTE AND MODIFY THE SOFTWARE AS LONG AS YOU TRACK
CHANGES/DATES OF IN SOURCE FILES AND KEEP ALL MODIFICATIONS UNDER GPL. YOU CAN DISTRIBUTE YOUR APPLICATION USING A
GPL LIBRARY COMMERCIALLY, BUT YOU MUST ALSO DISCLOSE THE SOURCE CODE.

GNU General Public License Version 2 Disclaimer:

---

This file is part of SLO Cloud

SLO Cloud is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later.

SLO Cloud is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free
Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or
visit http://opensource.org/licenses/GPL-2.0

---

*/

namespace SLOCloud\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use SLOCloud\Application;
use SLOCloud\Config;

/**
 * Class OrmTestCase
 * @package SBCCD\Tests
 */
abstract class OrmTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /** @var EntityManager */
    protected static $entityManager = null;
    protected static $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    /** @var PHPUnit_Extensions_Database_DB_IDatabaseConnection */
    private $conn = null;

    public static function setUpBeforeClass()
    {
        $config = new Config('config');
        $config->loadIni('cli.ini');
        $config->loadIni('unit-tests.ini');
        $dbConfig = $config->value('db');

        self::$pdo = new \PDO(self::buildDSN($dbConfig, $dbConfig['user'], $dbConfig['password']));
        $dbConfig['pdo'] = self::$pdo;
        self::$entityManager = Application::createEntityManager($dbConfig, null);
        self::dropCreateDatabase();
    }

    public static function tearDownAfterClass()
    {
        self::$entityManager = null;
    }

    /**
     * (@inheritdoc)
     */
    protected function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Composite([
            new \PHPUnit_Extensions_Database_Operation_DeleteAll(),
            new IdentityInsertForSqlServer()
        ]);
    }

    /**
     * @return EntityManager
     */
    protected function createEntityManager()
    {
        return self::$entityManager;
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    final protected function getConnection()
    {
        if ($this->conn === null) {
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }
        return $this->conn;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \ErrorException
     */
    private static function dropCreateDatabase()
    {
        $em = self::$entityManager;
        $connection = $em->getConnection();

        self::toggleForeignKeyConstraints($connection, false);

        $schemaTool = new SchemaTool($em);

        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);

        self::toggleForeignKeyConstraints($connection, true);
    }

    /**
     * @param Connection $conn
     * @param boolean $on
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ErrorException
     */
    private static function toggleForeignKeyConstraints(Connection $conn, $on)
    {
        if ($conn->getParams()['driver'] === 'pdo_sqlite') {
            $needToChange = $on? '0': '1';
            $result = $conn->executeQuery('PRAGMA foreign_keys')->fetchAll()[0]['foreign_keys'];
            if ($result === $needToChange) {
                $conn->executeQuery('PRAGMA foreign_keys = '.($result === '0'? 'ON': 'OFF'))->fetchAll();
                if ($conn->executeQuery('PRAGMA foreign_keys')->fetchAll()[0]['foreign_keys'] === $result) {
                    throw new \ErrorException("Failed to ".($on?"enable":"disable")." foreign key support");
                }
            }
        }

        if ($conn->getParams()['driver'] === 'pdo_sqlsrv') {
            $result = $conn->executeQuery('select * from sys.foreign_keys where is_disabled=1')->fetchAll();
            $possible = $conn->executeQuery('select * from sys.foreign_keys')->fetchAll();
            if ($on && count($result) > 0) {
                $conn->executeQuery('exec sp_msforeachtable "ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all"');
                $result = $conn->executeQuery('select * from sys.foreign_keys where is_disabled=1')->fetchAll();
                if (count($result) > 0) {
                    throw new \ErrorException("Failed to enable foreign key constraints");
                }
            } elseif (!$on && count($result) === 0 && count($possible) !== 0) {
                $conn->executeQuery('EXEC sp_msforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all";');
                $result = $conn->executeQuery('select * from sys.foreign_keys where is_disabled=1')->fetchAll();
                if (count($result) === 0) {
                    throw new \ErrorException("Failed to disable foreign key constraints");
                }
            }
        }
    }

    private static function buildDSN(array $config) {
        switch($config['driver']) {
            case "pdo_sqlsrv":
                $dsn = "sqlsrv:";
                $dsn .= "Server=".$config['host'];
                if (array_key_exists('port',$config) && $config['port'] !== null) {
                    $dsn .= ",".$config['port'];
                }
                $dsn .= ";Database=".$config['dbname'];
                break;
            default:
                throw new \Exception("Unsupported driver '".$config['driver']."', don't know how to make dsn");

        }
        return $dsn;
    }
}
