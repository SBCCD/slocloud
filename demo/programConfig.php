<?php

/* config.php

This file will be customized per institution, such that each file contains the configuration variables for
each different client.

*/


// To use this array, just call global $config; from whatever file we're in
$config = array(
    "institutionName" => "Demo Community College",
    "institutionShortName" => "DCC",
    "pocName" => "Abraham Lincoln",
    "pocEmail" => "honestabe@slocollege.edu"
);

$termsList = array(
	"Summer", "Fall", "Winter", "Spring"
);

// Arrays containing the programs and classes
$programsList = array(
    "Physics", "Mathematics", "Psychology"
    ); 


// Create a huge multidimensional array of SLOs 
$psloList["Physics"] = array("Explain kinetic energy to an iguana.",
		"Correctly assemble a trebuchet from ordinary conference handouts.",
		"Correctly solve applications using percentage, ratio, proportion, and measurement.",
		"Demonstrate quantitative reasoning skills by developing convincing arguments and by communicating mathematically both verbally and in writing.",
		"Read graphs; find statistical mean, median and mode."
);

$psloList["Mathematics"] = array("Convert rational numbers into decimals, fractions, and percentages.",
	"Demonstrate quantitative reasoning skills by developing convincing arguments and by communicating mathematically both verbally and in writing.",
	"Solve various application problems requiring the use of ratios, proportions, and percentages.",
	"To use appropriate technology such as calculators or computer software to enhance mathematical thinking, visualization, and understanding; to solve mathematical problems; and to judge the reasonableness of the results.",
	"Use rounding techniques to estimate results of operations on whole numbers, fractions, and decimals.",
	"Use the order of operations to add; subtract; multiply; and exponentiate whole numbers, fractions, and decimals."
);

$psloList["Psychology"] = array("Demonstrate knowledge of the major psychological disorders defined in DSM IV TR.",
    "Demonstrate knowledge of the theoretical perspectives used to describe the causes of mental disorders.",
    "Be able to differentiate the major theoretical perspectives of psychology.",
    "Be able to explain why psychology is considered a science."
    );

?>
