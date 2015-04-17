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

/*
 * These are functions that are specific to loading data from institutions. The loadData
 * function is the only one expected to exist by the rest of the application.
 */

/*
 * Gets an array of subjects from the given array of sections, for the given terms
 */
function getSubjects($sections)
{
    $subjects = array();
    foreach ($sections as $i => $section) {
        $term = $section['Term'];
        if (!array_key_exists($term, $subjects)) {
            $subjects[$term] = array();
        }
        if (!array_key_exists($section['Subject'], $subjects[$term])) {
            $subjects[$term][$section['Subject']] = [
                "id" => $section['Subject'],
                "department" => explode('|', $section['Departments'])[0]
            ];
        }
    }

    return $subjects;
}

function getTerms($sections)
{
    $terms = array();
    foreach ($sections as $i => $section) {
        if (!in_array($section['Term'], $terms)) {
            $terms[] = $section['Term'];
        }
    }

    sortTerms($terms);

    return $terms;
}

function getDivisions($data)
{
    $divisions = [];
    foreach ($data as $i => $row) {
        $division = [];
        $division['id'] = $row['Division ID'];
        $division['name'] = $row['Division Description'];
        $divisions[$division['id']] = $division;
    }

    return $divisions;
}

function getDivisionDepartmentMap($data)
{
    $map = [];
    foreach ($data as $i => $row) {
        $division = $row['Division'];
        $department = $row['Dept ID'];
        if (array_key_exists($division, $map)) {
            $map[$division][] = $department;
        } else {
            $map[$division] = [$department];
        }
    }
    foreach ($map as $division => $departments) {
        sort($map[$division]);
    }
    ksort($map);
    return $map;
}

function getSectionFacultyMap($sections)
{
    $map = [];
    foreach ($sections as $section) {
        if (!array_key_exists($section['Term'], $map)) {
            $map[$section['Term']] = [];
        }
        if (!array_key_exists('Faculty', $section)) {
            throw new ErrorException("Missing faculty record for section: ".$section['Section']);
        }
        $map[$section['Term']][$section['Section']] = $section['Faculty'];
    }

    return $map;
}

function getSectionDepartmentMap($sections)
{
    $map = [];
    foreach ($sections as $section) {
        if (!array_key_exists($section['Term'], $map)) {
            $map[$section['Term']] = [];
        }
        $map[$section['Term']][$section['Section']] = explode('|', $section['Departments'])[0];
    }

    return $map;
}

function getDepartmentDivisionMap($data)
{
    $map = [];
    foreach ($data as $i => $row) {
        $division = $row['Division'];
        $department = $row['Dept ID'];
        $map[$department] = $division;
    }

    ksort($map);
    return $map;
}

/*
 * Gets an array of courses, for the given subject, from the given array of sections.
 */
function getCourses($sections, $subjects)
{
    $courses = array();
    foreach ($sections as $i => $section) {
        $term = $section['Term'];
        if (!array_key_exists($term, $courses)) {
            $courses[$term] = array();
        }
        if (!array_key_exists($section['Subject'], $courses[$term])) {
            $courses[$term][$section['Subject']] = array();
        }
        if (array_key_exists($section['Subject'], $subjects[$term]) &&
            !in_array($section['Course'], $courses[$term][$section['Subject']])) {
            $courses[$term][$section['Subject']][] = $section['Course'];
        }
    }
    return $courses;
}

/*
 * Gets an array of sections, for the given courses, from the given array of sections.
 */
function getSections($sections, $courses)
{
    $courseSectionMap = array();
    foreach ($sections as $i => $section) {
        $term = $section['Term'];
        if (!array_key_exists($term, $courseSectionMap)) {
            $courseSectionMap[$term] = array();
        }
        if (!array_key_exists($section['Course'], $courseSectionMap[$term])) {
            $courseSectionMap[$term][$section['Course']] = array();
        }
        if (in_array($section['Course'], $courses) &&
            !in_array($section['Section'], $courseSectionMap[$term][$section['Course']])) {
            $courseSectionMap[$term][$section['Course']][] = $section['Section'];
        }
    }
    return $courseSectionMap;
}

function getAssessmentMethods($data)
{
    $assessments = [];
    foreach ($data as $assessment) {
        $assessments[$assessment['Course']] = $assessment['Assessment'];
    }
    return $assessments;
}

function getSLOs($SLOData, $courses)
{
    $SLOs = array();
    foreach ($SLOData as $i => $SLO) {
        if (!array_key_exists($SLO['Course'], $SLOs)) {
            $SLOs[$SLO['Course']] = array();
        }
        if (in_array($SLO['Course'], $courses) && !in_array($SLO['SLO Statement'], $SLOs[$SLO['Course']])) {
            $record = [];
            $record['Statement'] = $SLO['SLO Statement'];
            if (array_key_exists('PLO', $SLO)) {
                $record['PLO'] = $SLO['PLO'];
            }
            if (array_key_exists('GEO', $SLO)) {
                $record['GEO'] = $SLO['GEO'];
            }
            if (array_key_exists('ILO', $SLO)) {
                $record['ILO'] = $SLO['ILO'];
            }
            $SLOs[$SLO['Course']][] = $record;
        }
    }
    return $SLOs;
}

