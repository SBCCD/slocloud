<?php
/* Remember that this is going to be called from a sub-folder, so all the URLs need to be changed to add the ../ in front of them */

session_start();

// Each institution will have its own customization file.
require_once("programConfig.php");

global $config;

global $programsList; 

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
    <title>SLOCloud&trade;</title>

    <meta charset="utf-8">

    <meta name="robots" content="noindex,nocache,noarchive">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Jesse Lawson">

    <!-- Google Font: Open Sans -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600,600italic,800,800italic">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oswald:400,300,700">

    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="../css/font-awesome.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <!-- Ladda UI buttons -->
    <link rel="stylesheet" href="../css/ladda-themeless.min.css">
	<script src="../js/spin.min.js"></script>
	<script src="../js/ladda.min.js"></script>

	<script src="../js/libs/jquery-1.10.2.min.js"></script>

    <!-- App CSS -->
    <link rel="stylesheet" href="../css/mvpready-admin.css">
    <link rel="stylesheet" href="../css/mvpready-flat.css">
    <!-- <link href="../css/custom.css" rel="stylesheet">-->

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script language="javascript">

	function selectedReportingYear() {
		document.getElementById("programs-div").style.display = "none";
		document.getElementById("slos-div").style.display = "none";
		document.getElementById("proposed-actions-summary").style.display = "none";
		document.getElementById("tools-button").style.display = "none";
		getPsloSummary(); 
	}

	function selectedReportingPeriod() {
		document.getElementById("programs-div").style.display = "block";
		document.getElementById("slos-div").style.display = "none";
		document.getElementById("proposed-actions-summary").style.display = "none";
		document.getElementById("tools-button").style.display = "none";
		getPsloSummary();
	}
		

    // Call this when we change the program so that we can get a current list of classes
    // based on the selected program
    function getClasses(program) {
		//document.getElementById("sections-div").style.display = "none";
		document.getElementById("slos-div").style.display = "none";
		document.getElementById("proposed-actions-summary").style.display = "none";
		document.getElementById("tools-button").style.display = "none";



    	document.getElementById("classes-div").style.display = "block"; 

    	console.log("Getting classes for "+program+"...");

    	var oReq = new XMLHttpRequest(); 

    	// temporarily set the html of the class field
    	document.getElementById("class").readOnly = true;
    	document.getElementById("class").innerHTML = "Loading...";

	    oReq.onload = function() {
	    	// the data is retured via this.responseText
	    	
	    	// Replace innerhtml with new option fields
	    	
	    	
	    	var newHTML = "loading...";
		document.getElementById("class").innerHTML = "<option>Loading...</option>";
	    	
	    	// Parse the JSON result into a JavaScript object so we can iterate through them
	    	var response = JSON.parse(this.responseText);

	    	// Iterate through our new JS object and add <option> tags to each class so that we can select them
	    	for(var a=0; a<response.length; a++) {
	    		newHTML += "<option>"+response[a]+"</option>"; 
	    	}
			
		// Replace the HTML of the select element dynamically   	
		document.getElementById("class").innerHTML = "<option>-- Select One --</option>";
	    	document.getElementById("class").innerHTML += newHTML;
	    }; 
	    oReq.open("get", "getClasses.php?program="+subject, true); 
	    oReq.send(); 
	};

	// Call this when we change the class so that we can get a current list of sections
    // based on the selected class
    function getSections(classes) {

    	document.getElementById("sections-div").style.display = "block"; 

    	console.log("Getting sections for "+classes+"...");

    	var oReq = new XMLHttpRequest(); 

    	// temporarily set the html of the class field
    	document.getElementById("sections").readOnly = true;
    	document.getElementById("sections").innerHTML = "Loading...";

	    oReq.onload = function() {
	    	// the data is retured via this.responseText
	    	
	    	// Replace innerhtml with new option fields
	    	
	    	
	    	var newHTML = "loading...";
	    	
	    	// Parse the JSON result into a JavaScript object so we can iterate through them
	    	var response = JSON.parse(this.responseText);

	    	// Iterate through our new JS object and add <option> tags to each class so that we can select them
	    	for(var a=0; a<response.length; a++) {
	    		newHTML += "<option>"+response[a]+"</option>"; 
	    	}

			// Replace the HTML of the select element dynamically   	
		document.getElementById("sections").innerHTML = "<option>-- Select One --</option>";	
	    	document.getElementById("sections").innerHTML += newHTML;
	    }; 
	    oReq.open("get", "getSections.php?class="+classes, true); 
	    oReq.send(); 
	};

       
		
      function getPSLOs(program) {

    	document.getElementById("slos-div").style.display = "block"; 
	document.getElementById("proposed-actions-summary").style.display = "block";
	document.getElementById("tools-button").style.display = "block";

    	console.log("Getting PSLOs..."); 

    	var oReq = new XMLHttpRequest(); 

    	// temporarily set the html of the class field
    	//document.getElementById("slos").readOnly = true;
    	//document.getElementById("slos").innerHTML = "Loading...";

	    oReq.onload = function() {
	    	// the data is retured via this.responseText
	    	
	    	// Replace innerhtml with new option fields
	    
	    	var newHTML = "loading...";
	    	
	    	// Parse the JSON result into a JavaScript object so we can iterate through them
	    	var response = JSON.parse(this.responseText);

		newHTML = '<table class="table table-striped table-bordered" style="width: 100%;"><thead><tr><td><strong>PSLO</strong></td><td></td><td><strong>Mapped</strong></td><td><strong>Met Target</strong></td><td><strong>%</strong></td></tr></thead><tbody>';

	    	// Iterate through our new JS object and add <option> tags to each class so that we can select them
	    	for(var a=0; a<response.length; a++) {
			var slonum = a+1;
			var bsAssessed = ["12","10","8","8","10","4","10","6"];
			var bsMet = ["7","9","7","5","6","4","10","5"];
			var percent = (bsMet[a]/bsAssessed[a])*100;
				
	    		newHTML += "<tr><td>"+slonum+"</td><td>"+response[a]+"</td><td><input type=\"text\" name=\"slo"+slonum+"-assessed\" value=\""+bsAssessed[a]+"\"></td><td><input type=\"text\" name=\"slo"+slonum+"-met\" value=\""+bsMet[a]+"\"></td><td><input type=\"text\" value=\""+percent.toFixed(2)+"%\"></td></tr>"; 
	    	}

		newHTML += '</tbody></table>';

		// Replace the HTML of the select element dynamically   		
	    	document.getElementById("slos").innerHTML = newHTML;
	    }; 
	    oReq.open("get", "getPSLOs.php?program="+program, true); 
	    oReq.send(); 
	};

	function getPsloSummary() {
		var classnum = ["100", "120", "130", "140", "150"];
		var classname = "MATH";
		var program = document.getElementById("program").value;
		if(program == "Psychology"){ classname = "PSYC"; }
		if(program == "Physics") { classname = "PHYS"; }

		console.log(classnum[Math.floor(Math.random()*classnum.length)]);
		
		document.getElementById('random1').innerHTML = classname+classnum[Math.floor(Math.random()*classnum.length)];
		document.getElementById('random2').innerHTML = classname+classnum[Math.floor(Math.random()*classnum.length)];
		document.getElementById('random3').innerHTML = classname+classnum[Math.floor(Math.random()*classnum.length)];
		document.getElementById('random4').innerHTML = classname+classnum[Math.floor(Math.random()*classnum.length)];
		document.getElementById('random5').innerHTML = classname+classnum[Math.floor(Math.random()*classnum.length)];
	};
		




    </script>
    

