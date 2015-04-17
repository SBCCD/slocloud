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

use SLOCloud\Model\ErrorResult;

class ExportImport extends Base
{
    public function getExport()
    {
        $app = $this->app;
        $app->render("ExportImport/export.html.twig", [
            'institution' => $app->config('institution'),
            'years' => getAcademicYears($app->getReportingTerms())
        ]);
    }

    public function postExport()
    {
        $app = $this->app;
        $year = $app->request->post("year");
        $period = $app->request->post("period");
        $filter = $app->request->post("filter");
        $format = $app->request->post("format");
        $encoding = $app->request->post("encoding");
        $encodingOption = $app->request->post("encodingOption");

        $terms = $year !== "all"? termsForYearAndPeriod($year, $period) : [];
        $SLOs = $app->sloService->getSLOs($terms, $filter !== "all");

        switch($encoding) {
            case "ANSI":
                $charset = "windows-1252";
                break;

            case "UTF-8":
                $charset = "utf-8";
                break;

            default:
                $app->response->headers->set('Cache-Control', 'no-cache');
                $error = "Bad encoding specified: $encoding";
                $app->writeError($error);
                echo json_encode(new ErrorResult($error));
                return;
        }

        if ($encodingOption === "debug" && $charset !== "utf-8") {
            $encodingErrors = [];
            $chars = [];
            foreach ($SLOs as $SLO) {
                foreach ($this->strSplitUnicode($this->toCSV([$SLO])) as $char) {
                    try {
                        iconv('utf-8', $charset, $char);
                    } catch (\Exception $e) {
                        if (!in_array($char, $chars)) {
                            $chars[] = $char;
                            $encodingErrors[] = "The character '$char' in SLO with id ".$SLO->getId().
                                " for ".$SLO->getTerm()." ".$SLO->getSection()." on ".
                                $SLO->getEnteredOn()->format("n/j/Y g:i:s a")." is not able to be ".
                                "converted to that encoding.";
                        }
                    }
                }
            }

            if (!empty($encodingErrors)) {
                $app->response->headers->set('Cache-Control', 'no-cache');
                $app->writeError($encodingErrors);
                echo json_encode(new ErrorResult($encodingErrors));
                return;
            } else {
                $app->response->headers->set('Cache-Control', 'no-cache');
                $error = "No problems found in encoding. Use 'none' to download.";
                $app->writeError($error);
                echo json_encode(new ErrorResult($error));
                return;
            }
        }

        if ($encodingOption === "skip" && $charset !== "utf-8") {
            $good = [];
            foreach ($SLOs as $SLO) {
                $failed = false;
                foreach ($this->strSplitUnicode($this->toCSV($app->sloService->export([$SLO]))) as $char) {
                    try {
                        iconv('utf-8', $charset, $char);
                    } catch (\Exception $e) {
                        $failed = true;
                    }
                }
                if (!$failed) {
                    $good[] = $SLO;
                }
            }
            $SLOs = $good;
        }

        if (count($SLOs) <= 0) {
            $app->response->headers->set('Cache-Control', 'no-cache');
            $error = "No records returned";
            $app->writeError($error);
            echo json_encode(new ErrorResult($error));
            return;
        }

        $app->flashKeep();
        switch($format) {
            case "csv":
                $ext = "csv";
                $mime = "text/csv";
                $data = $this->toCSV($app->sloService->export($SLOs));
                break;

            case "tsv":
                $ext = "tsv";
                $mime = "text/tab-separated-values";
                $data = $this->toTSV($app->sloService->export($SLOs));
                break;

            default:
                $app->response->headers->set('Cache-Control', 'no-cache');
                $error = "Bad format specified: $format";
                $app->writeError($error);
                echo json_encode(new ErrorResult($error));
                return;
        }

        if ($charset !== 'utf-8') {
            try {
                $data = iconv('utf-8', $charset, $data);
            } catch (\Exception $e) {
                $app->response->headers->set('Cache-Control', 'no-cache');
                $error = "Encoding failed. Please try a different ".
                    "encoding, use the 'debug' option find the problem, or use 'skip' to ignore the problem SLOs.";
                $app->writeError($error);
                echo json_encode(new ErrorResult($error));
                return;
            }
        }

        $app->setCookie("fileDownload", "true");
        $app->response->headers->set('Content-Disposition', 'attachment; filename="extract.'.$ext.'"');
        $app->response->headers->set('Content-Type', $mime.'; charset='.$charset);
        echo $data;
    }

    public function getImport()
    {
        $app = $this->app;
        if ($app->userIsAdmin()) {
            $app->render("ExportImport/import.html.twig", [
                'institution' => $app->config('institution')
            ]);
        }
    }

    public function postImport()
    {
        global $_FILES;
        $app = $this->app;

        if ($app->userIsAdmin()) {
            $file = $_FILES['file'];

            if ($app->hasUploadErrored($file)) {
                echo $app->uploadError($file);
                $app->writeError($app->uploadError($file));
                return;
            }

            $rows = getCsvWithHeader($file['tmp_name']);

            $count = $app->sloService->import($rows);
            echo "Imported $count SLOs";
        }
    }

    /**
     * @param string[] $rows
     * @return string
     * @throws \Exception
     */
    private function toCSV(array $rows)
    {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = str_putcsv($row);
        }
        return implode(PHP_EOL, $lines);
    }

    /**
     * @param string[] $rows
     * @return string
     * @throws \Exception
     */
    private function toTSV(array $rows)
    {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = str_putcsv($row, "\t");
        }
        return implode(PHP_EOL, $lines);
    }

    /**
     * same as a str_split, but assumes string is unicode
     * http://php.net/manual/en/function.str-split.php#107658
     * @param string $str
     * @param int $l
     * @return array
     */
    private function strSplitUnicode($str, $l = 0)
    {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}
