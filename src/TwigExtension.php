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

class TwigExtension extends \Twig_Extension
{
    /** @var array */
    private $data;

    /** @var bool */
    private $isDebug;

    public function __construct($data, $debug)
    {
        $this->data = $data;
        $this->isDebug = $debug;
    }

    public function getName()
    {
        return 'slocloud';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('isAuthenticated', array($this, 'isAuthenticated')),
            new \Twig_SimpleFunction('lookupPlo', array($this, 'lookupPlo')),
            new \Twig_SimpleFunction('lookupGeo', array($this, 'lookupGeo')),
            new \Twig_SimpleFunction('lookupIlo', array($this, 'lookupIlo')),
            new \Twig_SimpleFunction('css', array($this, 'css')),
            new \Twig_SimpleFunction('script', array($this, 'script'))
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('ifTrue', function ($test, $valueIfTrue) {
                return ($test ? $valueIfTrue : "");
            }),
            new \Twig_SimpleFilter('ifFalse', function ($test, $valueIfFalse) {
                return ($test ? "" : $valueIfFalse);
            }),
            new \Twig_SimpleFilter('if', function ($test, $valueIfTrue, $valueIfFalse) {
                return ($test ? $valueIfTrue : $valueIfFalse);
            })
        ];
    }

    public function lookupPlo($plo, $subject)
    {
        if ($plo === "N/A") {
            return "Not Applicable";
        } else {
            if (array_key_exists($subject, $this->data['PLOList'])) {
                if (array_key_exists($plo, $this->data['PLOList'][$subject])) {
                    return $this->data['PLOList'][$subject][$plo];
                } else {
                    return "Missing PLO for plo name '$plo'";
                }
            } else {
                return "Missing PLO for subject '$subject' and plo name '$plo'";
            }
        }
    }

    public function lookupGeo($geo)
    {
        if ($geo === "N/A") {
            return "Not Applicable";
        } else {
            if (array_key_exists($geo, $this->data['GEOList'])) {
                return $this->data['GEOList'][$geo]['Name'].": ".$this->data['GEOList'][$geo]['Statement'];
            } else {
                return "Missing GEO for geo name '$geo'";
            }
        }
    }

    public function lookupIlo($ilo)
    {
        if ($ilo === "N/A") {
            return "Not Applicable";
        } else {
            if (array_key_exists($ilo, $this->data['ILOList'])) {
                return $this->data['ILOList'][$ilo]['Name'] . ": " . $this->data['ILOList'][$ilo]['Statement'];
            } else {
                return "Missing ILO for ilo name '$ilo'";
            }
        }
    }

    public function isAuthenticated($appName = 'default')
    {
        /** @var \SLOCloud\Application $app */
        $app = Application::getInstance($appName);
        $disableLogin = $app->config('disable.login');
        if ($disableLogin) {
            return true;
        }
        return $app->isAuthenticated();
    }

    public function css($name)
    {
        $file = "css/$name";
        $minFile = str_replace(".css", ".min.css", $file);
        return $this->path($name, $minFile, $file);
    }

    public function script($name)
    {
        $file = "js/$name";
        $minFile = str_replace(".js", ".min.js", $file);
        return $this->path($name, $minFile, $file);
    }

    /**
     * @param string $name
     * @param string $minFile
     * @param string $file
     * @return string
     */
    private function path($name, $minFile, $file)
    {
        if (file_exists($minFile) && !$this->isDebug) {
            return $minFile . "?v=" . filemtime($minFile);
        } else {
            if (file_exists($file)) {
                return $file . "?v=" . filemtime($file);
            } else {
                return $name;
            }
        }
    }
}
