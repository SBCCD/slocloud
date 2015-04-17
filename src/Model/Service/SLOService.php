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
use Doctrine\ORM\EntityManager;
use SLOCloud\Application;
use SLOCloud\Controller\Utility;
use SLOCloud\Middleware\AutomaticTransactions;
use SLOCloud\Model\Storage\SLO;

/**
 * Service responsible for working with SLOs
 * @package SLOCloud\Model\Service
 */
abstract class SLOService
{
    /** @var string */
    protected $SLOClassName;
    /** @var string */
    protected $StatementClassName;

    /** @var EntityManager */
    protected $session = null;
    /** @var array Static Data */
    protected $data = null;
    /** @var array */
    public $validationErrors = [];

    public function __construct(EntityManager $session, array $data)
    {
        $this->session = $session;
        $this->data = $data;
    }

    abstract public function getType();

    abstract protected function SLOFromPost(array $post);

    abstract protected function fillData(&$SLO);

    abstract protected function exportSLO($SLO);

    abstract protected function importSLO(array $row);

    abstract protected function importStatement(array $row);

    /** @return string[] */
    abstract protected function getExportKeys();

    abstract protected function validateSLO($SLO);

    abstract protected function validateStatement($statement);

    abstract protected function calculateSummaryForSLO($SLO, $result = null);

    abstract protected function calculateSummaryForStatement($statement, $result = null);

    abstract protected function getPossiblePLOs($PLOs, $subject, $program);

    abstract protected function addPLOSummaryForSLO(array $results, $SLO, $PLOs);

    abstract protected function addILOSummaryForSLO(array $results, $SLO, $ILOs);

    abstract protected function addGEOSummaryForSLO(array $results, $SLO, $GEOs);

    /**
     * @param Application $app
     * @param Utility $controller
     */
    abstract protected function checkModelData($app, $controller);

    /**
     * @param Application $app
     */
    abstract public function registerRoutes($app);

    /**
     * @param Application $app
     * @param Utility $controller
     */
    public function checkData($app, $controller)
    {
        $classes = $app->data('classesList');
        $allCourses = flatten($classes);
        $outcomes = $app->data('SLOList');

        $this->checkModelData($app, $controller);

        $missing = [];
        // Verify every course has statements
        foreach ($classes as $term => $subjects) {
            foreach ($subjects as $subject => $courses) {
                foreach ($courses as $id => $course) {
                    if (!array_key_exists($course, $outcomes)) {
                        if (!array_key_exists($course, $missing)) {
                            $missing[$course] = "Missing Statements. Offered: $term";
                        } else {
                            $missing[$course] .= " & $term";
                        }
                    }
                }
            }
        }

        $controller->recordMessages(
            $missing,
            "# of Courses without Statements: " . count($missing)
        );

        $missing = [];
        // Verify every statement has a course
        foreach ($outcomes as $course => $statements) {
            if (!in_array($course, $allCourses)) {
                if (!array_key_exists($course, $missing)) {
                    $missing[$course] = "Have statements, but no sections";
                }
            }
        }

        $controller->recordMessages(
            $missing,
            "# of Courses with a Statement, but no Sections: " . count($missing)
        );

    }

    /**
     * @param SLO[] $SLOs
     * @param string[] $GEOs
     * @return string[][]
     */
    public function calculateGEOSummary(array $SLOs, $GEOs)
    {
        $statements = [];
        $proposed = [];
        $reporting = [];
        $GEOs["N/A"] = "N/A";

        foreach ($GEOs as $geoName => $geo) {
            if ($geoName !== "N/A") {
                $geo = $geo['Name'].": ".$geo['Statement'];
            }
            $statements[$geo] = [];
        }

        foreach ($SLOs as $SLO) {
            $statements = $this->addGEOSummaryForSLO($statements, $SLO, $GEOs);

            if (trim($SLO->getProposed()) !== "") {
                $proposed[] = nl2br(trim($SLO->getProposed())."\n(".$SLO->getSection()." for ".$SLO->getTerm().")");
            }
            $reporting[] = [
                "when" => $SLO->getEnteredOn()->format(\DateTime::ISO8601),
                "section" => $SLO->getSection(),
                "term" => $SLO->getTerm()
            ];
        }

        // don't return long string keys. Always an array with numeric keys.
        $statements = $this->mapToNumericKeys($statements);

        return [$statements, $proposed, $reporting];
    }

