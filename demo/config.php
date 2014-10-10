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

// Arrays containing the subjects and classes
$subjectsList = array(
    "PHYS", "MATH", "PSYC"
    ); 

// Classes are sorted by subjectsList elements
$classesList["PHYS"] = array("PHYS101", "PHYS102", "PHYS103");
$classesList["MATH"] = array("MATH101", "MATH102", "MATH103");
$classesList["PSYC"] = array("PSYC101", "PSYC102", "PSYC103");

$sectionsList["PHYS101"] = array("76543","76567","79666");
$sectionsList["MATH101"] = array("76543","76567","79666");
$sectionsList["PSYC101"] = array("76543","76567","79666");
$sectionsList["PHYS102"] = array("76543","76567","79666");
$sectionsList["MATH102"] = array("76543","76567","79666");
$sectionsList["PSYC102"] = array("76543","76567","79666");
$sectionsList["PHYS103"] = array("76543","76567","79666");
$sectionsList["MATH103"] = array("76543","76567","79666");
$sectionsList["PSYC103"] = array("76543","76567","79666");

// Create a huge multidimensional array of SLOs 
$sloList["MATH101"] = array("Convert numbers between percentage, decimal, and fraction notation.",
		"Correctly perform the fundamental operations of arithmetic on whole numbers, fractions, and decimals.",
		"Correctly solve applications using percentage, ratio, proportion, and measurement.",
		"Demonstrate quantitative reasoning skills by developing convincing arguments and by communicating mathematically both verbally and in writing.",
		"Read graphs; find statistical mean, median and mode."
);

$sloList["MATH102"] = array("Convert rational numbers into decimals, fractions, and percentages.",
	"Demonstrate quantitative reasoning skills by developing convincing arguments and by communicating mathematically both verbally and in writing.",
	"Solve various application problems requiring the use of ratios, proportions, and percentages.",
	"To use appropriate technology such as calculators or computer software to enhance mathematical thinking, visualization, and understanding; to solve mathematical problems; and to judge the reasonableness of the results.",
	"Use rounding techniques to estimate results of operations on whole numbers, fractions, and decimals.",
	"Use the order of operations to add; subtract; multiply; and exponentiate whole numbers, fractions, and decimals."
);

$sloList["MATH103"] = array("Demonstrate quantitative reasoning skills by developing convincing arguments and by communicating mathematically both verbally and in writing.",
	"Set up the equation or inequality; then find the solution and explain the reasonableness of the answer a word or application problem.",
	"Solve and graph linear inequalities and calculate slopes and intercepts.",
	"Solve equations and simplify algebraic expressions involving exponents, polynomial and rational expressions and equations, roots, and radicals.",
	"Solve linear and quadratic equations by factoring, completing the square, and using the quadratic formula.",
	"Use appropriate technology such as calculators or computer software to enhance mathematical thinking, visualization, and understanding, to solve mathematical problems, and judge the reasonableness of the results."
);

$sloList["PHYS101"] = array(
    "Describe major aspects of mechanics of materials and objects.",
    "Describe the principles of electricity, magnetism and light.",
    "Explain the major concepts of modern physics.",
    "Explain the major scientific contributions of Aristotle, Copernicus, Galileo, Newton, Einstein, and other great scientists.",
    "Explain the principles of heat and sound."
    );

$sloList["PHYS102"] = array(
    "Analyze major aspects of mechanics of materials and objects.",
    "Describe the major aspects of Vibrations, Waves, and Sound.",
    "Evaluate the major scientific contributions of all great scientists.",
    "Explain the principles of Heat and Thermodynamics."
    );

$sloList["PHYS103"] = array(
    "Analyze the major scientific aspects of Electricity and Magnetism.",
    "Describe the Physics of Light and Optics.",
    "Evaluate the principles of Modern Physics."
    );

$sloList["PSYC101"] = array(
    "Perform and evaluate descriptive (e.g., mean, median, mode, variance, standard deviation) and inferential (e.g., Pearson correlation, t-tests, z-test, and one-way analysis of variance) statistics.",
    "Using SPSS software, input data, analyze data, and interpret output for statistics, t tests, correlation, and one-way analysis of variance.",
    "Identify major theories in developmental psychology."
        );

$sloList["PSYC102"] = array(
    "Demonstrate knowledge of the major psychological disorders defined in DSM IV TR.",
    "Demonstrate knowledge of the theoretical perspectives used to describe the causes of mental disorders.",
    "Be able to differentiate the major theoretical perspectives of psychology.",
    "Be able to explain why psychology is considered a science."
    );

$sloList["PSYC103"] = array(
    "Demonstrate knowledge of the major anatomical structures and functions of the nervous system.",
    "Describe, compare and contrast the predominant theories of gender development.",
    "Identify and differentiate male and female sexual and reproductive anatomy, physiology, and sexual responses."
    );
    
?>
