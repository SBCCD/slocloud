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
namespace SLOCloud\Controller;

use Doctrine\ORM\ORMException;
use SLOCloud\Model\ErrorResult;
use SLOCloud\Model\Storage\SLO;
use SLOCloud\Model\SuccessResult;

class Data extends Base
{
    public function getForm()
    {
        $app = $this->app;
        $type = $app->sloService->getType();
        $app->render("$type/reporting.html.twig", [
            'institution' => $app->config('institution'),
            'divisions' => $app->data('divisions'),
            'termsList' => $app->data('termsList'),
            'GEOList' => $app->data('GEOList'),
            'ILOList' => $app->data('ILOList')
        ]);
    }

    public function postForm()
    {
        $app = $this->app;
        $post = $app->request->post();
        try {
            $SLO = $app->sloService->submit($post);

            if ($SLO === false) {
                $app->response->setStatus(400);
                echo new ErrorResult("The SLO is invalid", $app->sloService->validationErrors);
                return;
            }

            $app->sendSLOSubmitEmail($SLO);

            $app->writeInfo("New SLO with id:'{$SLO->getId()}'");
            $app->flashKeep();
            echo new SuccessResult("New SLO with id: {$SLO->getId()}");
        } catch (\PDOException $e) {
            $app->writeError("Failed PDO query", array('exception' => $e));

            $app->flashKeep();
            $app->response->setStatus(500);
            echo new ErrorResult("An error has occurred. Please contact the helpdesk.");
        } catch (ORMException $e) {
            $app->writeError("Failed doctrine query", array('exception' => $e));

            $app->flashKeep();
            $app->response->setStatus(500);
            echo new ErrorResult("An error has occurred. Please contact the helpdesk.");
        } catch (\ErrorException $e) {
            $app->writeError("Failed post", array('exception' => $e));

            $app->flashKeep();
            $app->response->setStatus(500);
            echo new ErrorResult("An error has occurred. Please contact the helpdesk.");
        }
    }

    public function getSLOSummary()
    {
        $app = $this->app;
        $type = $app->sloService->getType();
        $app->render("$type/SLOSummary.html.twig", [
            'institution' => $app->config('institution'),
            'divisions' => $app->data('divisions'),
            'years' => getAcademicYears($app->getReportingTerms())
        ]);
    }

    public function getPSLOSummary()
    {
        $app = $this->app;
        $type = $app->sloService->getType();
        $app->render("$type/PSLOSummary.html.twig", [
            'institution' => $app->config('institution'),
            'divisions' => $app->data('divisions'),
            'years' => getAcademicYears($app->getReportingTerms()),
            'programs' => array_keys($app->data('PLOList'))
        ]);
    }

    public function getILOGEOSummary()
    {
        $app = $this->app;
        $type = $app->sloService->getType();
        $app->render("$type/ILOGEOSummary.html.twig", [
            'institution' => $app->config('institution'),
            'divisions' => $app->data('divisions'),
            'years' => getAcademicYears($app->getReportingTerms())
        ]);
    }

    public function getReset()
    {
        $app = $this->app;

        if ($app->userIsAdmin()) {
            if (!$app->config('allow-resets')) {
                echo "You must set 'allow-resets' to true to perform this operation!";
                return;
            }

            $app->render("Data/reset.html.twig", [
                'institution' => $app->config('institution')
            ]);
        }
    }

    public function postReset()
    {
        $app = $this->app;
        $reset = $app->request->post('reset');

        if ($app->userIsAdmin()) {
            if (!$app->config('allow-resets')) {
                echo "You must set 'allow-resets' to true to perform this operation!";
                return;
            }
            if ($reset === 'reset') {
                $app->sloService->reset();

                echo "Reset complete";
            }
        }
    }

    public function getSubjects()
    {
        $app = $this->app;
        $term = $app->request->get('term');
        $year = $app->request->get('year');
        $period = $app->request->get('period');
        $division = $app->request->get('division', null);
        $data = [];

        if ($term !== null) {
            $data = $this->getSubjectsList($term, $division);
        } elseif ($period !== null && $year !== null) {
            $data = $this->getSubjectsReport($year, $period, $division);
        } else {
            $app->error("Invalid arguments for ".__FUNCTION__);
        }

        $app->returnJson($data);
    }

