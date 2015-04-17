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

use Assert\Assertion;
use SLOCloud\Model\Storage\SimpleSLO;
use SLOCloud\Model\Storage\SimpleStatement;

class SimpleSLOService extends SLOService
{
    protected $SLOClassName = 'SLOCloud\Model\Storage\SimpleSLO';
    protected $StatementClassName = 'SLOCloud\Model\Storage\SimpleStatement';

    public function getType()
    {
        return "Simple";
    }

    protected function validateSLO($SLO)
    {
        /** @var SimpleSLO $SLO */
        Assertion::isInstanceOf($SLO, $this->SLOClassName);

        $this->validationErrors = [];
        if (!is_object($SLO->getEnteredOn()) || get_class($SLO->getEnteredOn()) !== "DateTime") {
            $this->validationErrors[] = "Missing when";
        }
        if (!is_string($SLO->getTerm()) || $SLO->getTerm() === "") {
            $this->validationErrors[] = "Missing term";
        }
        if (!is_string($SLO->getSubject()) || $SLO->getSubject() === "") {
            $this->validationErrors[] = "Missing subject";
        }
        if (!is_string($SLO->getClass()) || $SLO->getClass() === "") {
            $this->validationErrors[] = "Missing class";
        }
        if (!is_string($SLO->getSection()) || $SLO->getSection() === "") {
            $this->validationErrors[] = "Missing section";
        }
        if (!is_array($SLO->getPloArray()) || count($SLO->getPloArray()) === 0) {
            $this->validationErrors[] = "Missing plos";
        }
        if (!is_array($SLO->getGeoArray()) || count($SLO->getGeoArray()) === 0) {
            $this->validationErrors[] = "Missing geos";
        }
        if (!is_array($SLO->getIloArray()) || count($SLO->getIloArray()) === 0) {
            $this->validationErrors[] = "Missing ilos";
        }

        if (count($SLO->getStatements()) <= 0) {
            $this->validationErrors[] = "Must have at least one Statement";
        }

        foreach ($SLO->getStatements() as $i => $statement) {
            $errors = $this->validateStatement($statement);
            $this->validationErrors = array_merge(
                $this->validationErrors,
                array_map(function ($s) use ($i) {
                    return $s . " for Statement $i";
                }, $errors)
            );
        }
    }

    protected function validateStatement($statement)
    {
        /** @var SimpleStatement $statement */
        Assertion::isInstanceOf($statement, $this->StatementClassName);

        $errors = [];
        if (!is_string($statement->getStatement()) || $statement->getStatement() === "") {
            $errors[] = "Missing statement";
        }
        if (!is_numeric($statement->getAssessed())) {
            $errors[] = "Invalid assessed";
        }
        if (!is_numeric($statement->getTargetMet())) {
            $errors[] = "Invalid target met";
        }

        if ($statement->getTargetMet() > $statement->getAssessed()) {
            $errors[] = "Can't have larger target met than assessed";
        }

        return $errors;
    }

    protected function SLOFromPost(array $post)
    {
        $statementValues = self::getStatementValuesFromPost($post);
        $statements = [];

        foreach ($statementValues as $label => $values) {
            $s = new SimpleStatement(
                $values["$label-statement"],
                $post["$label-assessed"],
                $post["$label-target-met"],
                -1,
                -1,
                "Not Used",
                "Not Used",
                "Not Used",
                "Not Used"
            );
            $statements[] = $s;
        }

        $SLO = new SimpleSLO(
            new \DateTime(),
            $post['term'],
            $post['subject'],
            $post['class'],
            $post['section'],
            $post['method'],
            $post['proposed'],
            $post['plos'],
            'Not Used',
            $post['ilos'],
            $statements
        );

        return $SLO;
    }

    protected function importSLO(array $row)
    {
        $SLO = new SimpleSLO(
            new \DateTime($row['when']),
            $row['term'],
            $row['subject'],
            $row['course'],
            $row['section'],
            $row['assessment method'],
            $row['reflections'],
            $row['program level outcomes'],
            'Not Used',
            $row['core competencies'],
            [$this->importStatement($row)],
            $row['id']
        );

        return $SLO;
    }

    protected function importStatement(array $row)
    {
        return new SimpleStatement(
            $row['statement'],
            $row['assessed'],
            $row['met target'],
            -1,
            -1,
            "Not Used",
            "Not Used",
            "Not Used",
            "Not Used",
            null,
            $row['statement id']
        );
    }

    protected function getExportKeys()
    {
        return [
            'id',
            'when',
            'term',
            'subject',
            'division',
            'course',
            'section',
            'faculty',
            'assessment method',
            'reflections',
            'program level outcomes',
            'geos',
            'core competencies',
            'statement id',
            'met target',
            'assessed',
            'statement'
        ];
    }

    protected function exportSLO($SLO)
    {
        /** @var SimpleSLO $SLO */
        Assertion::isInstanceOf($SLO, $this->SLOClassName);

        $rows = [];
        foreach ($SLO->getStatements() as $statement) {
            /** @var SimpleStatement $statement */
            $when = $SLO->getEnteredOn();
            $when->setTimeZone(new \DateTimeZone("America/Los_Angeles"));
            $rows[] = [
                $SLO->getId(),
                $when->format("Y-m-d H:i:s"),
                $SLO->getTerm(),
                $SLO->getSubject(),
                $SLO->getDivision(),
                $SLO->getClass(),
                $SLO->getSection(),
                $SLO->getFaculty(),
                $SLO->getMethod(),
                $SLO->getProposed(),
                $SLO->getPLos(),
                $SLO->getGeos(),
                $SLO->getIlos(),
                $statement->getId(),
                $statement->getTargetMet(),
                $statement->getAssessed(),
                $statement->getStatement()
            ];
        }
        return $rows;
    }

