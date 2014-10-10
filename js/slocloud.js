
    // Call this when we change the subject so that we can get a current list of classes
    // based on the selected subject
    function getClasses(subject) {
		document.getElementById("sections-div").style.display = "none";
		document.getElementById("slos-div").style.display = "none";


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