    public function getClasses()
    {
        $app = $this->app;
        $term = $app->request->get('term');
        $year = $app->request->get('year');
        $period = $app->request->get('period');
        $subject = $app->request->get('subject');
        $data = [];

        if ($term !== null && $subject !== null) {
            $data = $this->getClassList($term, $subject);
        } elseif ($year !== null && $period !== null && $subject !== null) {
            $data = $this->getClassesReport($year, $period, $subject);
        } else {
            $app->error("Invalid arguments for ".__FUNCTION__);
        }

        $app->returnJson($data);
    }

    public function getSections()
    {
        $app = $this->app;
        $term = $app->request->get('term');
        $class = $app->request->get('class');
        $sectionsList = $app->data('sectionsList');

        $sections = [];
        if (array_key_exists($term, $sectionsList) && array_key_exists($class, $sectionsList[$term])) {
            $SLOSections = $app->sloService->getLastUpdates($term, $class);

            foreach ($sectionsList[$term][$class] as $section) {
                $sections[$section] = ["name" => $section, "when" => "Never"];
            }
            foreach ($SLOSections as $SLOSection => $when) {
                $sections[$SLOSection]["when"] = $when->format(\DateTime::ISO8601);
            }
        }

        $app->returnJson($sections);
    }

    public function getSLOs()
    {
        $app = $this->app;
        $class = $app->request->get('class');
        $section = $app->request->get('section');
        $term = $app->request->get('term');
        $data = [
            "statements" => [],
            "previous" => false
        ];

        if (array_key_exists($class, $app->data('SLOList'))) {
            $data['statements'] = $app->data('SLOList')[$class];
        }

        $SLO = $app->sloService->getByTermAndSection($term, $section);
        if ($SLO !== false) {
            $data['previous'] = $SLO;
        }

        $app->returnJson($data);
    }

    public function getPLOs()
    {
        $app = $this->app;
        $program = $app->request->get('program');
        $data = [];

        if (array_key_exists($program, $app->data('PLOList'))) {
            $data = $app->data('PLOList')[$program];
        }

        $app->returnJson($data);
    }

    public function getSLOSummaryData()
    {
        $app = $this->app;
        $year = $app->request->get('year');
        $period = $app->request->get('period');
        $class = $app->request->get('class');

        if ($class === null || $period === null || $year === null) {
            $app->error("Invalid arguments for ".__FUNCTION__);
        }

        $terms = termsForYearAndPeriod($year, $period);
        $SLOs = $app->sloService->getSLOs($terms, true, $class);
        $sections = $this->getApplicableSectionsByClass($class, $terms);
        $data = $this->calculateSLOSummary($SLOs, $sections);

        $app->returnJson($data);
    }

    public function getPSLOSummaryData()
    {
        $app = $this->app;
        $year = $app->request->get('year');
        $period = $app->request->get('period');
        $subject = $app->request->get('subject');
        $program = $app->request->get('program');

        if (($program === null && $subject === null) || $period === null || $year === null) {
            $app->error("Invalid arguments for ".__FUNCTION__);
        }

        $terms = termsForYearAndPeriod($year, $period);
        if ($subject !== null) {
            $sections = $this->getApplicableSectionsBySubject($subject, $terms);
        } else {
            $sections = $this->getApplicableSectionsByProgram($program, $terms);
        }
        $SLOs = $app->sloService->getSLOs($terms, true, null, $subject, $program);
        $data = $this->calculatePSLOSummary($SLOs, $subject, $program, $sections);

        $app->returnJson($data);
    }

