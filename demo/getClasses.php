<?php

include("config.php"); 

// Grab our global vars from config file
global $subjectsList;
global $classesList; 
global $sloList; 

// Check to see if there's a get request (there needs to be, otherwise there's no point in this file)
if($_GET['subject']) {
	
	// Grab the subject
	$subject = $_GET['subject']; 

	// Echo an array containing all of the classes that are part of the requested subject
	
	echo json_encode($classesList[$subject]); 
}

?>