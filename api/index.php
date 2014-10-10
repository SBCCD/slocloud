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
header("Access-Control-Allow-Origin: *"); // DISALLOW THIS DURING PRODUCTION!!
// This will allow anyone to connect to the api.

header('Content-Type: application/json');

/*

*/

require 'Slim/Slim.php';

$app = new Slim();

// Post a new SLO Report
$app->post('/api/savereport', function() {
	// Create a new report
	$sql = "INSERT INTO slo_reports (institution_id, year, term, subject, class, assessed, met_target, proposed_action) 
	VALUES (:institution_id, :year, :term, :subject, :class, :assessed, :met_target, :proposed_action)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		// Bind parameters from post vars
		$stmt->bindParam("institution_id", $app->request()->post('institution_id'));
		$stmt->bindParam("year", $app->request()->post('year'));
		$stmt->bindParam("term", $app->request()->post('term'));
		$stmt->bindParam("subject", $app->request()->post('subject'));
		$stmt->bindParam("class", $app->request()->post('class'));
		$stmt->bindParam("assessed", $app->request()->post('assessed'));
		$stmt->bindParam("met_target", $app->request()->post('met_target'));
		$stmt->bindParam("proposed_action", $app->request()->post('proposed_action'));
		$stmt->execute();
		//$program = $stmt->fetchObject();  
		$db = null;
		echo '{"success":{"text": ""}}'; 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
});

$app->run();


function getInstitutionName($institution_id) {
	$sql = "SELECT name FROM institutions WHERE id=:institution_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("institution_id", $institution_id);
		$stmt->execute();
		$program = $stmt->fetchObject();  
		$db = null;
		echo json_encode($program); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getConnection() {
	$dbhost="localhost";
	$dbuser="yourcredentialshere";
	$dbpass="yourcredentialshere";
	$dbname="sloclouddb";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>