function getGEOs($GEOData)
{
    $GEOs = array();
    foreach ($GEOData as $i => $GEO) {
        $GEOs[$GEO['GEO No.']] = array(
            'Name' => $GEO['GEO Name'],
            'Statement' => $GEO['GEO Statement']
        );
    }
    return $GEOs;
}

function getILOs($ILOData)
{
    $ILOs = [];
    foreach ($ILOData as $i => $ILO) {
        $ILOs[$ILO['ILO No.']] = [
            'Name' => $ILO['ILO Name'],
            'Statement' => $ILO['ILO Statement']
        ];
    }
    return $ILOs;
}

function getPLOsForSimple($PLOData)
{
    $PLOs = [];
    foreach ($PLOData as $i => $PLO) {
        if (!array_key_exists($PLO['Program'], $PLOs)) {
            $PLOs[$PLO['Program']] = [];
        }
        if (!in_array($PLO['PLO No.'], $PLOs[$PLO['Program']])) {
            $PLOs[$PLO['Program']][$PLO['PLO No.']] = $PLO['PLO Statement'];
        }
    }
    ksort($PLOs);
    return $PLOs;
}

function getPLOsForRubric($PLOData, $subjects)
{
    $PLOs = [];
    foreach ($PLOData as $i => $PLO) {
        if (!array_key_exists($PLO['Program'], $PLOs)) {
            $PLOs[$PLO['Program']] = [];
        }
        if (in_array($PLO['Program'], $subjects) && !in_array($PLO['PLO No.'], $PLOs[$PLO['Program']])) {
            $PLOs[$PLO['Program']][$PLO['PLO No.']] = $PLO['PLO Statement'];
        }
    }
    return $PLOs;
}

function getCoursePLOMap($PLOData, $possibleCourses)
{
    $CoursePLOMap = [];
    foreach ($PLOData as $i => $PLO) {
        $courses = getNumericPart($PLO);
        foreach ($courses as $j => $course) {
            // Check for wildcard matches
            if (stristr($course, "*")) {
                // this will be slow, but it is only done one per cache cycle and is yet another
                // reason to put this stuff in a database!
                $pattern = '['.str_replace('*', '\w', $course).']';
                foreach ($possibleCourses as $possibleCourse) {
                    if (preg_match($pattern, $possibleCourse) === 1) {
                        if (!array_key_exists($possibleCourse, $CoursePLOMap)) {
                            $CoursePLOMap[$possibleCourse] = [];
                        }
                        $CoursePLOMap[$possibleCourse][] = $PLO['PLO No.'];
                    }
                }
            } else {
                if (!array_key_exists($course, $CoursePLOMap)) {
                    $CoursePLOMap[$course] = [];
                }
                $CoursePLOMap[$course][] = $PLO['PLO No.'];
            }
        }
    }
    foreach ($CoursePLOMap as $course => $PLO) {
        sort($CoursePLOMap[$course]);
    }
    ksort($CoursePLOMap);
    return $CoursePLOMap;
}

function getCourseILOMap($ILOData)
{
    $CourseILOMap = [];
    foreach ($ILOData as $i => $ILO) {
        $courses = getNumericPart($ILO);
        foreach ($courses as $j => $course) {
            if (!array_key_exists($course, $CourseILOMap)) {
                $CourseILOMap[$course] = [];
            }
            $CourseILOMap[$course][] = $ILO['ILO No.'];
        }
    }
    foreach ($CourseILOMap as $course => $ILOs) {
        sort($CourseILOMap[$course]);
    }
    ksort($CourseILOMap);
    return $CourseILOMap;
}

function getNumericPart($array)
{
    $result = [];
    foreach ($array as $i => $value) {
        if (is_numeric($i) && $value !== '') {
            $result[$i] = $value;
        }
    }
    return $result;
}

function filterSections($Sections, callable $termFilter, callable $departmentFilter)
{
    $rows = [];
    foreach ($Sections as $section) {
        if (call_user_func($termFilter, $section['Term']['Code'])) {
            if (array_any($section['Departments'], $departmentFilter)) {
                $row = [];
                $row['Term'] = $section['Term']['Code'];
                $row['Subject'] = $section['Subject']['Id'];
                $row['Course'] = $section['Subject']['Id'].'-'.$section['Course']['Number'];
                $row['Section'] = $section['Name'];
                $row['Faculty'] = implode('|', array_unique(array_map(function ($person) {
                    return substr($person['FirstName'], 0, 1)." ".$person['LastName'];
                }, $section['FacultyPersons'])));
                $row['Departments'] = implode('|', $section['Departments']);
                $rows[] = $row;
            }
        }
    }
    return $rows;
}