    /**
     * @param SLO[] $SLOs
     * @param string[] $ILOs
     * @return string[][]
     */
    public function calculateILOSummary(array $SLOs, $ILOs)
    {
        $statements = [];
        $proposed = [];
        $reporting = [];
        $ILOs["N/A"] = "N/A";

        foreach ($ILOs as $ILOName => $ILO) {
            if ($ILOName !== "N/A") {
                $ILO = $ILO['Name'].": ".$ILO['Statement'];
            }
            $statements[$ILO] = [];
        }

        foreach ($SLOs as $SLO) {
            $statements = $this->addILOSummaryForSLO($statements, $SLO, $ILOs);

            if (trim($SLO->getProposed()) !== "") {
                $proposed[] = nl2br(trim($SLO->getProposed())."\n(".$SLO->getSection()." for ".$SLO->getTerm().")");
            }
            $reporting[] = [
                "when" => $SLO->getEnteredOn()->format(\DateTime::ISO8601),
                "section" => $SLO->getSection(),
                "term" => $SLO->getTerm()
            ];
        }

        // don't return long string keys. Always an array with numeric keys.
        $statements = $this->mapToNumericKeys($statements);

        return [$statements, $proposed, $reporting];
    }

    /**
     * @param SLO[] $SLOs
     * @param string $subject
     * @param string $program
     * @param string[] $PLOs
     * @return \string[][]
     */
    public function calculatePLOSummary(array $SLOs, $subject, $program, $PLOs)
    {
        $statements = [];
        $proposed = [];
        $reporting = [];

        $PLOs = $this->getPossiblePLOs($PLOs, $subject, $program);
        $PLOs["N/A"] = "N/A";

        foreach ($PLOs as $ploName => $plo) {
            $statements[$plo] = [];
        }

        foreach ($SLOs as $SLO) {
            $statements = $this->addPLOSummaryForSLO($statements, $SLO, $PLOs);
            if (trim($SLO->getProposed()) !== "") {
                $proposed[] = nl2br(trim($SLO->getProposed())."\n(".$SLO->getSection()." for ".$SLO->getTerm().")");
            }
            $reporting[] = [
                "when" => $SLO->getEnteredOn()->format(\DateTime::ISO8601),
                "section" => $SLO->getSection(),
                "term" => $SLO->getTerm()
            ];
        }

        // don't return long string keys. Always an array with numeric keys.
        $statements = $this->mapToNumericKeys($statements);

        return [$statements, $proposed, $reporting];
    }

    /**
     * @param SLO[] $SLOs
     * @return string[][]
     */
    public function calculateSLOSummary(array $SLOs)
    {
        $proposed = [];
        $statements = [];
        $reporting = [];

        foreach ($SLOs as $SLO) {
            foreach ($SLO->getStatements() as $statement) {
                if (array_key_exists($statement->getStatement(), $statements)) {
                    $statements[$statement->getStatement()] =
                        $this->calculateSummaryForStatement($statement, $statements[$statement->getStatement()]);
                } else {
                    $statements[$statement->getStatement()] = $this->calculateSummaryForStatement($statement);
                }
            }
            if (trim($SLO->getProposed()) !== "") {
                $proposed[] = nl2br(trim($SLO->getProposed())."\n(".$SLO->getSection()." for ".$SLO->getTerm().")");
            }
            $reporting[] = [
                "when" => $SLO->getEnteredOn()->format(\DateTime::ISO8601),
                "section" => $SLO->getSection(),
                "term" => $SLO->getTerm()
            ];
        }

        // don't return long string keys. Always an array with numeric keys.
        $statements = $this->mapToNumericKeys($statements);

        return [$statements,$proposed, $reporting];
    }

    public function getTermsOnRecord()
    {
        $terms = flatten(
            $this->session->createQueryBuilder()
            ->select('slo.term')->distinct()
            ->from($this->SLOClassName, 'slo')
            ->getQuery()
            ->execute()
        );

        sortTerms($terms);
        return $terms;
    }

