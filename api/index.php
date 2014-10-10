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
	$dbuser="slocloudusr";
	$dbpass="Qb749V77LzMyRpaB";
	$dbname="sloclouddb";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>