    public function getILOGEOSummaryData()
    {
        $app = $this->app;
        $year = $app->request->get('year');
        $period = $app->request->get('period');
        $type = $app->request->get('type');

        if ($period === null || $year === null || $type === null) {
            $app->error("Invalid arguments for ".__FUNCTION__);
        }

        $terms = termsForYearAndPeriod($year, $period);
        $sections = $this->getApplicableSectionsByTerm($terms);
        $SLOs = $app->sloService->getSLOs($terms, true);
        if ($type == "ilo") {
            $data = $this->calculateILOSummary($SLOs, $sections);
        } elseif ($type === "geo") {
            $data = $this->calculateGEOSummary($SLOs, $sections);
        } else {
            throw new \Exception("Invalid type: $type");
        }

        $app->returnJson($data);
    }

    /**
     * @param string $year
     * @param string $period
     * @param string $division
     * @return array
     * @throws \Exception
     */
    private function getSubjectsReport($year, $period, $division)
    {
        $terms = termsForYearAndPeriod($year, $period);

        $subjects = [];
        foreach ($terms as $term) {
            $termSubjects = $this->getSubjectsList($term, $division);
            $subjects = array_merge($subjects, $termSubjects);
        }

        return $subjects;
    }

    /**
     * @param $term
     * @param $division
     * @return mixed
     */
    private function getSubjectsList($term, $division)
    {
        $app = $this->app;
        $subjects = [];
        $subjectsList = $app->data('subjectsList');
        if (array_key_exists($term, $subjectsList)) {
            if ($division !== null) {
                $map = $app->data('divisionDepartmentMap');
                if (array_key_exists($division, $map)) {
                    $departments = $map[$division];
                    foreach ($subjectsList[$term] as $id => $subject) {
                        if (in_array($subject['department'], $departments)) {
                            $subjects[$id] = $subject;
                        }
                    }
                }
            } else {
                $subjects = $subjectsList[$term];
            }
        }
        return $subjects;
    }

    /**
     * @param string $term
     * @param string $subject
     * @return array
     */
    private function getClassList($term, $subject)
    {
        $app = $this->app;
        $classesList = $app->data('classesList');
        if (array_key_exists($term, $classesList) &&
            array_key_exists($subject, $classesList[$term])
        ) {
            $classes = $classesList[$term][$subject];
            sort($classes);
            return $classes;
        } else {
            return [];
        }
    }

    /**
     * @param string $year
     * @param string $period
     * @param string $subject
     * @return array
     */
    private function getClassesReport($year, $period, $subject)
    {
        $terms = termsForYearAndPeriod($year, $period);

        $classes = [];
        foreach ($terms as $term) {
            $termClasses = $this->getClassList($term, $subject);
            $classes = array_merge($classes, $termClasses);
        }
        $classes = array_unique($classes);
        sort($classes);
        return $classes;
    }

    /**
     * @param SLO[] $SLOs
     * @param string[] $possibleReporting
     * @return array
     */
    private function calculateSLOSummary($SLOs, $possibleReporting)
    {
        list($statements, $proposed, $reporting) = $this->app->sloService->calculateSLOSummary($SLOs);

        $notReporting = $this->getNotReporting($possibleReporting, $reporting);

        $this->sortReports($notReporting);
        $this->sortReports($reporting);

        return [
            "proposed" => $proposed,
            "statements" => $statements,
            "reporting" => $reporting,
            "notReporting" => $notReporting
        ];
    }

    /**
     * @param SLO[] $SLOs
     * @param string $subject
     * @param string $program
     * @param string[] $possibleReporting
     * @return array
     * @throws \Exception
     */
    private function calculatePSLOSummary($SLOs, $subject, $program, $possibleReporting)
    {
        list($statements, $proposed, $reporting) =
            $this->app->sloService->calculatePLOSummary($SLOs, $subject, $program, $this->app->data('PLOList'));

        $notReporting = $this->getNotReporting($possibleReporting, $reporting);

        $this->sortReports($notReporting);
        $this->sortReports($reporting);

        return [
            "proposed" => $proposed,
            "statements" => $statements,
            "reporting" => $reporting,
            "notReporting" => $notReporting
        ];
    }

