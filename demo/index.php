<?php
/* Remember that this is going to be called from a sub-folder, so all the URLs need to be changed to add the ../ in front of them */

session_start();

// Each institution will have its own customization file.
require_once("config.php");

global $config;

global $subjectsList; 

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

    // Call this when we change the subject so that we can get a current list of classes
    // based on the selected subject
    function getClasses(subject) {
		document.getElementById("sections-div").style.display = "none";
		document.getElementById("slos-div").style.display = "none";
		document.getElementById("proposed-actions").style.display = "none";


    	document.getElementById("classes-div").style.display = "block"; 

    	console.log("Getting classes for "+subject+"...");

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
	    oReq.open("get", "getClasses.php?subject="+subject, true); 
	    oReq.send(); 
	};

	// Call this when we change the class so that we can get a current list of sections
    // based on the selected class
    function getSections(classes) {

    	document.getElementById("sections-div").style.display = "block"; 
	document.getElementById("slos-div").style.display = "none";
	document.getElementById("proposed-actions").style.display = "none"; 

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

       
		
      function getSLOs(classes) {

    	document.getElementById("slos-div").style.display = "block"; 
	document.getElementById("proposed-actions").style.display = "block"; 

    	console.log("Getting SLOs for "+classes+"...");

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

		newHTML = '<table class="table table-striped table-bordered" style="width: 100%;"><thead><tr><td><strong>SLO#</strong></td><td></td><td><strong>Assessed</strong></td><td><strong>Met Target</strong></td></tr></thead><tbody>';

	    	// Iterate through our new JS object and add <option> tags to each class so that we can select them
	    	for(var a=0; a<response.length; a++) {
			var slonum = a+1;
	    		newHTML += "<tr><td>"+slonum+"</td><td>"+response[a]+"</td><td><input type=\"text\" name=\"slo"+slonum+"-assessed\"></td><td><input type=\"text\" name=\"slo"+slonum+"-met\"></td></tr>"; 
	    	}

		newHTML += '</tbody></table>';

		// Replace the HTML of the select element dynamically   		
	    	document.getElementById("slos").innerHTML = newHTML;
	    }; 
	    oReq.open("get", "getSLOs.php?class="+classes, true); 
	    oReq.send(); 
	};



    </script>
    

</head>

<body class=" ">

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

        <div class="col-sm-7 col-md-8 layout-main">

          <div class="portlet">

            <h4 class="portlet-title">
              <?php echo $config["institutionShortName"]; ?> SLO Reporting: 2014-15
            </h4>

            <div class="portlet-body">

             <form id="slo-form" class="form-horizontal" method="post" action="saveReport.php">
<fieldset>

<!-- Form Name -->
<?php //<legend>DEMONSTRATION PURPOSES ONLY</legend> ?>

<div id="success-alert"></div>


<!-- Select Basic -->
<div class="control-group">
  <label class="control-label" for="term">Term</label>
  <div class="controls">
    <select id="term" name="term" class="input-xlarge">
      <option>Fall</option>
      <option>Winter</option>
      <option>Spring</option>
      <option>Summer</option>
    </select>
  </div>
</div>

<!-- Select Basic -->
<div class="control-group">
  <label class="control-label" for="subject">Subject</label>
  <div class="controls">
    <select id="subject" name="subject" class="input-xlarge" onchange="getClasses(this.value)">
    <option>--Select One--</option> 
      <?php
	      // Create a dynamic list of subjects based on this institution's config file
	      foreach($subjectsList as $subj) {
			echo "<option name=\"$subj\">$subj</option>";
	      }

	  ?>
      
    </select>
  </div>
</div>

<!-- Select Basic -->
<div class="control-group" id="classes-div" style="display:none">
  <label class="control-label" for="class">Class</label>
  <div class="controls">
    <select id="class" name="class" class="input-xlarge" readonly onchange="getSections(this.value)">
	<option>--Select One--</option>
    </select>
  </div>
</div>

<!-- Select Basic -->
<div class="control-group" id="sections-div" style="display:none">
  <label class="control-label" for="sections">Section</label>
  <div class="controls">
    <select id="sections" name="sections" class="input-xlarge" readonly onchange="getSLOs(document.getElementById('class').value)">
     	<option>--Select One--</option>
    </select>
  </div>
</div>

<!-- Select Basic -->
<div class="control-group" id="slos-div" style="display:none">
  <label class="control-label" for="slos">Class SLOs</label>
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

<div id="proposed-actions" style="display:none">
<!-- Textarea -->
<div class="control-group">
  <label class="control-label" for="proposed">Proposed Actions</label>
  <div class="controls">                     
    <textarea id="proposed" name="proposed"></textarea>
  </div>
</div>

<!-- Button -->
<div class="control-group">
  <label class="control-label" for="singlebutton">When you're finished, click the "Save & Submit" button below. If you have a new SLO report to make, the form will clear and you can begin reporting again. When you click the button below, your input is encrypted and saved into the database.</label>
  <div class="controls">
    <button id="submit-button" name="singlebutton" class="submit-button btn btn-primary ladda-button" data-style="expand-right">Save & Submit</button>
  </div>
</div>

</div>

</fieldset>
</form>



            </div> <!-- /.portlet-body -->

          </div> <!-- /.portlet -->

        </div> <!-- /.layout-main -->



        <div class="col-sm-5 col-md-4 layout-sidebar">

          <?php /*<div class="portlet">
            <a href="javascript:;" class="btn btn-primary btn-jumbo btn-block ">New Product</a>
            <br>
            <a href="javascript:;" class="btn btn-secondary btn-lg btn-block ">New Template</a>
        </div> <!-- /.portlet -->*/ ?>

          <h4>Instructions</h4>

          <div class="well">

            <ul class="icons-list text-md">
              <li>
                <i class="icon-li fa fa-check-square text-secondary"></i>
                <strong>Fill out each section</strong>
                <br>
                Completeness is key to creating actionable reporting. Fill out all the fields and be as verbose as necessary.
              </li>

              <li>
                <i class="icon-li fa fa-comments-o text-success"></i>
                <strong>Discuss your ideas</strong>
                <br>
                Every single proposed action is considered, so use this time to get your best ideas on the plate.
              </li>
              <li>
                <i class="icon-li fa fa-institution text-secondary"></i>
                <strong>Keep doing what you're doing</strong>
                <br>
                Faculty are the backbone to any educational institution. Without you, we'd be nothing!
              </li>
            </ul>
          </div> <!-- /.well -->

          <!--<h3></h3>-->

          <h4>Your SLO Reporting Coordinator</h4>

          <div class="well">
              <!--<p>The SLO Reporting Coordinator for <?php echo $config["institutionName"]; ?> is:<br/><br/>
                  <?php echo $config["pocName"]; ?><br/>
                  <?php echo $config["pocEmail"]; ?>
              </p>-->
              SLOCloud&trade; is an open-source higher education innovation project for <?php echo $config["institutionName"]; ?>. If you have any questions about this application or the contents herein, please contact<br/><br/><strong><?php echo $config["pocName"]; ?></strong><br/><a href="mailto:<?php echo $config["pocEmail"]; ?>"><?php echo $config["pocEmail"]; ?></a>
          </div>

        </div> <!-- /.layout-sidebar -->

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
		document.getElementById("proposed-actions").style.display = "none";

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

