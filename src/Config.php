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

class Config
{
    /** @var string directory to look for ini files */
    protected $configDir;

    /** @var array settings read from ini files */
    public $settings;

    public function __construct($configDir)
    {
        $this->configDir = $configDir;
        $this->settings = parse_ini_file($this->configDir."/config.ini", true);
        $this->clean();
    }

    public function loadIni($file)
    {
        $fileName = $this->configDir."/$file";
        if (file_exists($fileName)) {
            $newSettings = parse_ini_file($fileName, true);

            $this->settings = Config::iniMerge($this->settings, $newSettings);
            $this->clean();
        } else {
            throw new \InvalidArgumentException("Can't find file $fileName");
        }
    }

    // the ini file format doesn't support multiple nested arrays, so
    // this function parses the filter keys into associative arrays
    public function ldap()
    {
        $ldap = $this->value('ldap');
        foreach ($ldap as $domain => $settings) {
            if (!is_array($settings['filter'])) {
                $ldap[$domain]['filter'] = Config::decodeMap($settings['filter']);
            }
        }
        return $ldap;
    }

    // The db section needs to support null on the port setting
    public function db()
    {
        $db = $this->value('db');
        $db['port'] = $this->maybeNull('port', 'db');
        return $db;
    }

    /**
     * @param string $key
     * @param string $section
     * @return bool
     * @throws \Exception
     */
    public function bool($key, $section = null)
    {
        $value = $this->value($key, $section);

        if ($value === '' || $value === 0 || $value === false) {
            return false;
        } elseif ($value === '1' || $value === 1 | $value === true) {
            return true;
        } else {
            throw new \Exception("Invalid bool value for ".($section === null?"":"[$section]")."[$key], got '$value'");
        }
    }

    public function maybeNull($key, $section = null)
    {
        $value = $this->value($key, $section);

        if ($value === '' || $value === null) {
            return null;
        } else {
            return $value;
        }
    }

    public function value($key, $section = null)
    {
        if ($section === null) {
            if (!array_key_exists($key, $this->settings)) {
                throw new \Exception("No key [$key] found in settings.");
            }
            return $this->settings[$key];
        } else {
            if (!array_key_exists($section, $this->settings)) {
                throw new \Exception("No section [$section] found in settings.");
            } elseif (!array_key_exists($key, $this->settings[$section])) {
                throw new \Exception("No key [$section][$key] found in settings.");
            }
            return $this->settings[$section][$key];
        }
    }

    // This cleans up certain deficiencies in the ini format as well
    // as does some additional processing
    protected function clean()
    {
        $this->settings['ldap'] = $this->ldap();
        $this->settings['db'] = $this->db();
        $this->settings['debug'] = $this->bool('debug');
        $this->settings['https'] = $this->bool('https');
    }

    //http://php.net/manual/en/function.parse-ini-file.php#86410
    protected static function iniMerge($config_ini, $custom_ini)
    {
        foreach ($custom_ini as $k => $v) {
            if (is_array($v)) {
                $config_ini[$k] = Config::iniMerge($config_ini[$k], $custom_ini[$k]);
            } else {
                $config_ini[$k] = $v;
            }
        }
        return $config_ini;
    }

    protected static function decodeMap($encoded)
    {
        if (is_array($encoded)) {
            return $encoded;
        }

        $map = [];
        $pairs = explode(';', $encoded);
        foreach ($pairs as $i => $pair) {
            $parts = explode('=', $pair);
            if (count($parts) > 1) {
                $map[$parts[0]] = $parts[1];
            } else {
                $map[$i] = $pair;
            }
        }

        return $map;
    }
}
