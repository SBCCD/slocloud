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

class Ldap extends TrackErrors
{
    /** @var mixed[][][] */
    private $config = array();
    private $lastError = null;
    private $lastErrno = null;

    public function __construct($settings, $writeDebug = null, $writeError = null)
    {
        parent::__construct($writeDebug, $writeError);
        //Todo: Make this more visible, can't tell what this should look like without reading the code
        $this->config = $settings;
    }

    public function lastError()
    {
        if ($this->lastError !== null) {
            return $this->lastError;
        } else {
            return "No Error";
        }
    }

    public function lastErrno()
    {
        if ($this->lastErrno !== null) {
            return $this->lastErrno;
        } else {
            return 0;
        }
    }

    public function open($hostname, $port)
    {
        $this->debug(__METHOD__ . ": Connecting to LDAP Server (" . $hostname . ") on Port (" . $port . ")...");

        $conn = ldap_connect($hostname, $port);  // must be a valid LDAP server!
        if ($conn) {
            $this->debug(__METHOD__ . ": Connection result is " . $conn . ".");

            if (ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
                $this->debug(__METHOD__ . ": Setting LDAP_OPT_PROTOCOL_VERSION to LDAPv3 (3) ... Success!");
            } else {
                $this->error(__METHOD__ . ": Setting LDAP_OPT_PROTOCOL_VERSION to LDAPv3 (3) ... Failed!");
            }

            if (ldap_set_option($conn, LDAP_OPT_REFERRALS, 0)) {
                $this->debug(__METHOD__ . ": Setting LDAP_OPT_REFERRALS ... Success!");
            } else {
                $this->error(__METHOD__ . ": Setting LDAP_OPT_REFERRALS ... Failed!");
            }

            if (ldap_set_option($conn, LDAP_OPT_SIZELIMIT, 100)) {
                $this->debug(__METHOD__ . ": Setting LDAP_OPT_SIZELIMIT ... Success!");
            } else {
                $this->error(__METHOD__ . ": Setting LDAP_OPT_SIZELIMIT ... Failed!");
            }
        } else {
            $this->error(__METHOD__ . ": Unable to connect to LDAP server.");
        }

        return $conn;
    }

    public function bind($conn, $username, $password)
    {
        $this->debug(__METHOD__ . ": Binding (" . $username . ") called!");

        // Note: No login or password is an "anonymous" bind, typically read-only access
        $ldap_bind = @ldap_bind($conn, $username, $password); // this is a particular user access.

        if (!$ldap_bind) {
            $this->debug(__METHOD__ . ": Binding (" . $username . ") ... Failed!");
            $this->lastError = ldap_error($conn);
            $this->lastErrno = ldap_errno($conn);
            $this->debug(__METHOD__ . ": Error #" . $this->lastErrno . ' - ' . $this->lastError . '.');
        } else {
            $this->debug(__METHOD__ . ": Binding (" . $username . ") ... Success!");
        }
        return $ldap_bind;
    }

    public function search($conn, $baseDn, $filter, $attributes)
    {
        //If not array, create array with passed values.
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $ldap_result = @ldap_search($conn, $baseDn, $filter, $attributes);

        if (!$ldap_result) {
            $this->debug(__METHOD__ . ": Searching (" . $baseDn . ")" . $filter . " ... Failed!");
            $this->lastError = ldap_error($conn);
            $this->lastErrno = ldap_errno($conn);
            $this->debug(__METHOD__ . ": Error #" . $this->lastErrno . ' - ' . $this->lastError . '.');
            return false;
        } else {
            $this->debug(__METHOD__ . ": Searching (" . $baseDn . ")" . $filter . " ... Success!");
        }

        $count = ldap_count_entries($conn, $ldap_result);
        $this->debug(__METHOD__ . ": Number of entries returned is " . $count . ".");

        if ($count > 0) {
            $info = ldap_get_entries($conn, $ldap_result);

            if (!$info) {
                $this->debug(__METHOD__ . ": Getting entries ... Failed!");
                $this->lastError = ldap_error($conn);
                $this->lastErrno = ldap_errno($conn);
                $this->debug(__METHOD__ . ": Error #" . $this->lastErrno . ' - ' . $this->lastError . '.');
                return false;
            } else {
                $this->debug(__METHOD__ . ": Getting entries (" . $info[0]['dn'] . ") ... Success!");
            }

            return $info[0]['dn'];
        } else {
            $this->debug(__METHOD__ . ": Search returned no data!");
            return false;
        }
    }

