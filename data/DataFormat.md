Data Format
=========================

The data in this directory will be moved into the database in the future. Right now they are CSV files
for allowing SBCCD to quickly start using it.

**All files must be in ASCII/windows-1252 encoding. This is the default output by Excel.**

**The first row must always be column headers. They must match one of those below (without quotes), case sensitively, or 
they will be ignored.**

## Data Elements

* "Term" : Term identifier. In the form YYYYTT where YYYY is the four digit year and TT is one of
    * SM = Summer
    * FA = Fall
    * SP = Spring
* "Subject": Subject identifier. Must be unique among all subjects.
* "Course": Course identifier. Must be unique among all courses.
* "Section": Section identifier. Must be unique among all sections. 

## Sections.csv - Sections file

This file contains all sections that should have SLOs reported for the term. This file is usually created using
information from the Student Information System.

* "Term": Term for the section. Required.
* "Subject": Subject or Department (depending on model/display). Required.
* "Course": Course for the section. Required.
* "Section": Section identifier. Required.
* "Faculty": Faculty that taught the course, separated by pipes (|). Required for Simple model, only passed through to 
  exports.
* "Departments": Departments (separated by pipes (|)) responsible for the section. Required for Simple model. Used to 
  filter by division.

## Sections.Manual.csv - Manually defined sections

This optional file allows the definition of "dummy courses and sections". This allows tracking data that isn't directly 
associated with a section but is required for evaluating programs. One example is the ACS General Chemistry exam.

Same column format as the Sections file, but the Term and Faculty columns are left blank.

## CSLOs.csv - SLO Statement file

This file contains all the SLO Statements for each course. In the case of Rubric, they are the defaults used in the form.
They can be changed and new ones added while filling out the form. For Simple, these are the only ones allowed and they
must be defined here first. This does not effect imports.

* "Course": The course identifier to match in the Sections file. Required.
* "SLO Statement": The statement to use for that course. Order in the file is preserved for display. Required.
* "GEO": Automatic GEO mapping applied per statement for Rubric model. Optional.
* "ILO": Automatic ILO mapping applied per statement for Rubric model. Optional.
* "PLO": Automatic PLO mapping applied per statement for Rubric model. Optional.

## PLOs.csv - Program Level Outcome (PLO) file

This file contains outcome statements for programs. These are used for the Program Summary reports. They are mapped
to SLOs differently, depending on the model:

* For the Rubric model, they are specified per SLO statement. The defaults are in the CSLOs file and they can be
  selected on the form.
  
* For the Simple model, they are specified per course. The use the numeric columns below to specify course identifiers.

Columns are:

* "PLO No.": PLO identifier. Must be unique per PLO. Required.
* "Program": Unique Program name. Shown in reports. Required.
* "PLO Statement": The statement to use for that PLO. Required. Order in the file is preserved for display.
* Numeric columns: The header is added to let the code know how many columns there is in the file. All numeric columns
  are used to specify courses to map PLOs to for the Simple model, one per column. If there aren't enough numeric
  columns, add more numeric columns as needed. Required only for the Simple model.
  
## GEOs.csv - General Educational Outcome (GEO) file

This file contains outcome statements for general educational requirements. These are used for the GEO reports. They
are mapped to SLOs differently, depending on the model:

* For the Rubric model, they are specified per SLO statement. The defaults are in the CSLOs file and they can be
  selected on the form.
  
* For the Simple model, they are not in use.

Columns are:

* "GEO No.": GEO identifier. Must be unique per GEO. Required.
* "GEO Name": Unique GEO name. Shown in reports. Required.
* "GEO Statement": The statement to use for that GEO. Required. Order in the file is preserved for display.

## ILOs.csv - Institution Learning Outcomes (ILO) file

This file contains outcome statements for institutional learning outcomes. These are used for the ILO/CC reports. They
are mapped to SLOs differently, depending on the model:

* For the Rubric model, they are specified per SLO statement. The defaults are in the CSLOs file and they can be
  selected on the form.
  
* For the Simple model, they are specified per course. The use the numeric columns below to specify course identifiers.
  
* "ILO No.": ILO identifier. Must be unique per ILO. Required.
* "ILO Name": Unique ILO name. Shown in reports. Required.
* "ILO Statement": The statement to use for that ILO. Required. Order in the file is preserved for display.
* Numeric columns: The header is added to let the code know how many columns there is in the file. All numeric columns
  are used to specify courses to map ILOs to for the Simple model, one per column. If there aren't enough numeric
  columns, add more numeric columns as needed. Required only for the Simple model.
  
## Simple Model only files

### Assessments.csv - Assessments file

This optional file specifies the filled in value for assessments that were pre-determined by the department. When specified here,
they are **not** changeable on the Simple SLO form.

* "Course": Course identifier. Required.
* "Assessment": Assessment to use for the Simple SLO form. Required.

### Division.csv - Division file

This file lists all possible divisions for use in drop downs. This is usually pulled from the Student Information
System.

* "Division ID": Unique identifier for the division. Required.
* "Division Description": Friendly name for the division. Displayed value on drop downs. Required.

## DepartmentDivisionMap.csv - Department to Division map file

This file lists which division a department is mapped to.  This is used to map sections to divisions to allow
filtering reports by division.

* "Dept ID": Department identifier. From the Sections file.
* "Division": Division identifier. From the Division file.