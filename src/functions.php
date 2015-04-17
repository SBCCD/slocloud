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

// These should be general functions that are not specific to an institution

function array_any($array, callable $filter)
{
    return array_reduce($array, function ($return, $value) use ($filter) {
        return $return || call_user_func($filter, $value);
    }, false);
}

function beginsWith($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}

/**
 * clear a directory, except for an array of exceptions
 * @param string $dir
 * @param array $exceptions
 * @param bool $rmdir
 */
function clearDirectory($dir, $exceptions = [], $rmdir = false)
{
    if (is_dir($dir)) {
        $objects = array_diff(scandir($dir), array('.','..'));
        foreach ($objects as $object) {
            if (!in_array($object, $exceptions)) {
                if (is_dir($dir."/".$object)) {
                    clearDirectory($dir."/".$object, $exceptions, true);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }

        if ($rmdir && is_dir_empty($dir)) {
            rmdir($dir);
        }
    }
}

function is_dir_empty($dir)
{
    if (!is_readable($dir)) {
        throw new \Exception("'$dir' doesn't exist or isn't readable");
    }
    if (!is_dir($dir)) {
        throw new \Exception("'$dir' isn't a directory");
    }
    $handle = opendir($dir);
    if ($handle === false) {
        throw new \Exception("Failed to open '$dir'");
    }
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            return false;
        }
    }
    return true;
}

/**
 * Reads a file in CSV format with headers and returns an array of associative arrays.
 * Assumes the first row is the header and uses it for keys to the associative arrays.
 * Note: currently this assumes the input file is windows-1252 encoded. It isn't properly detected
 * as such and causes encoding failures.
 * @param string $file
 * @param bool $assumeUtf8
 * @return array|false
 * @throws Exception
 */
function getCsvWithHeader($file, $assumeUtf8 = false)
{
    if (!$assumeUtf8) {
        $data = file_get_contents($file);
        $data = iconv('windows-1252', 'utf-8', $data);

        $fp = fopen("php://temp", 'r+b');
        if ($fp === false) {
            return false;
        }

        fwrite($fp, $data);

        rewind($fp);
    } else {
        $fp = fopen($file, "r+b");
        if ($fp === false) {
            return false;
        }
    }

    $headers = [];
    $return = [];
    $row = 0;

    while (($data = fgetcsv($fp)) !== false) {
        if ($row == 0) {
            $headers = $data;
            $row++;
            continue;
        }
        foreach ($data as $col => $value) {
            if (array_key_exists($col, $headers)) {
                $return[$row][$headers[$col]] = $value;
            } else {
                throw new Exception("Missing header $col in $file on row $row");
            }
        }
        $row++;
    }

    fclose($fp);

    return $return;
}

/**
 * Writes the given array of associative arrays to the file using the associative keys as headers
 * in a CSV format.
 * @param string $file
 * @param string[][] $data
 * @param bool $writeWindows1252
 * @return bool
 */
function putCsvWithHeader($file, $data, $writeWindows1252 = false)
{
    if (!is_array($data) || count($data) <= 0 || !is_array(reset($data))) {
        return false;
    }

    if ($writeWindows1252) {
        array_walk_recursive($data, function (&$value) {
            $value = iconv('utf-8', 'windows-1252', $value);
        });
    }

    $rows = [array_keys(reset($data))];

    foreach ($data as $i => $line) {
        $rows[] = array_values($line);
    }

    $fp = fopen($file, 'w');
    if ($fp === false) {
        return false;
    }

    $return = array_reduce($rows, function ($return, $row) use ($fp) {
        if ($return) {
            $return = fputcsv($fp, $row);
        }
        return $return;
    }, true);

    fclose($fp);

    return $return;
}

function flatten($array)
{
    if (!is_array($array)) {
        // nothing to do if it's not an array
        return array($array);
    }

    $result = array();
    foreach ($array as $value) {
        // explode the sub-array, and add the parts
        $result = array_merge($result, flatten($value));
    }

    return array_unique($result);
}

// Based on http://php.net/manual/en/class.recursivedirectoryiterator.php#97228
function rsearch($path, $pattern)
{
    $Directory = new RecursiveDirectoryIterator($path);
    $Iterator = new RecursiveIteratorIterator($Directory);
    $Regex = new RegexIterator($Iterator, $pattern, RecursiveRegexIterator::GET_MATCH);
    return flatten(iterator_to_array($Regex));
}

/**
 * reorders the given array using the order in an array of keys provided
 * @param array $array
 * @param string[] $keys
 * @return array
 * @throws ErrorException
 */
function reorderWith($array, $keys)
{
    $ordered = [];
    foreach ($keys as $key) {
        if (array_key_exists($key, $array)) {
            $ordered[$key] = $array[$key];
        } else {
            throw new ErrorException("Missing key '$key' in array");
        }
    }
    return $ordered;
}