    protected function fillData(&$SLO)
    {
        /** @var SimpleSLO $SLO */
        Assertion::isInstanceOf($SLO, $this->SLOClassName);

        if (array_key_exists('sectionFacultyMap', $this->data) &&
            array_key_exists($SLO->getTerm(), $this->data['sectionFacultyMap']) &&
            array_key_exists($SLO->getSection(), $this->data['sectionFacultyMap'][$SLO->getTerm()])) {
            $SLO->setFaculty($this->data['sectionFacultyMap'][$SLO->getTerm()][$SLO->getSection()]);
        }

        if (array_key_exists('sectionDepartmentMap', $this->data) &&
            array_key_exists($SLO->getTerm(), $this->data['sectionDepartmentMap']) &&
            array_key_exists($SLO->getSection(), $this->data['sectionDepartmentMap'][$SLO->getTerm()])) {
            $department = $this->data['sectionDepartmentMap'][$SLO->getTerm()][$SLO->getSection()];
            if (array_key_exists($department, $this->data['departmentDivisionMap'])) {
                $SLO->setDivision($this->data['departmentDivisionMap'][$department]);
            }
        }
    }

    /**
     * @param SimpleSLO $SLO
     * @param null|string[] $result
     * @return string[]
     */
    protected function calculateSummaryForSLO($SLO, $result = null)
    {
        if ($result === null || !array_key_exists("assessed", $result)) {
            $result = [
                "assessed" => 0,
                "met-target" => 0,
                "%-met-target" => 0
            ];
        }

        foreach ($SLO->getStatements() as $statement) {
            $result = $this->calculateSummaryForStatement($statement, $result);
        }
        return $result;
    }

    /**
     * @param SimpleStatement $statement
     * @param null|string[] $result
     * @return string[]
     */
    protected function calculateSummaryForStatement($statement, $result = null)
    {
        if ($result === null || !array_key_exists("assessed", $result)) {
            $result = [
                "assessed" => 0,
                "met-target" => 0,
                "%-met-target" => 0
            ];
        }

        $result['assessed'] += $statement->getAssessed();
        $result['met-target'] += $statement->getTargetMet();
        $result['%-met-target'] =  number_format(($result['met-target'] / $result['assessed']) * 100, 2);
        return $result;
    }

    /**
     * @param string[][] $PLOs
     * @param string $subject
     * @param string $program
     * @return string[]
     */
    protected function getPossiblePLOs($PLOs, $subject, $program)
    {
        if (array_key_exists($program, $PLOs)) {
            return $PLOs[$program];
        } else {
            return [];
        }
    }

    /**
     * @param string[][] $results
     * @param SimpleSLO $SLO
     * @param string[] $PLOs
     * @return string[]
     */
    protected function addPLOSummaryForSLO(array $results, $SLO, $PLOs)
    {
        foreach ($SLO->getPloArray() as $PLOName) {
            if (array_key_exists($PLOName, $PLOs)) {
                $PLO = $PLOs[$PLOName];
                $results[$PLO] = $this->calculateSummaryForSLO($SLO, $results[$PLO]);
            }
        }
        return $results;
    }

    /**
     * @param string[][] $results
     * @param SimpleSLO $SLO
     * @param string[] $ILOs
     * @return \string[]
     * @throws \Exception
     */
    protected function addILOSummaryForSLO(array $results, $SLO, $ILOs)
    {
        foreach ($SLO->getIloArray() as $ILOName) {
            if ($ILOName === "N/A") {
                $ILO = "N/A";
            } elseif (array_key_exists($ILOName, $ILOs)) {
                $ILO = $ILOs[$ILOName]['Name'].": ".$ILOs[$ILOName]['Statement'];
            } else {
                throw new \Exception("ILO ".$ILOName." not found");
            }

            $results[$ILO] = $this->calculateSummaryForSLO($SLO, $results[$ILO]);
        }
        return $results;
    }

    /**
     * @param string[][] $results
     * @param SimpleSLO $SLO
     * @param string[] $GEOs
     * @return \string[]
     * @throws \Exception
     */
    protected function addGEOSummaryForSLO(array $results, $SLO, $GEOs)
    {
        foreach ($SLO->getGeoArray() as $GEOName) {
            if ($GEOName === "N/A") {
                $GEO = "N/A";
            } elseif (array_key_exists($GEOName, $GEOs)) {
                $GEO = $GEOs[$GEOName]['Name'].": ".$GEOs[$GEOName]['Statement'];
            } else {
                throw new \Exception("GEO ".$GEOName." not found");
            }

            $results[$GEO] = $this->calculateSummaryForSLO($SLO, $results[$GEO]);
        }
        return $results;
    }

    protected function checkModelData($app, $controller)
    {
        // No checks at this time
    }

    public function registerRoutes($app)
    {
        $app->get(
            '/simpleOutcomes',
            [$app, 'requireAuthenticationAJAX'],
            '\SLOCloud\Controller\Simple\Data:getSimpleOutcomes'
        );
    }
}
