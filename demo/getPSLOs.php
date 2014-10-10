<?php /* 

SLO Cloud - A Cloud-Based SLO Reporting Tool for Higher Education

This is a peer-reviewed, open-source, public project made possible by the Open Innovation in Higher Education project. 

Copyright (C) 2014 Jesse Lawson

Contributors: 
Jesse Lawson

THIS PROJECT IS LICENSED UNDER GPLv2. YOU MAY COPY, DISTRIBUTE AND MODIFY THE SOFTWARE AS LONG AS YOU TRACK CHANGES/DATES OF IN SOURCE FILES AND KEEP ALL MODIFICATIONS UNDER gpl. yOU CAN DISTRIBUTE YOUR APPLICATION USING A gpl LIBRARY COMMERCIALLY, BUT YOU MUST ALSO DISCLOSE THE SOURCE CODE.

GNU General Public License Version 2 Disclaimer:

---

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or visit http://opensource.org/licenses/GPL-2.0

---
 
 */

?>

<?php

include("programConfig.php"); 

// Grab our global vars from config file
global $subjectsList;
global $classesList; 
global $sloList; 
global $sectionsList;

// Check to see if there's a get request (there needs to be, otherwise there's no point in this file)
if($_GET['program']) {
	
	// Grab the subject
	$program = $_GET['program']; 

	// Echo an array containing all of the classes that are part of the requested subject
	
	echo json_encode($psloList[$program]); 
}

?>