</head>

<body class=" " onload="getPsloSummary()">

    <div id="wrapper">

        <header class="navbar navbar-inverse" role="banner">

            <div class="container">

                <div class="navbar-header">
                    <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <i class="fa fa-cog"></i>
                    </button>

                    <h1 class="toptitle"><img src="../img/slocloud-badge-trans.png" style="height: 35px; width: auto; margin-bottom: 10px" /> <?php echo $config["institutionName"]; ?></h1>

                </div> <!-- /.navbar-header -->

            </div> <!-- /.container -->

        </header>


<div class="row">
    <center><h4>&nbsp;</h4></center>
</div>

  <div class="content">

    <div class="container">

      <div class="layout layout-stack-sm layout-main-left">

        <div class="col-sm-12 col-md-12 layout-main">

          <div class="portlet">

		<?php $page = "programs"; include("navigation.php"); ?>

            <h4 class="portlet-title">
              <?php echo $config["institutionShortName"]; ?> PSLO Report Generator
            </h4>

		<!--<p>This is a demonstration for a PSLO report system written in JavaScript and PHP. Source code is available at Lawsonry.com/slocloud. To send questions, comments, or burritos, contact Aeron Zentner or Jesse Lawson.</p>-->

            <div class="portlet-body">

             <form id="slo-form" class="form-horizontal" method="post" action="saveReport.php">
<fieldset>

<!-- Form Name -->
<?php //<legend>DEMONSTRATION PURPOSES ONLY</legend> ?>

<div id="success-alert"></div>

<!-- Select Basic -->
<div class="control-group">
  <label class="control-label" for="year">Reporting Year</label>
  <div class="controls">
    <select id="year" name="year" class="input-xlarge" onchange="selectedReportingYear(); selectedReportingPeriod(); getPsloSummary();">
      <option>--Select One--</option>
	<option>2013-14</option>
      <option>2012-13</option>
      <option>2011-12</option>
      <option>2010-11</option>
    </select>
  </div>
</div>

<?php /*<!-- Select Basic -->
<div class="control-group">
  <label class="control-label" for="term">Reporting Period</label>
  <div class="controls">
    <select id="term" name="term" class="input-xlarge" onchange="selectedReportingPeriod()">
      <option>--Select One--</option>
	<option>Annual</option>
	<option>Fall</option>
      <option>Winter</option>
      <option>Spring</option>
      <option>Summer</option>
    </select>
  </div>
</div>*/ ?>

