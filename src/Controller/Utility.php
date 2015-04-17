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

class Utility extends Base
{
    /** @var mixed[] */
    private $allMessages;

    public function session()
    {
        $app = $this->app;
        if ($app->isDebug() && $app->userIsAdmin()) {
            $this->returnLink();
            echo highlight_string("<?php\n\$_SESSION = " . var_export($_SESSION, true) . ";\n", true);
        }
    }

    public function info()
    {
        $app = $this->app;
        if ($app->isDebug() && $app->userIsAdmin()) {
            $this->returnLink();
            phpinfo();
        }
    }

    public function config()
    {
        global $config;
        $app = $this->app;
        if ($app->isDebug() && $app->userIsAdmin()) {
            $this->returnLink();
            echo highlight_string("<?php\n\$config = " . var_export($config, true) . ";\n", true);
        }
    }

    public function data()
    {
        $app = $this->app;
        if ($app->isDebug() && $app->userIsAdmin()) {
            $app->response->header("Content-Type", "text/text");
            echo "<?php\n\$data = " . var_export($app->data, true) . ";\n";
        }
    }

    public function getLoginAs()
    {
        $app = $this->app;
        if ($app->userIsAdmin()) {
            $app->render("Utility/loginAs.html.twig", [
                'institution' => $app->config('institution')
            ]);
        }
    }

    public function postLoginAs()
    {
        $app = $this->app;
        if ($app->userIsAdmin()) {
            $id = $app->request->post('id');
            $this->returnLink();
            if ($id !== '') {
                $app->saveLogin($id, $app->type);
                echo "Account Id changed. You no longer have access to this page.<br />";
                echo highlight_string("<?php\n\$_SESSION = " . var_export($_SESSION, true) . ";\n", true);
            } else {
                echo "Missing account id";
            }
        }
    }

    public function check()
    {
        $app = $this->app;
        if ($app->isDebug() || $app->userIsAdmin()) {
            $this->returnLink();
            $this->allMessages = [];
            $app->sloService->checkData($app, $this);

            $this->displayMessages();
        }
    }

    public function getCache()
    {
        $app = $this->app;
        if ($app->userIsAdmin()) {
            $app->render("Utility/cache.html.twig", [
                'institution' => $app->config('institution')
            ]);
        }
    }

    public function postClearCache()
    {
        $app = $this->app;
        if ($app->userIsAdmin()) {
            $clear = $app->request->post('clear');
            if ($clear === "clear") {
                $this->returnLink();
                $cacheDir = realpath('../var/cache');
                clearDirectory($cacheDir, ["README.txt"]);
                echo "Cache Cleared";
            }
        }
    }

    private function returnLink()
    {
        $url = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']))."";
        echo "<a href=\"$url\">return to main page</a><br />";
    }

    private function displayMessages()
    {
        ksort($this->allMessages);
        echo "<table border=\"1\">";
        foreach ($this->allMessages as $subject => $messages) {
            ksort($messages);
            echo "<tr>";
            echo "<th style=\"vertical-align: top;\">$subject</th>";
            echo "<td><table>";
            foreach ($messages as $course => $message) {
                echo "<tr><th style=\"text-align: left; width: 8em;\">$course</th><td>$message</td>";
            }
            echo "</table></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    /**
     * @param string[] $missing
     * @param string $msg
     * @return \string[]
     */
    public function recordMessages($missing, $msg)
    {
        ksort($missing);
        echo $msg."<br />";

        foreach ($missing as $course => $message) {
            $parts = explode("-", $course);
            if (array_key_exists($parts[0], $this->allMessages)) {
                if (array_key_exists($course, $this->allMessages[$parts[0]])) {
                    echo "Error, $course is already in all messages, but shouldn't be!<br />";
                    die();
                }
                $this->allMessages[$parts[0]][$course] = $message;
            } else {
                $this->allMessages[$parts[0]] = [$course => $message];
            }
        }
    }
}