    /**
     * @param string $term
     * @param string $class
     * @return \DateTime[]
     */
    public function getLastUpdates($term, $class)
    {
        /** @var SLO[] $SLOs */
        $SLOs = $this->session
            ->getRepository($this->SLOClassName)
            ->findBy(['term' => $term, 'class' => $class]);

        $sections = [];
        foreach ($SLOs as $SLO) {
            $sections[$SLO->getSection()] = $SLO->getEnteredOn();
        }

        return $sections;
    }

    public function getByTermAndSection($term, $section)
    {
        $SLO = $this->session
            ->createQuery(
                "select SLO from ".$this->SLOClassName." SLO ".
                "join SLO.statements statement ".
                "where SLO.term = '$term' and SLO.section ='$section' ".
                "order by SLO.enteredOn DESC, SLO.id "
            )
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if ($SLO === null) {
            return false;
        }

        $this->fillData($SLO);
        return $SLO;
    }

    public function import(array $rows)
    {
        $SLOs = $this->SLOsFromExport($rows);

        foreach ($SLOs as $id => $SLO) {
            $this->session->persist($SLO);
        }

        $this->session->flush();
        $this->session->clear(); // free up memory from import

        return count($SLOs);
    }

    /**
     * @param SLO[] $SLOs
     * @return string[][]
     */
    public function export(array $SLOs)
    {
        $rows = [$this->getExportKeys()];
        foreach ($SLOs as $SLO) {
            $rows = array_merge($rows, $this->ExportSLO($SLO));
        }
        return $rows;
    }

    public function reset()
    {
        $this->session->clear();
        $this->truncate($this->StatementClassName);
        $this->truncate($this->SLOClassName);
    }

