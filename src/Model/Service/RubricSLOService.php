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
use SLOCloud\Model\Storage\RubricSLO;
use SLOCloud\Model\Storage\RubricStatement;

class RubricSLOService extends SLOService
{
    protected $SLOClassName = 'SLOCloud\Model\Storage\RubricSLO';
    protected $StatementClassName = 'SLOCloud\Model\Storage\RubricStatement';

    public function getType()
    {
        return "Rubric";
    }

    protected function validateSLO($SLO)
    {
        /** @var RubricSLO $SLO */
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
        if (!is_string($SLO->getProposed()) || $SLO->getProposed() === "") {
            $this->validationErrors[] = "Missing proposed";
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
        /** @var RubricStatement $statement */
        Assertion::isInstanceOf($statement, $this->StatementClassName);

        $errors = [];
        if (!is_string($statement->getStatement()) || $statement->getStatement() === "") {
            $errors[] = "Missing statement";
        }
        if (!is_string($statement->getPlo()) || $statement->getPlo() === "") {
            $errors[] = "Missing plo";
        }
        if (!is_string($statement->getGeo()) || $statement->getGeo() === "") {
            $errors[] = "Missing geo";
        }
        if (!is_string($statement->getIlo()) || $statement->getIlo() === "") {
            $errors[] = "Missing ilo";
        }
        if (!is_string($statement->getMet()) || $statement->getMet() === "") {
            $errors[] = "Missing met";
        }
        if (!is_numeric($statement->getRubric1()) || $statement->getRubric1() < 0) {
            $errors[] = "Missing or invalid rubric1";
        }
        if (!is_numeric($statement->getRubric2()) || $statement->getRubric2() < 0) {
            $errors[] = "Missing or invalid rubric2";
        }
        if (!is_numeric($statement->getRubric3()) || $statement->getRubric3() < 0) {
            $errors[] = "Missing or invalid rubric3";
        }
        if (!is_numeric($statement->getRubric4()) || $statement->getRubric4() < 0) {
            $errors[] = "Missing or invalid rubric4";
        }

        return $errors;
    }

    protected function SLOFromPost(array $post)
    {
        $statementValues = self::getStatementValuesFromPost($post);
        $statements = [];

        foreach ($statementValues as $label => $values) {
            $s = new RubricStatement(
                $values["$label-statement"],
                $post["$label-rubric-1"],
                $post["$label-rubric-2"],
                $post["$label-rubric-3"],
                $post["$label-rubric-4"],
                $post["$label-met"],
                $post["$label-plo"],
                $post["$label-geo"],
                $post["$label-ilo"]
            );
            $statements[] = $s;
        }

        $SLO = new RubricSLO(
            new \DateTime(),
            $post['term'],
            $post['subject'],
            $post['class'],
            $post['section'],
            "Not Used",
            $post['proposed'],
            'Not Used',
            'Not Used',
            'Not Used',
            $statements
        );

        return $SLO;
    }

    protected function importSLO(array $row)
    {
        $SLO = new RubricSLO(
            new \DateTime($row['when']),
            $row['term'],
            $row['subject'],
            $row['class'],
            $row['section'],
            'Not Used',
            $row['proposed'],
            'Not Used',
            'Not Used',
            'Not Used',
            [$this->importStatement($row)],
            $row['id']
        );

        return $SLO;
    }

    protected function importStatement(array $row)
    {
        return new RubricStatement(
            $row['statement'],
            $row['rubric1'],
            $row['rubric2'],
            $row['rubric3'],
            $row['rubric4'],
            $row['met'],
            $row['plo'],
            $row['geo'],
            $row['ilo'],
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
            'class',
            'section',
            'proposed',
            'statement id',
            'rubric1',
            'rubric2',
            'rubric3',
            'rubric4',
            'met',
            'plo',
            'geo',
            'ilo',
            'statement'
        ];
    }

    protected function exportSLO($SLO)
    {
        /** @var RubricSLO $SLO */
        Assertion::isInstanceOf($SLO, $this->SLOClassName);

        $rows = [];
        foreach ($SLO->getStatements() as $statement) {
            /** @var RubricStatement $statement */
            $when = $SLO->getEnteredOn();
            $when->setTimeZone(new \DateTimeZone("America/Los_Angeles"));
            $rows[] = [
                $SLO->getId(),
                $when->format("Y-m-d H:i:s"),
                $SLO->getTerm(),
                $SLO->getSubject(),
                $SLO->getClass(),
                $SLO->getSection(),
                $SLO->getProposed(),
                $statement->getId(),
                $statement->getRubric1(),
                $statement->getRubric2(),
                $statement->getRubric3(),
                $statement->getRubric4(),
                $statement->getMet(),
                $statement->getPlo(),
                $statement->getGeo(),
                $statement->getIlo(),
                $statement->getStatement()
            ];
        }
        return $rows;
    }

    protected function fillData(&$SLO)
    {
        // Not used at this time
    }

    /**
     * @param RubricSLO $SLO
     * @param null|string[] $result
     * @return string[]
     */
    protected function calculateSummaryForSLO($SLO, $result = null)
    {
        if ($result === null || !array_key_exists("rubric1", $result)) {
            $result = [
                "rubric1" => 0,
                "rubric2" => 0,
                "rubric3" => 0,
                "rubric4" => 0,
                "total" => 0,
                "3-or-higher" => 0,
                "%-3-or-higher" => 0
            ];
        }

        foreach ($SLO->getStatements() as $statement) {
            $result = $this->calculateSummaryForStatement($statement, $result);
        }
        return $result;
    }

    /**
     * @param RubricStatement $statement
     * @param null|string[] $result
     * @return string[]
     */
    protected function calculateSummaryForStatement($statement, $result = null)
    {
        if ($result === null || !array_key_exists("rubric1", $result)) {
            $result = [
                "rubric1" => 0,
                "rubric2" => 0,
                "rubric3" => 0,
                "rubric4" => 0,
                "total" => 0,
                "3-or-higher" => 0,
                "%-3-or-higher" => 0
            ];
        }

        $result['rubric1'] += $statement->getRubric1();
        $result['rubric2'] += $statement->getRubric2();
        $result['rubric3'] += $statement->getRubric3();
        $result['rubric4'] += $statement->getRubric4();
        $result['total'] += $statement->getTotalAssessed();
        $result['3-or-higher'] += $statement->getTotalSufficient();
        $result['%-3-or-higher'] = number_format(($result['3-or-higher'] / $result['total']) * 100, 2);
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
        if (array_key_exists($subject, $PLOs)) {
            return $PLOs[$subject];
        } else {
            return [];
        }
    }

    /**
     * @param string[][] $results
     * @param RubricSLO $SLO
     * @param string[] $PLOs
     * @return string[]
     * @throws \Exception
     */
    protected function addPLOSummaryForSLO(array $results, $SLO, $PLOs)
    {
        foreach ($SLO->getStatements() as $statement) {
            if (array_key_exists($statement->getPlo(), $PLOs)) {
                $PLO = $PLOs[$statement->getPlo()];
            } else {
                throw new \Exception("PLO ".$statement->getPlo()." not found");
            }
            $results[$PLO] = $this->calculateSummaryForStatement($statement, $results[$PLO]);
        }
        return $results;
    }

    /**
     * @param string[][] $results
     * @param RubricSLO $SLO
     * @param string[] $ILOs
     * @return string[]
     * @throws \Exception
     */
    protected function addILOSummaryForSLO(array $results, $SLO, $ILOs)
    {
        foreach ($SLO->getStatements() as $statement) {
            if ($statement->getIlo() === "N/A") {
                $ILO = "N/A";
            } elseif (array_key_exists($statement->getIlo(), $ILOs)) {
                $ILO = $ILOs[$statement->getIlo()]['Name'].": ".$ILOs[$statement->getIlo()]['Statement'];
            } else {
                throw new \Exception("ILO ".$statement->getIlo()." not found");
            }

            $results[$ILO] = $this->calculateSummaryForStatement($statement, $results[$ILO]);
        }
        return $results;
    }

    /**
     * @param string[][] $results
     * @param RubricSLO $SLO
     * @param string[] $GEOs
     * @return string[]
     * @throws \Exception
     */
    protected function addGEOSummaryForSLO(array $results, $SLO, $GEOs)
    {
        foreach ($SLO->getStatements() as $statement) {
            if ($statement->getGeo() === "N/A") {
                $GEO = "N/A";
            } elseif (array_key_exists($statement->getGeo(), $GEOs)) {
                $GEO = $GEOs[$statement->getGeo()]['Name'].": ".$GEOs[$statement->getGeo()]['Statement'];
            } else {
                throw new \Exception("GEO ".$statement->getGeo()." not found");
            }

            $results[$GEO] = $this->calculateSummaryForStatement($statement, $results[$GEO]);
        }
        return $results;
    }

    protected function checkModelData($app, $controller)
    {
        $subjectsList = $app->data('subjectsList');
        $PLOs = $app->data('PLOList');
        $missing = [];
        // Verify every subject has PLOs
        foreach ($subjectsList as $term => $subjects) {
            foreach ($subjects as $id => $subject) {
                if (!array_key_exists($id, $PLOs)) {
                    if (!array_key_exists($id, $missing)) {
                        $missing[$id] = "Missing PLOs for $id, $term";
                    } else {
                        $missing[$id] .= " & $term";
                    }
                }
            }
        }

        $controller->recordMessages(
            $missing,
            "# of Missing PLO Entries: " . count($missing)
        );
    }

    public function registerRoutes($app)
    {
        // Not used at this time
    }
}
