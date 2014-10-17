<?php

$reporting = "";

$slo = "";

$pslo = "";

if($page == "reporting") {

	$reporting = '<span style="background: #FFFF00;">';
} else {
	$reporting = "<span>";
} 

if($page == "dashboard") {
	$slo = '<span style="background: #FFFF00;">';
} else {
	$slo = "<span>";
} 

if($page == "programs") {
	$pslo = '<span style="background: #FFFF00;">';
} else {
	$pslo = "<span>"; 
} 

?>

<p>

<a href="http://lawsonry.com/projects/slocloud/demo"><?php echo $reporting; ?>Reporting Page</span></a> / <a href="http://lawsonry.com/projects/slocloud/demo/dashboard.php"><?php echo $slo; ?>SLO Summary Report</a> / <a href="http://lawsonry.com/projects/slocloud/demo/programs.php"><?php echo $pslo; ?>PSLO Summary Report</a></p>

		<hr/>