    protected function truncate($classname)
    {
        $this->session->getEventManager()->dispatchEvent(AutomaticTransactions::PRE_NON_DOCTRINE_EVENT_NEEDING_COMMIT);
        $cmd = $this->session->getClassMetadata($classname);
        $connection = $this->session->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL($cmd->getTableName()));
        $this->session->getEventManager()->dispatchEvent(AutomaticTransactions::POST_NON_DOCTRINE_EVENT_NEEDING_COMMIT);
    }

    /**
     * @param integer $id
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function SLOExists($id)
    {
        Assertion::integerish($id);
        $SLO = $this->session->find($this->SLOClassName, $id);
        return $SLO !== null;
    }

    /**
     * @param integer $id
     * @return null|SLO
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getSLOById($id)
    {
        Assertion::integerish($id);

        $SLO = $this->session->find($this->SLOClassName, $id);
        if ($SLO === null) {
            throw new \InvalidArgumentException("No SLO with id $id");
        }

        $this->fillData($SLO);
        return $SLO;
    }

    /**
     * Get SLOs matching the provided parameters. $subject and $program are mutually exclusive
     * @param null|array $terms
     * @param bool $latestOnly
     * @param null|string $class
     * @param null|string $subject
     * @param null|string $program
     * @return SLO[]
     */
    public function getSLOs($terms = null, $latestOnly = false, $class = null, $subject = null, $program = null)
    {
        $qb = $this->session
            ->createQueryBuilder()
            ->select('SLO')
            ->addSelect('statement')
            ->from($this->SLOClassName, 'SLO')
            ->join('SLO.statements', 'statement');

        if (is_array($terms) && count($terms) > 0) {
            $qb->where('SLO.term in (:term)');
        }
        if (!is_null($class) && is_string($class)) {
            $qb->andWhere('SLO.class = :class');
        }
        if (!is_null($subject) && is_string($subject)) {
            $qb->andWhere('SLO.subject = :subject');
        } elseif (!is_null($program) && is_string($program)) {
            $qb->andWhere('SLO.plos LIKE :plos');
        }

        if ($latestOnly) {
            $qb->addOrderBy('SLO.enteredOn', 'ASC');
        }
        $qb->addOrderBy('SLO.id', 'ASC');

        $query = $qb->getQuery();

        if (is_array($terms) && count($terms) > 0) {
            $query->setParameter("term", $terms);
        }
        if (!is_null($class) && is_string($class)) {
            $query->setParameter("class", $class);
        }
        if (!is_null($subject) && is_string($subject)) {
            $query->setParameter("subject", $subject);
        } elseif (!is_null($program) && is_string($program)) {
            $query->setParameter("plos", "%$program%");
        }

        $SLOs = $query->execute();

        $this->fillDataArray($SLOs);

        return $latestOnly? self::onlyLatestSubmission($SLOs) : $SLOs;
    }

    /**
     * @param string[] $post
     * @return bool|SLO
     */
    public function submit($post)
    {
        $SLO = $this->SLOFromPost($post);

        if (!$this->isValid($SLO)) {
            return false;
        }

        $this->session->persist($SLO);
        $this->session->flush($SLO);

        return $SLO;
    }

    public function isValid(SLO $SLO)
    {
        Assertion::isInstanceOf($SLO, 'SLOCloud\Model\Storage\SLO');

        $this->validateSLO($SLO);
        return (count($this->validationErrors) > 0 ? false : true);
    }

    protected function SLOsFromExport(array $rows)
    {
        /** @var SLO[] $SLOs */
        $SLOs = [];
        foreach ($rows as $row) {
            if (array_key_exists($row['id'], $SLOs)) {
                $SLOs[$row['id']]->addStatement($this->importStatement($row));
            } else {
                if (count(array_diff(array_keys($row), $this->getExportKeys())) !== 0) {
                    throw new \Exception("CSV File format mismatch. Must be for SLO type ".$this->getType());
                }
                $SLOs[$row['id']] = $this->importSLO($row);
            }
        }
        return $SLOs;
    }

    protected function fillDataArray(array &$SLOs)
    {
        foreach ($SLOs as &$SLO) {
            $this->fillData($SLO);
        }
    }

    /**
     * @param $statements
     * @return array
     */
    protected function mapToNumericKeys($statements)
    {
        $statements = array_map(function ($statement, $result) {
            $result['statement'] = $statement;
            return $result;
        }, array_keys($statements), $statements);
        return $statements;
    }

    /**
     * @param SLO[] $SLOs
     * @return SLO[] mixed
     */
    protected static function onlyLatestSubmission(array $SLOs)
    {
        /** @var SLO[] $latest */
        $latest = [];
        foreach ($SLOs as $slo) {
            $key = $slo->getSection() . "|" . $slo->getTerm();
            if (!array_key_exists($key, $latest)) {
                $latest[$key] = $slo;
            } elseif ($latest[$key]->getEnteredOn() < $slo->getEnteredOn()) {
                $latest[$key] = $slo;
            }
        }

        $ids = array_map(function (SLO $slo) {
            return $slo->getId();
        }, $latest);

        return array_combine($ids, array_values($latest));
    }

    protected static function getStatementValuesFromPost($post)
    {
        $values = self::filterByKeys($post, function ($k) {
            return str_split($k, 3)[0] === "slo";
        });

        $statementValues = self::groupByKeys($values, function ($k) {
            return explode('-', $k)[0];
        });

        return $statementValues;
    }

    /**
     * filter an associative array by key using the passed filter function
     * @param array $array
     * @param callable $filter
     * @return array
     */
    protected static function filterByKeys($array, $filter)
    {
        $filteredKeys = array_filter(array_keys($array), $filter);
        return array_intersect_key($array, array_flip($filteredKeys));
    }

    /**
     * group the values in an associative array by key using the passed mapping function.
     * @param array $array
     * @param callable $map
     * @return array
     */
    protected static function groupByKeys($array, $map)
    {
        // make an associative array with only the keys to group by
        $result = array_flip(array_unique(array_map($map, array_keys($array))));
        // initialize each group to an empty array
        $result = array_map(function () {
            return [];
        }, $result);
        // copy each key/value pair into the resulting group
        return array_reduce(array_keys($array), function ($carry, $item) use ($array, $map) {
            $carry[call_user_func($map, $item)][$item] = $array[$item];
            return $carry;
        }, $result);
    }
}