/**
 * @param \SLOCloud\Application $app
 * @param array|mixed $config
 * @return array|mixed
 * @throws ErrorException
 * @throws Exception
 */
function loadData($app, $config)
{
    $shortName = $config['institution']['shortName'];
    $cacheTime = intval($config['data.cachetime']);
    $dataDir = "../data/$shortName";
    $cacheDir = "../var/cache/$shortName";
    $cacheFile = "$cacheDir/Cache.json";
    $data = [];

    $now = new DateTime();
    if ($cacheTime > 0 && file_exists($cacheFile)) {
        $mtime = new DateTime("@".filemtime($cacheFile));
        $diff = $mtime->diff($now);
        if ($diff->i < $cacheTime) {
            $data = json_decode(file_get_contents($cacheFile), true);
            return $data;
        }
    }

    $data['sections'] = getCsvWithHeader("$dataDir/Sections.csv");
    if ($data['sections'] === false) {
        throw new Exception("Unable to get Section data");
    }

    $data['termsList'] = getTerms($data['sections']);

    if (file_exists("$dataDir/Sections.Manual.csv")) {
        $manualSections = getCsvWithHeader("$dataDir/Sections.Manual.csv");
        if ($manualSections === false) {
            throw new Exception("Unable to get Manual Section data");
        }
        // Manuals assumed to work in all terms specified in the Section file
        foreach ($manualSections as $section) {
            foreach ($data['termsList'] as $term) {
                $section['Term'] = $term;
                $data['sections'][] = $section;
            }
        }
    }

    // Arrays containing the subjects and classes
    $data['subjectsList'] = getSubjects($data['sections']);

    // Classes are sorted by subjectsList elements
    $data['classesList'] = getCourses($data['sections'], $data['subjectsList']);

    $data['sectionsList'] = getSections($data['sections'], flatten($data['classesList']));

    $SLOs = getCsvWithHeader("$dataDir/CSLOs.csv");
    if ($SLOs === false) {
        throw new Exception("Unable to get SLO Statements");
    }

    $PLOs = getCsvWithHeader("$dataDir/PLOs.csv");
    if ($PLOs === false) {
        throw new Exception("Unable to get PLO Mappings");
    }

    $GEOs = getCsvWithHeader("$dataDir/GEOs.csv");
    if ($GEOs === false) {
        throw new Exception("Unable to get GEO Mappings");
    }

    $ILOs = getCsvWithHeader("$dataDir/ILOs.csv");
    if ($ILOs === false) {
        throw new Exception("Unable to get ILO Mappings");
    }

    // Create a huge multidimensional array of SLOs, GEOs, PLOs and ILOs
    $data['SLOList'] = getSLOs($SLOs, flatten($data['classesList']));
    $data['GEOList'] = getGEOs($GEOs);
    $data['ILOList'] = getILOs($ILOs);

    if ($config['model']['slo_type'] === "Simple") {
        $data['PLOList'] = getPLOsForSimple($PLOs);

        $divisionData = getCsvWithHeader("$dataDir/Divisions.csv");
        if ($divisionData === false) {
            throw new Exception("Unable to get division data");
        }

        $mapData = getCsvWithHeader("$dataDir/DepartmentDivisionMap.csv");
        if ($mapData === false) {
            throw new Exception("Unable to get division/department map data");
        }

        $data['divisions'] = getDivisions($divisionData);
        $data['divisionDepartmentMap'] = getDivisionDepartmentMap($mapData);

        $data['coursePLOMap'] = getCoursePLOMap($PLOs, flatten($data['classesList']));
        $data['courseILOMap'] = getCourseILOMap($ILOs);

        $data['sectionFacultyMap'] = getSectionFacultyMap($data['sections']);
        $data['sectionDepartmentMap'] = getSectionDepartmentMap($data['sections']);
        $data['departmentDivisionMap'] = getDepartmentDivisionMap($mapData);

        $assessmentData = [];
        if (file_exists("$dataDir/Assessments.csv")) {
            $assessmentData = getCsvWithHeader("$dataDir/Assessments.csv");
            if ($assessmentData === false) {
                throw new Exception("Unable to get assessment method data");
            }
        }
        $data['assessmentList'] = getAssessmentMethods($assessmentData);
    } else {
        $data['PLOList'] = getPLOsForRubric($PLOs, flatten($data['subjectsList']));
    }

    if (!file_exists($cacheDir)) {
        mkdir($cacheDir);
    }

    if ($cacheTime > 0 && !file_put_contents($cacheFile, json_encode($data))) {
        throw new Exception("Failed to Cache data!");
    }

    return $data;
}
