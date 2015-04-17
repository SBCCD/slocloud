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
namespace SLOCloud\Model\Storage;

use Doctrine\ORM\EntityManager;
use SLOCloud\Tests\OrmTestCase;

class TestStorage extends OrmTestCase
{
    /** @var EntityManager */
    private $session = null;

    protected function setUp()
    {
        parent::setUp();
        $this->session = $this->createEntityManager();
    }

    /** @test */
    public function readSLO()
    {
        /** @var SimpleSLO $simple */
        $simple = $this->session->find('SLOCloud\Model\Storage\SimpleSLO', 1);
        /** @var RubricSLO $rubric */
        $rubric = $this->session->find('SLOCloud\Model\Storage\RubricSLO', 2);

        $this->assertInstanceOf('SLOCloud\Model\Storage\SimpleSLO', $simple);
        $this->assertCount(2, $simple->getStatements());
        $this->assertInstanceOf('SLOCloud\Model\Storage\SimpleStatement', $simple->getStatements()[0]);
        $this->assertInstanceOf('SLOCloud\Model\Storage\RubricSLO', $rubric);
        $this->assertCount(3, $rubric->getStatements());
        $this->assertInstanceOf('SLOCloud\Model\Storage\RubricStatement', $rubric->getStatements()[0]);
    }

    /** @test */
    public function writeNewSLO()
    {
        $simple = new SimpleSLO(
            new \DateTime('2015-05-20 00:00:00.000000', new \DateTimeZone("UTC")),
            "term",
            "subject",
            "class",
            "section",
            "method",
            "proposed",
            "N/A",
            "Not Used",
            "N/A",
            [
                new SimpleStatement(
                    "This is a new statement",
                    1,
                    2,
                    -1,
                    -1,
                    "Not Used",
                    "Not Used",
                    "Not Used",
                    "Not Used"
                ),
                new SimpleStatement(
                    "This is another new statement",
                    3,
                    4,
                    -1,
                    -1,
                    "Not Used",
                    "Not Used",
                    "Not Used",
                    "Not Used"
                )
            ]
        );

        $this->session->persist($simple);
        $this->session->flush();

        $this->assertEquals(3, $simple->getId());
        $this->assertEquals(6, $simple->getStatements()[0]->getId());
        $this->assertEquals(7, $simple->getStatements()[1]->getId());

        $this->checkAdditionalData([
            'unit_tests_slos' => ['id'],
            'unit_tests_statements' => ['id']
        ]);
    }

    /** @test */
    public function updateSLO()
    {
        /** @var SimpleSLO $slo */
        $slo = $this->session->find('SLOCloud\Model\Storage\SimpleSLO', 1);
        $slo->setMethod("This is the new method");
        $slo->setProposed("This is the new proposed");
        $slo->addStatement(new SimpleStatement(
            "This is a new statement",
            1,
            2,
            -1,
            -1,
            "Not Used",
            "Not Used",
            "Not Used",
            "Not Used"
        ));

        $this->session->flush();

        /** @var SimpleSLO $updatedSlo */
        $updatedSlo = $this->session->find('SLOCloud\Model\Storage\SimpleSLO', 1);

        $this->assertEquals("This is the new method", $updatedSlo->getMethod());
        $this->assertEquals("This is the new proposed", $updatedSlo->getProposed());
        $this->assertCount(3, $updatedSlo->getStatements());
        $this->assertEquals("This is a new statement", $updatedSlo->getStatements()[2]->getStatement());
    }

    /**
     * Check that the remaining data exactly equals the tables in the given file
     * @param string $changesFile file to compare against
     */
    protected function checkOnlyData($changesFile)
    {
        $expectedDataSet = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($changesFile);

        $conn = $this->getConnection();
        $tables = $expectedDataSet->getTableNames();
        foreach ($tables as $tableName) {
            $queryTable =  $conn->createQueryTable(
                $tableName,
                'SELECT * FROM [$tableName]'
            );
            $expectedTable = $expectedDataSet->getTable($tableName);

            $this->assertTablesEqual($queryTable, $expectedTable);
        }
    }

    /**
     * Check for data from new.yml file in addition to initial.yml file
     * @param string[] $includeTables associative array of "table to compare" => [fields to order by]
     */
    protected function checkAdditionalData($includeTables)
    {
        $initialData = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet("./tests/data/initial.yml");
        $initialData->addYamlFile("./tests/data/new.yml");

        $filteredExpectedSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($initialData);
        $filteredExpectedSet->addIncludeTables(array_keys($includeTables));

        $conn = $this->getConnection();
        foreach ($includeTables as $tableName => $order) {
            $expectedTable = $filteredExpectedSet->getTable($tableName);
            $queryTable =  $conn->createQueryTable(
                $tableName,
                "SELECT * FROM [$tableName] ORDER BY [".join('], [', $order)."]"
            );

            $this->assertTablesEqual($queryTable, $expectedTable);
        }
    }

    /**
     * (@inheritdoc)
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet("./tests/data/initial.yml");
    }
}
