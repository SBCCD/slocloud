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
  var $division = $("#division");
  var $term = $("#term");
  var $subject = $("#subject");
  var $class = $("#class");
  var $section = $("#section");
  var $slos = $("#slos");
  var successTemplate = Handlebars.compile($("#success-template").html());
  var validationTemplate = Handlebars.compile($("#validation-template").html());
  var failureTemplate = Handlebars.compile($("#failure-template").html());
  var tableTemplate = Handlebars.compile($("#slo-table-template").html());
  var rowTemplate = Handlebars.compile($("#slo-row-template").html());
  var $form = $('#slo-form');
  var $alert = $('#alert');

  var getTerms = toggleVisibility(
    $('.fieldset-terms'),
    $('.fieldset-subjects, .fieldset-classes, .fieldset-sections, .fieldset-slos'),
    function() { return $division.val() === ""; },
    null,
    function() { return $term.val() !== ""; },
    function() { $term.change(); }
  );

  var getSubjects = toggleVisibility(
    $('.fieldset-subjects'),
    $('.fieldset-classes, .fieldset-sections, .fieldset-slos'),
    function() { return $term.val() === ""; },
    function() { getSubjectsList($subject, "subjects", {term:$term.val(), division:$division.val()}); }
  );

  var getClasses = toggleVisibility(
    $('.fieldset-classes'),
    $('.fieldset-sections, .fieldset-slos'),
    function() { return $subject.val() === ""; },
    function() { getClassesList($class, "classes", {term: $term.val(), subject: $subject.val()}); }
  );

  var getSections = toggleVisibility(
    $('.fieldset-sections'),
    $('.fieldset-slos'),
    function() { return $class.val() === ""; },
    function() { getSectionsList($section, "sections", {term: $term.val(), "class": $class.val()}); }
  );

  var getSLOs = toggleVisibility(
    $('.fieldset-slos'),
    null,
    function() { return $class.val() === "" || $section.val() === "" || $term.val() === ""; },
    function() {
      $slos.html("<p>Loading...</p>");

      $.when(
        $.getJSON("simpleOutcomes", {"class":$class.val()})
      ).then(function(Outcomes){
          $.getJSON(
            "slos",
            {"class":$class.val(),"section":$section.val(),"term":$term.val()},
            function (response) {
              var rows = "";

              for (var a = 0; a < response.statements.length; a++) {
                rows += rowTemplate({
                  num: a+1,
                  label: "slo" + (a + 1),
                  statement: response.statements[a]['Statement']
                });
              }

              $slos.html(tableTemplate({
                rows: rows,
                assessment: Outcomes['assessment'],
                disableAssessment: Outcomes['assessment'] !== '',
                plos: Outcomes['plos'],
                ilos: Outcomes['ilos']
              }));

              if (response.previous !== false) {
                var $method = $("#method"),
                    $proposed = $("#proposed");
                if ($method.prop("readonly") !== true){
                  $method.val(response.previous.method);
                }
                $proposed.val(response.previous.proposed);
                var $rows = $("tr", $slos);
                for(var i = 0; i < response.previous.statements.length; i++) {
                  var statement = response.previous.statements[i];
                  $rows.each(function(i,row) {
                    if ($("textarea",row).val() === statement.statement) {
                      $("[id$='assessed']",row).val(statement.assessed);
                      $("[id$='target-met']",row).val(statement.targetMet);
                    }
                  })
                }

                $rows.each(function(i,row) {
                  if($("[id$='assessed']", row).val() === "0") {
                    $("button",row).click();
                  }
                })
              }

              $('[data-toggle="tooltip"]').tooltip();

              for (a = 0; a < response.length; a++) {
                if (response[a].hasOwnProperty('PLO')) {
                  $("#slo"+(a+1)+"-plo").val(response[a]['PLO']);
                }
                if (response[a].hasOwnProperty('ILO')) {
                  $("#slo"+(a+1)+"-ilo").val(response[a]['ILO']);
                }
              }
            });
        });
    }
  );

  function validate(event) {
    ensureIsPositiveOrZero($(event.target));
  }

  function submit(e) {
    e.preventDefault();

    var errors = [];
    var simpleInputs = $("input[name*='-assessed']:enabled, input[name*='-target-met']:enabled", $form);
    var simpleInputsAvailable = $("input[name*='-assessed'], input[name*='-target-met']", $form);
    if (simpleInputsAvailable.length > 0 && simpleInputs.length <= 0) {
      errors.push("You must have at least one Statement assessed");
    }
    if (simpleInputs.length > 0) {
      $.each(groupBySloId(simpleInputs), function (num, inputs) {
        if (!inputs) return;
        if (inputs.some(inputIsNotInt)) {
          $.each(inputs, function (i, input) {
            if (inputIsNotInt(input)) {
              var label = $("label[for='" + $(input).attr('id') + "']").text();
              errors.push("Statement " + num + " '" + label + "' must have a numeric value");
            }
          });
        } else if (!inputs.some(inputIsPositiveNonZero)) {
          errors.push("Statement " + num + " must have at least one positive, non-zero value");
        } else if (!isMetMoreThanAssessed(inputs)) {
          errors.push("Statement " + num + " can't have more Met Target than were Assessed");
        }
      });
    }

    if (errors.length > 0) {
      $alert.html(validationTemplate({errors:errors}));
      $('html, body').animate({ scrollTop: $alert.offset().top }, 'slow');
      return;
    }

    postSLOs($form, $alert, successTemplate, failureTemplate);
    return false;
  }

  $slos.on("focus","input[name*='-assessed'], input[name*='-target-met']", recordValue);
  $slos.on("blur","input[name*='-assessed'], input[name*='-target-met']", validate);

  $division.change(getTerms);
  $term.change(getSubjects);
  $subject.change(getClasses);
  $class.change(getSections);
  $section.change(getSLOs);
  $slos.on("click","button.slo-delete", sloDelete);
  $('#submit-button').click(submit);
});