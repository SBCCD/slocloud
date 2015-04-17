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
namespace SLOCloud\Model\Service;

use Doctrine\ORM\EntityManager;
use SLOCloud\Model\Storage\SimpleSLO;
use SLOCloud\Model\Storage\SLO;
use SLOCloud\Tests\OrmTestCase;

class TestRubricSLOService extends OrmTestCase
{
    /** @var EntityManager */
    private $session = null;

    private $importFile = "tests/data/Rubric.csv";

    protected function setUp()
    {
        parent::setUp();
        $this->session = $this->createEntityManager();
    }

    /** @test */
    public function submitValidSLO()
    {
        $service = new RubricSLOService($this->session, []);

        /** @var SimpleSLO $SLO */
        $SLO = $service->submit([
            'term' => 'term',
            'subject' => 'subject',
            'class' => 'class',
            'section' => 'section',
            'proposed' => 'proposed',
            'slo1-statement' => 'statement1',
            'slo1-rubric-1' => '25',
            'slo1-rubric-2' => '20',
            'slo1-rubric-3' => '15',
            'slo1-rubric-4' => '10',
            'slo1-met' => 'Yes',
            'slo1-plo' => 'PLO#2',
            'slo1-geo' => 'GEO#1',
            'slo1-ilo' => 'ILO#3',
            'slo2-statement' => 'statement2',
            'slo2-rubric-1' => '20',
            'slo2-rubric-2' => '15',
            'slo2-rubric-3' => '10',
            'slo2-rubric-4' => '5',
            'slo2-met' => 'No',
            'slo2-plo' => 'PLO#1',
            'slo2-geo' => 'GEO#3',
            'slo2-ilo' => 'ILO#2',
        ]);

        $this->assertTrue($SLO !== false, "Submit failed: ".join('\n', $service->validationErrors));
        $this->assertInstanceOf('SLOCloud\Model\Storage\RubricSLO', $SLO);
        $this->assertEquals(3, $SLO->getId());
        $this->assertCount(2, $SLO->getStatements());
        $this->assertInstanceOf('SLOCloud\Model\Storage\RubricStatement', $SLO->getStatements()[0]);
    }

    /** @test */
    public function getSLOById()
    {
        $service = new RubricSLOService($this->session, []);
        $SLO = $service->getSLOById(2);

        $this->assertEquals("class", $SLO->getClass());
    }

    /** @test */
    public function getAllSLOs()
    {
        $service = new RubricSLOService($this->session, []);
        $SLOs = $service->getSLOs();

        $this->assertCount(1, $SLOs);
    }

    /** @test */
    public function reset()
    {
        $service = new RubricSLOService($this->session, []);
        $before = $service->getSLOs();
        $this->assertCount(1, $before);

        $service->reset();
        $after = $service->getSLOs();
        $this->assertCount(0, $after);
    }

    /**
     * @test
     * @depends reset
     */
    public function importExportData()
    {
        $service = new RubricSLOService($this->session, []);

        $service->reset();
        $count = $service->import(getCsvWithHeader($this->importFile));
        $SLOs = $service->getSLOs();

        $rows = $service->export($SLOs);
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = str_putcsv($row);
        }
        $lines[] = "";
        file_put_contents("tests/var/RubricExport.csv", iconv('utf-8', "windows-1252", implode(PHP_EOL, $lines)));

        $this->assertEquals(20, $count);
        $this->assertCount(20, $SLOs);
        $this->assertFileEquals($this->importFile, "tests/var/RubricExport.csv");
    }

    /**
     * @test
     * @depends importExportData
     */
    public function selectAllSLOs()
    {
        $service = $this->getServiceWithImport();
        $SLOs = $service->getSLOs([], false);

        $this->assertCount(20, $SLOs);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function selectSLOsByTerm()
    {
        $service = $this->getServiceWithImport();
        $spring = $service->getSLOs(['2014SP'], false);
        $springAndSummer = $service->getSLOs(['2014SP', '2014SM'], false);

        $this->assertCount(4, $spring);
        $this->assertCount(5, $springAndSummer);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function selectLatestSLOs()
    {
        $service = $this->getServiceWithImport();
        $SLOs = $service->getSLOs(['2014FA'], true);

        $this->assertCount(14, $SLOs);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function selectSLOsByTermAndClass()
    {
        $service = $this->getServiceWithImport();
        $SLOs = $service->getSLOs(['2014FA'], false, "KIN/F-168A");

        $this->assertCount(3, $SLOs);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function selectSLOsBySubject()
    {
        $service = $this->getServiceWithImport();
        $SLOs = $service->getSLOs(null, true, null, "KIN/F");

        $this->assertCount(5, $SLOs);
    }


    /**
     * @test
     * @depends importExportData
     */
    public function getTermsOnRecord()
    {
        $service = $this->getServiceWithImport();
        $terms = $service->getTermsOnRecord();

        $this->assertEquals(['2014FA', '2014SM', '2014SP'], $terms);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function getLastUpdatesSingleSubmit()
    {
        $service = $this->getServiceWithImport();
        $updates = $service->getLastUpdates('2014FA', 'KIN/F-168A');

        $this->assertCount(3, $updates);
        $this->assertArrayHasKey("KIN/F-168A-15", $updates);
        $this->assertArrayHasKey("KIN/F-168A-30", $updates);
        $this->assertArrayHasKey("KIN/F-168A-45", $updates);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function getLastUpdatesMultipleSubmit()
    {
        $service = $this->getServiceWithImport();
        $updates = $service->getLastUpdates('2014FA', 'KIN/F-191A');

        $this->assertCount(1, $updates);
        $this->assertArrayHasKey("KIN/F-191A-40", $updates);
    }

    /**
     * @test
     * @depends importExportData
     */
    public function getByTermAndSection()
    {
        $session = $this->getServiceWithImport();
        /** @var SLO $SLO */
        $SLO = $session->getByTermAndSection('2014FA', 'KIN/F-191A-40');

        $this->assertEquals(
            $SLO->getEnteredOn(),
            new \DateTime('2014-12-05 16:20:52', new \DateTimeZone("America/Los_Angeles"))
        );
    }

    private function getServiceWithImport()
    {
        $service = new RubricSLOService($this->session, []);
        $service->reset();
        $service->import(getCsvWithHeader($this->importFile));
        return $service;
    }

    /**
     * (@inheritdoc)
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet("./tests/data/initial.yml");
    }
}
