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
