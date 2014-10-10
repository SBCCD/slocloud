<?php

include("config.php"); 

// Grab our global vars from config file
global $subjectsList;
global $classesList; 
global $sloList; 
global $sectionsList;

// Check to see if there's a get request (there needs to be, otherwise there's no point in this file)
if($_GET['class']) {
	
	// Grab the subject
	$class = $_GET['class']; 

	// Echo an array containing all of the classes that are part of the requested subject
	
	echo json_encode($sloList[$class]); 
}

?>