    /**
     * @param SLO[] $SLOs
     * @param string[] $possibleReporting
     * @return array
     * @throws \Exception
     */
    private function calculateILOSummary($SLOs, $possibleReporting)
    {
        $app = $this->app;

        list($statements, $proposed, $reporting) =
            $app->sloService->calculateILOSummary($SLOs, $app->data('ILOList'));

        $notReporting = $this->getNotReporting($possibleReporting, $reporting);

        $this->sortReports($notReporting);
        $this->sortReports($reporting);

        return [
            "proposed" => $proposed,
            "statements" => $statements,
            "reporting" => $reporting,
            "notReporting" => $notReporting
        ];
    }

    /**
     * @param SLO[] $SLOs
     * @param string[] $possibleReporting
     * @return array
     * @throws \Exception
     */
    private function calculateGEOSummary($SLOs, $possibleReporting)
    {
        $app = $this->app;

        list($statements, $proposed, $reporting) =
            $app->sloService->calculateGEOSummary($SLOs, $app->data('GEOList'));

        $notReporting = $this->getNotReporting($possibleReporting, $reporting);

        $this->sortReports($notReporting);
        $this->sortReports($reporting);

        return [
            "proposed" => $proposed,
            "statements" => $statements,
            "reporting" => $reporting,
            "notReporting" => $notReporting
        ];
    }

    /**
     * @param array $possibleReporting
     * @param array $reporting
     * @return array
     */
    private function getNotReporting($possibleReporting, $reporting)
    {
        $reported = array_map(function ($report) {
            return $report['section'] . "|" . $report['term'];
        }, $reporting);
        $notReporting = array_filter($possibleReporting, function ($section) use ($reported) {
            return !in_array($section, $reported);
        });
        $notReporting = array_map(function ($report) {
            $parts = explode("|", $report);
            return [
                "section" => $parts[0],
                "term" => $parts[1]
            ];
        }, $notReporting);
        return $notReporting;
    }

    private function sortReports(array &$array)
    {
        usort($array, function ($a, $b) {
            $result = -strcmp($a['term'], $b['term']);
            if ($result !== 0) {
                return $result;
            } else {
                return strcmp($a['section'], $b['section']);
            }
        });
    }

    private function getApplicableSectionsByClass($class, $terms)
    {
        $app = $this->app;
        $sectionsList = $app->data('sectionsList');

        $sections = [];
        foreach ($terms as $term) {
            if (array_key_exists($term, $sectionsList) && array_key_exists($class, $sectionsList[$term])) {
                foreach ($sectionsList[$term][$class] as $section) {
                    $sections[] = $section."|".$term;
                }
            }
        }

        return $sections;
    }

    private function getApplicableSectionsBySubject($subject, $terms)
    {
        $app = $this->app;
        $possibleSections = $app->data('sections');

        $sections = [];
        foreach ($terms as $term) {
            foreach ($possibleSections as $section) {
                if ($section['Term'] === $term && $section['Subject'] === $subject) {
                    $sections[] = $section['Section']."|".$term;
                }
            }
        }

        return $sections;
    }

    private function getApplicableSectionsByProgram($PLO, $terms)
    {
        $app = $this->app;
        $map = $app->data('coursePLOMap');
        $PLOs = $app->data('PLOList')[$PLO];

        $sections = [];
        foreach ($map as $course => $possiblePLOs) {
            foreach ($PLOs as $plo => $statement) {
                if (in_array($plo, $possiblePLOs)) {
                    $sections = array_merge($sections, $this->getApplicableSectionsByClass($course, $terms));
                }
            }
        }
        $sections = array_unique($sections);

        return $sections;
    }

    private function getApplicableSectionsByTerm($terms)
    {
        $app = $this->app;
        $sectionsList = $app->data('sectionsList');

        $sections = [];
        foreach ($terms as $term) {
            if (array_key_exists($term, $sectionsList)) {
                foreach (array_keys($sectionsList[$term]) as $class) {
                    foreach ($sectionsList[$term][$class] as $section) {
                        $sections[] = $section."|".$term;
                    }
                }
            }
        }

        return $sections;
    }
}