    public function get($conn, $baseDn, $filter, $attributes)
    {
        $this->startTrackingErrors();

        //If not array, create array with passed values.
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $ldap_result = ldap_read($conn, $baseDn, $filter, $attributes);

        if (!$ldap_result) {
            $this->debug(__METHOD__ . ": Retrieving (" . $baseDn . ")" . $filter . " ... Failed!");
            $this->lastError = ldap_error($conn);
            $this->lastErrno = ldap_errno($conn);
            $this->debug(__METHOD__ . ": Error #" . $this->lastErrno . ' - ' . $this->lastError . '.');
            return false;
        } else {
            $this->debug(__METHOD__ . ": Retrieving (" . $baseDn . ")" . $filter . " ... Success!");
        }

        $ldap_count = ldap_count_entries($conn, $ldap_result);
        $this->debug(__METHOD__ . ": Number of entries returned is " . $ldap_count . ".");

        if ($ldap_count > 0) {
            $ldap_info = ldap_get_entries($conn, $ldap_result);

            if (!$ldap_info) {
                $this->debug(__METHOD__ . ": Getting entries ... Failed!");
                $this->lastError = ldap_error($conn);
                $this->lastErrno = ldap_errno($conn);
                $this->debug(__METHOD__ . ": Error #" . $this->lastErrno . ' - ' . $this->lastError . '.');
                return false;
            } else {
                $this->debug(__METHOD__ . ": Getting entries (" . $baseDn . ") ... Success!");
            }

            return $ldap_info;
        } else {
            $this->debug(__METHOD__ . ": Get returned no data!");
            return false;
        }
    }

    public function getUser($type, $accountId)
    {
        if (array_key_exists($type, $this->config)) {
            $this->debug(__METHOD__ . ": Account type of '" . $type . "' found.");

            $settings = $this->config[$type];

            $hostname = $settings['host'];
            $port = $settings['port'];
            $base = $settings['dn'] . $settings['base'];
            $filter = '(' . $settings['filter']['AccountId'] . '=' . $accountId . ')';
            $attributes = array($settings['filter']['Login']);
            $username = $settings['username'];
            $password = $settings['password'];

            $this->debug(__METHOD__ . ": Looking for user account ... ");

            $conn = $this->open($hostname, $port);
            if ($conn === false) {
                $this->close($conn);
                return false;
            }
            $bind = $this->bind($conn, $username, $password);
            if ($bind === false) {
                $this->close($conn);
                return false;
            }
            $search = $this->search($conn, $base, $filter, array("dn"));
            if ($search === false) {
                $this->close($conn);
                return false;
            }
            $info = $this->get($conn, $search, "(objectClass=*)", $attributes);
            if ($info === false) {
                $this->close($conn);
                return false;
            }
            $this->close($conn);

            $domain = strtolower($type);

            $username = $info[0][$settings['filter']['Login']][0];
            $this->debug(__METHOD__ . ": User account found. (" . $username . ")");
            return $domain.'\\' . $username;
        } else {
            $this->error(__METHOD__ . ": Account type '$type' not configured.");
            return false;
        }
    }

    public function getUserAttribute($type, $accountId, $attribute)
    {
        if (array_key_exists($type, $this->config)) {
            $this->debug(__METHOD__ . ": Account type of '" . $type . "' found.");

            $settings = $this->config[$type];

            $base = $settings['dn'] . $settings['base'];
            $filter = '(' . $settings['filter']['AccountId'] . '=' . $accountId . ')';
            $attributes = array($attribute, $settings['filter']['Login']);
            $username = $settings['username'];
            $password = $settings['password'];
            $hostname = $settings['host'];
            $port = $settings['port'];

            $this->debug(__METHOD__ . ": Looking for user account ... ");

            $conn = $this->open($hostname, $port);
            if ($conn === false) {
                $this->close($conn);
                return false;
            }
            $bind = $this->bind($conn, $username, $password);
            if ($bind === false) {
                $this->close($conn);
                return false;
            }
            $search = $this->search($conn, $base, $filter, array("dn"));
            if ($search === false) {
                $this->close($conn);
                return false;
            }
            $info = $this->get($conn, $search, "(objectClass=*)", $attributes);
            if ($info === false) {
                $this->close($conn);
                return false;
            }
            $this->close($conn);

            $searchParts = str_replace('\, ', "|", $search);
            $searchParts = explode(",", $searchParts);

            $domain = '';
            foreach ($searchParts as $key => $value) {
                if ($domain == '') {
                    if (substr($value, 0, 3) == 'DC=') {
                        $domain .= substr($value, 3);
                    }
                } else {
                    if (substr($value, 0, 3) == 'DC=') {
                        $domain .= "." . substr($value, 3);
                    }
                }
            }

            $username = $info[0][$settings['filter']['Login']][0];
            $this->debug(__METHOD__ . ": User account found. (" . $username . ")");

            return $info[0][strtolower($attribute)][0];
        } else {
            $error = "Account type '$type' not configured.";
            $this->error(__METHOD__ . ": $error");
            $this->lastError = $error;
            return false;
        }

    }