// http://php.net/manual/en/function.str-getcsv.php#88773
if (!function_exists('str_putcsv')) {
    function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        foreach ($input as $i => $value) {
            if (is_array($value)) {
                throw new Exception("Value $i of the provided array is also an array. Invalid for csv values");
            }
        }

        // Open a temporary "file" for read/write...
        $fp = fopen('php://temp', 'r+');
        // ... write the $input array to the "file" using fputcsv()...
        fputcsv($fp, $input, $delimiter, $enclosure);
        // ... rewind the "file" so we can read what we just wrote...
        rewind($fp);
        // ... read the entire file into a variable...
        $data = stream_get_contents($fp);
        // ... close the "file"...
        fclose($fp);
        // ... and return the $data to the caller, with the trailing newline from stream_get_contents() removed.
        return rtrim($data, "\n");
    }
}

function contains($haystack, $needle, $caseSensitive = false)
{
    if ($caseSensitive) {
        return strpos($haystack, $needle) !== false;
    } else {
        return stripos($haystack, $needle) !== false;
    }
}

/**
 * Equivalent to the Array.prototype.some in JavaScript
 * @param array $array
 * @param callable $callback
 * @return bool
 */
function array_some(array $array, callable $callback)
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $value, $key) === true) {
            return true;
        }
    }
    return false;
}

/**
 * Equivalent to the Array.prototype.every in JavaScript
 * @param array $array
 * @param callable $callback
 * @return bool
 */
function array_every(array $array, callable $callback)
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $value, $key) === false) {
            return false;
        }
    }
    return true;
}

function fixCourseId($candidate)
{
    $course = $candidate;
    $course = strtoupper($course);
    $course = str_replace([" ","-"], "", $course);
    if (preg_match('/^([A-Z\/]+)(\d+)([A-Z]{1,2}\d?)?$/', $course, $parts) === 0) {
        throw new Exception("Incorrect Course value: '$course' ('$candidate')");
    } else {
        if (strlen($parts[2]) != 3) {
            $parts[2] = "0".$parts[2];
        }
        $course = $parts[1]."-".$parts[2];
        if (array_key_exists(3, $parts)) {
            $course .= $parts[3];
        }
    }
    if (preg_match('/^[A-Z\/]+-\d{3}(([A-Z])|([A-Z]\d)|([A-Z]X\d))?$/', $course) === 0) {
        throw new Exception("Incorrect Course value: '$course' ('$candidate')");
    }

    return $course;
}

function first(array $array)
{
    if (count($array) <= 0) {
        return false;
    }
    return $array[key($array)];
}

function sortTerms(array &$terms)
{
    usort($terms, function ($a, $b) {
        $a = termToInt($a);
        $b = termToInt($b);
        return ($a === $b) ? 0 : (($a > $b) ? -1 : 1);
    });
}

/**
 * @param string[] $terms
 * @return string[]
 * @throws \Exception
 */
function getAcademicYears($terms)
{
    $academicYears = [];
    foreach ($terms as $term) {
        if (preg_match('/^(\d+)([a-zA-Z]+)$/', $term, $matches) === 1) {
            $year = intval($matches[1]);
            $code = strtoupper($matches[2]);

            switch($code) {
                case "FA":
                    $academicYear = $year;
                    break;
                case "SP":
                case "SM":
                    $academicYear = $year-1;
                    break;
                default:
                    throw new \Exception("Invalid term $term");
            }
            if (!array_key_exists($academicYear, $academicYears)) {
                $academicYears[$academicYear] = "$academicYear - ".($academicYear+1);
            }
        } else {
            throw new \Exception("Invalid term $term");
        }
    }

    krsort($academicYears);

    return $academicYears;
}

/**
 * returns the terms for a given year
 * @param int $year
 * @return array
 */
function termsForYear($year)
{
    return [$year . "FA", ($year + 1) . "SP", ($year + 1) . "SM"];
}

/**
 * @param string $year
 * @param string $period
 * @return array
 * @throws \Exception
 */
function termsForYearAndPeriod($year, $period)
{
    $year = intval($year);

    if ($period === "annual") {
        return termsForYear($year);
    } elseif ($period === "last3") {
        $terms = array_merge(
            termsForYear($year),
            termsForYear($year - 1),
            termsForYear($year - 2)
        );
        sortTerms($terms);
        return $terms;
    } else {
        $period = strtoupper($period);
        switch ($period) {
            case "FA":
                return [$year . $period];
                break;
            case "SP":
            case "SM":
                return [($year + 1) . $period];
                break;
            default:
                throw new \Exception("Invalid period '$period'");
                break;
        }
    }
}

function termToInt($term)
{
    preg_match('/(\d+)([a-zA-Z]{2})/', $term, $matches);
    list(, $year, $termCode) = $matches;
    $result = intval($year)*10;
    switch($termCode) {
        case "SP":
            $result += 1;
            break;
        case "SM":
            $result += 2;
            break;
        case "FA":
            $result += 3;
            break;
        default:
            throw new \Exception("Unknown term code '$termCode'");
    }
    return $result;
}

// https://stackoverflow.com/a/4254008
function is_assoc($array)
{
    return (bool)count(array_filter(array_keys($array), 'is_string'));
}
