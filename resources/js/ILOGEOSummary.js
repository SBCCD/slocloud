/*

 SLO Cloud - A Cloud-Based SLO Reporting Tool for Higher Education

 This is a peer-reviewed, open-source, public project made possible by the Open Innovation in Higher Education project.

 Copyright (C) 2015 Jesse Lawson, San Bernardino Community College District

 Contributors:
 Jesse Lawson
 Jason Brady

 THIS PROJECT IS LICENSED UNDER GPLv2. YOU MAY COPY, DISTRIBUTE AND MODIFY THE SOFTWARE AS LONG AS YOU TRACK
 CHANGES/DATES OF IN SOURCE FILES AND KEEP ALL MODIFICATIONS UNDER GPL. YOU CAN DISTRIBUTE YOUR APPLICATION USING A
 GPL LIBRARY COMMERCIALLY, BUT YOU MUST ALSO DISCLOSE THE SOURCE CODE.

 GNU General Public License Version 2 Disclaimer:

 ---

 This file is part of SLO Cloud

 SLO Cloud is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
 License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later.

 SLO Cloud is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along with this program; if not, write to the Free
 Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or
 visit http://opensource.org/licenses/GPL-2.0

 ---

 */
$(function () {
  var $year = $("#year");
  var $period = $("#period");
  var $type = $("#type");
  var $slos = $("#slos");

  var getPeriods = toggleVisibility(
    $('.fieldset-periods'),
    $('.fieldset-types, .fieldset-slos'),
    function() { return $year.val() === ""; },
    null,
    function() { return $period.val() !== ""; },
    function() { $period.change(); }
  );

  var getTypes = toggleVisibility(
    $('.fieldset-types'),
    $('.fieldset-slos'),
    function() { return $period.val() === ""; },
    null,
    function() { return $type.val() !== ""; },
    function() { $type.change(); }
  );

  var getSLOs = toggleVisibility(
    $(".fieldset-slos"),
    null,
    function() { return $year.val() === "" || $period.val() === "" || $type.val() === ""; },
    function() {
      var typeName = ($type.val() == "ilo"?"Institution Learning Outcomes":"General Education Outcomes");
      getReportSummary($slos, "iloGeoSummaryData", {year:$year.val(), period:$period.val(), type:$type.val()}, {type: typeName});
    }
  );

  $("[data-action='print']").click(printPage);

  $year.change(getPeriods);
  if ($slos.hasClass("rubric")) {
    $period.change(getTypes);
    $type.change(getSLOs);
  } else {
    $period.change(getSLOs);
  }
});