    public function login($username, $password)
    {
        $this->debug(__METHOD__ . ": Login (" . $username . ") called!");

        $account = $this->findType($username);
        if ($account === false) {
            $this->error(__METHOD__.": Account type not supported");
            $this->lastError = "Account type not supported";
            return false;
        }

        $username = $this->fixUsername($username, $account);
        $settings = $this->config[$account['Type']];

        $base = $settings['dn'] . $settings['base'];
        $filter = '(' . $settings['filter']['Login'] . '=' . $account['Login'] . ')';
        $attributes = [$settings['filter']['AccountId']];
        $hostname = $settings['host'];
        $port = $settings['port'];

        $conn = $this->open($hostname, $port);
        if ($conn === false) {
            $this->close($conn);
            return false;
        }
        $bind = $this->bind($conn, $username, $password);
        if ($bind === false) {
            $this->close($conn);
            return false;
        }
        $search = $this->search($conn, $base, $filter, array("dn"));
        if ($search === false) {
            $this->close($conn);
            return false;
        }
        $info = $this->get($conn, $search, "(objectClass=*)", $attributes);
        if ($info === false) {
            $this->close($conn);
            return false;
        }
        $this->close($conn);

        if (array_key_exists($settings['filter']['AccountId'], $info[0])) {
            $account['AccountId'] = $info[0][$settings['filter']['AccountId']][0];
        } else {
            $account['AccountId'] = '';
        }
        $this->debug(__METHOD__.": Login process returned (".$account['AccountId'].").");
        return $account;
    }

    public function findUser($username)
    {
        $this->startTrackingErrors();
        $this->debug(__METHOD__ . ": Search (" . $username . ") called!");

        $account = $this->findType($username);
        if ($account === false) {
            $this->error(__METHOD__.": Account type not supported");
            $this->lastError = "Account type not supported";
            return false;
        }

        $settings = $this->config[$account['Type']];

        $base =$settings['dn'] . $settings['base'];
        $filter = '(' . $settings['filter']['Login'] . '=' . $account['Login'] . ')';
        $attributes = [$settings['filter']['AccountId']];
        $username = $settings['username'];
        $password = $settings['password'];
        $hostname = $settings['host'];
        $port = $settings['port'];

        $conn = $this->open($hostname, $port);
        if ($conn === false) {
            $this->close($conn);
            return false;
        }
        $bind = $this->bind($conn, $username, $password);
        if ($bind === false) {
            $this->close($conn);
            return false;
        }
        $search = $this->search($conn, $base, $filter, array("dn"));
        if ($search === false) {
            $this->close($conn);
            return false;
        }
        $info = $this->get($conn, $search, "(objectClass=*)", $attributes);
        if ($info === false) {
            $this->close($conn);
            return false;
        }
        $this->close($conn);

        $accountId = $info[0][$settings['filter']['AccountId']][0];
        return ($accountId == "" ? false : $info[0][$settings['filter']['AccountId']][0]);
    }

    public function close($ldap_conn)
    {
        $this->debug(__METHOD__ . ": Closing connection ...");

        $result = ldap_unbind($ldap_conn);
        if ($result) {
            $this->debug(__METHOD__ . ': Connection closed successfully.');
        } else {
            $this->error(__METHOD__ . ': Connection failed to close successfully.');
        }

        return $result;
    }

    private function fixUsername($username, $account)
    {
        $type = $this->config[$account['Type']]['type'];
        if ($type === "Active Directory") {
            $result = $account['Domain']."\\".$account['Login'];
            $this->debug(__METHOD__ . ": Fixed Username (" . $result . ")");
            return $result;
        } else {
            return $username;
        }
    }

    private function findType($username)
    {
        $account = array();
        $parts = explode("\\", $username);

        if (count($parts) === 2) {
            $account['Type'] = strtoupper($parts[0]);
            if (array_key_exists(strtoupper($parts[0]), $this->config)) {
                $account['Domain'] = $parts[0];
                $account['Login'] = $parts[1];

                $this->debug(__METHOD__ . ": Login type of '" . $account['Type'] . "' found.");
            } else {
                $this->error(__METHOD__.": Login type of '".$account['Type']."' not supported");
                return false;
            }
        } else {
            $account['Type'] = array_keys($this->config)[0];
            $account['Domain'] = strtolower($account['Type']);
            $account['Login'] = $username;
            $this->debug(__METHOD__ . ": Defaulting to login type of '" . $account['Type'] . "'.");
        }

        return $account;
    }
}
