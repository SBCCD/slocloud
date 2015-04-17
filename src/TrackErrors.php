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
namespace SLOCloud;

use Monolog\Logger;

class TrackErrors
{
    /** @var callback */
    protected $writeDebug;
    /** @var callback */
    protected $writeError;
    /** @var string[] */
    public $debugLog = array();
    /** @var string[] */
    public $errorLog = array();
    /** @var mixed */
    protected $oldTrackErrors = null;

    public function __construct($writeDebug = null, $writeError = null)
    {
        $this->writeDebug = $writeDebug;
        $this->writeError = $writeError;
        $this->startTrackingErrors();
    }

    private function writeDebug($msg)
    {
        $this->debugLog[] = $msg;
        if ($this->writeDebug !== null) {
            call_user_func($this->writeDebug, $msg);
        }
    }

    private function writeError($msg)
    {
        $this->errorLog[] = $msg;
        if ($this->writeError !== null) {
            call_user_func($this->writeError, $msg);
        }
    }

    public function reportErrors(Logger $log)
    {
        foreach ($this->errorLog as $msg) {
            $log->error($msg);
        }
    }

    public function reportDebug(Logger $log)
    {
        foreach ($this->debugLog as $msg) {
            $log->debug($msg);
        }
    }

    protected function debug($message)
    {
        global $php_errormsg;
        $this->writeDebug($message . (isset($php_errormsg) ? ": " . $php_errormsg : ""));
    }

    protected function error($message)
    {
        global $php_errormsg;
        $this->writeError($message . (isset($php_errormsg) ? ": " . $php_errormsg : ""));
    }

    protected function startTrackingErrors()
    {
        if (@ini_get('track_errors') != true) {
            $return = @ini_set('track_errors', true);
            if ($return === false) {
                $this->error(__METHOD__ . ": Unable to enable 'track_errors' setting");
                return;
            }
            $this->oldTrackErrors = $return;
        }
    }

    protected function stopTrackingErrors()
    {
        if ($this->oldTrackErrors != null) {
            $return = @ini_set('track_errors', $this->oldTrackErrors);
            if ($return === false) {
                $this->error(__METHOD__ . ": Unable to restore 'track_errors' setting: ");
                $this->oldTrackErrors = null;
                return;
            }
            $this->oldTrackErrors = null;
        }
    }
}