<!-- Select Basic -->
<div class="control-group" id="programs-div" style="display:none">
  <label class="control-label" for="program">Program</label>
  <div class="controls">
    <select id="program" name="program" class="input-xlarge" onchange="getPSLOs(this.value); getPsloSummary();">
    <option>--Select One--</option> 
      <?php
	      // Create a dynamic list of programs based on this institution's config file
	      foreach($programsList as $subj) {
			echo "<option name=\"$subj\">$subj</option>";
	      }

	  ?>
      
    </select>
  </div>
</div>

<!-- Select Basic -->
<div class="control-group" id="slos-div" style="display:none">
  <label class="control-label" for="slos">Program SLOs</label>
  <div class="controls">
    <div id="slos" name="slos" class="input-xlarge" readonly>
     	
    </div>
  </div>
</div>

<?php /*<div class="row">
	<div class="col-sm-6">
		<!-- Text input-->
		<div class="control-group">
		  <label class="control-label" for="assessed">Assessed</label>
		  <div class="controls">
		    <input id="assessed" name="assessed" type="text" placeholder="e.g. 45" class="input-xxlarge">
		    
		  </div>
		</div>
	</div>
	<div class="col-sm-6">

		<!-- Text input-->
		<div class="control-group">
		  <label class="control-label" for="mettarget">Met Achievement Target</label>
		  <div class="controls">
		    <input id="mettarget" name="mettarget" type="text" placeholder="e.g. 40" class="input-xxlarge">
		    
		  </div>
		</div>

	</div>
</div>*/ ?>

<!-- Textarea -->
<div class="control-group" id="proposed-actions-summary" style="display:none">
  <label class="control-label" for="proposed">Summary of Mapped PSLO Recommendations</label>
  <div class="controls">                     
    <div id="proposed" name="proposed">

	
	<table class="table striped">
	<thead><tr><td><strong></strong></td><td><strong></strong></td></tr></thead>
	<tbody>
	<tr><td>1.</td><td> Rewrite our lab use requirements given new State standards (<span id="random1"></span>)</td></tr>
	<tr><td>2.</td><td>Free hot dogs for students with 3.8+ GPA (<span id="random2"></span>)</td></tr>
	<tr><td>3.</td><td>Rewrite curriculum for section 3 of open text book. Most of the curriculum we teach from the shared syllabus is out-dated anyway. We should also pull in some of the students to see which parts of the classes they enjoyed the most. (<span id="random3"></span>)</td></tr>
	<tr><td>4.</td><td>Purchase projectors for classroom because the ones we have now are not keeping up with demand. Two burnouts in one day! Switch over to smartboards would be ideal. (<span id="random5"></span>)</td></tr>
	<tr><td>5.</td><td>Offer that <a href="http://lawsonry.com">Jesse Lawson</a> guy a director position at our college (<span id="random4"></span>)</td></tr>
	</tbody>
	</table>

</div>
  </div>
</div>

<!-- Button -->
<div class="btn-group demo-element" id="tools-button" style="display:none">
                <button type="button" class="btn btn-lg btn-primary dropdown-toggle" data-toggle="dropdown">
                Tools <span class="caret"></span>
                </button>

                <ul class="dropdown-menu" role="menu">
		  <li>Export to...</li>
                  <li><a href="javascript:;">Word (*.DOCX)</a></li>
                  <li><a href="javascript:;">Excel (*.XLS)</a></li>
		  <li><a href="javascript:;">Adobe (*.PDF)</a></li>
		  <li><a href="javascript:;">Plain (*.CSV)</a></li>
                  <li class="divider"></li>
                  <li><a href="javascript:;">Print</a></li>
		  <li><a href="javascript:;">Public Link</a></li>
                </ul>
              </div> <!-- /.btn-gruop -->
</fieldset>
</form>



            </div> <!-- /.portlet-body -->

          </div> <!-- /.portlet -->

        </div> <!-- /.layout-main -->



       
      </div> <!-- /.layout -->

    </div> <!-- /.container -->

  </div> <!-- .content -->

  <script language="javascript">
	  
	  // Bind the LaddaUI component to the submit button (aesthetics) and submit the form via Ajax

	  $(function() {
	$('#submit-button').click(function(e){
		document.getElementById("classes-div").style.display = "none"; 
		document.getElementById("sections-div").style.display = "none";
		document.getElementById("slos-div").style.display = "none";

	 	e.preventDefault();
	 	var l = Ladda.create(this);
	 	l.start();
	 	console.log("Preparing to save "+$('#slo-form').serializeArray()); 
	 	$.post("http://slocloud.pragmads.com/api/savereport", 
	 	    { data : $('#slo-form').serialize() },
	 	  function(response){
	 	    console.log("SLO Report has been saved: "+response.message);
	 	  }, "json")
	 	.always(function() { 
			
			l.stop(); // Stop the spinner
			$('#slo-form')[0].reset(); 	// Clear the form
			
			// DEMONSTRATION PURPOSES ONLY: Add the success alert
			document.getElementById("success-alert").innerHTML = '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#" aria-hidden="true">x</a><strong>Success!</strong><br/>Your SLO report has been successfuly saved. Use this form to submit another one (go ahead!), or go to that committee meeting that you have been putting off.';
		
			});
	 	return false;

			});
}); 
  </script>

<?php include("../footer.php"); ?>


