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
$(function(){
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

  var getSubjects = toggleVisibility(
    $('.fieldset-subjects'),
    $('.fieldset-classes, .fieldset-sections, .fieldset-slos'),
    function() { return $term.val() === ""; },
    function() {
      $.scrollTo($('.fieldset-subjects'), 800);
      getSubjectsList($subject, "subjects", {term:$term.val()});
    }
  );

  var getClasses = toggleVisibility(
    $('.fieldset-classes'),
    $('.fieldset-sections, .fieldset-slos'),
    function() { return $subject.val() === ""; },
    function() {
      $.scrollTo($('.fieldset-classes'), 800);
      getClassesList($class, "classes", {term:$term.val(), subject:$subject.val()});
    }
  );

  var getSections = toggleVisibility(
    $('.fieldset-sections'),
    $('.fieldset-slos'),
    function() { return $class.val() === ""; },
    function() {
      $.scrollTo($('.fieldset-sections'), 800);
      getSectionsList($section, "sections", {term:$term.val(),"class":$class.val()});
    }
  );

  var getSLOs = toggleVisibility(
    $('.fieldset-slos'),
    null,
    function() { return $class.val() === "" || $section.val() === "" || $term.val() === ""; },
    function() {
      $('[data-toggle="tooltip"]').tooltip('destroy');
      $slos.html("<p>Loading...</p>");

      $.when(
        $.getJSON("plos", {program:$subject.val()})
      ).then(function(PLOs){
          $.getJSON(
            "slos",
            {"class":$class.val(),"section":$section.val(),"term":$term.val()},
            function (response) {
              var rows = "";

              for (var a = 0; a < response.statements.length; a++) {
                rows += rowTemplate({
                  num: a+1,
                  label: "slo" + (a + 1),
                  plos: PLOs,
                  statement: response.statements[a]['Statement']});
              }

              $slos.html(tableTemplate({rows: rows}));

              if (response.previous !== false) {
                var $proposed = $("#proposed");
                $proposed.val(response.previous.proposed);
                for(var i = 0; i < response.previous.statements.length; i++) {
                  var statement = response.previous.statements[i];
                  var found = false;
                  $("tr",$("#slos")).each(function(i,el) {
                    if ($("textarea",el).val() === statement.statement) {
                      found = true;
                      var num = textToInt($('.slo-number',el));
                      $("[id$='"+num+"-met']",$slos).val(statement.met);
                      $("[id$='"+num+"-plo']",$slos).val(statement.plo);
                      $("[id$='"+num+"-geo']",$slos).val(statement.geo);
                      $("[id$='"+num+"-ilo']",$slos).val(statement.ilo);
                      $("[id$='"+num+"-rubric-1']",$slos).val(statement.rubric1);
                      $("[id$='"+num+"-rubric-2']",$slos).val(statement.rubric2);
                      $("[id$='"+num+"-rubric-3']",$slos).val(statement.rubric3);
                      $("[id$='"+num+"-rubric-4']",$slos).val(statement.rubric4);
                    }
                  });
                  if (!found) {
                    addStatement(null, statement.statement, PLOs, (function(newValues){
                      return function(newRow){
                        $("[id$='met']",newRow).val(newValues.met);
                        $("[id$='plo']",newRow).val(newValues.plo);
                        $("[id$='geo']",newRow).val(newValues.geo);
                        $("[id$='ilo']",newRow).val(newValues.ilo);
                        $("[id$='rubric-1']",newRow).val(newValues.rubric1);
                        $("[id$='rubric-2']",newRow).val(newValues.rubric2);
                        $("[id$='rubric-3']",newRow).val(newValues.rubric3);
                        $("[id$='rubric-4']",newRow).val(newValues.rubric4);
                      };
                    }(statement)));
                  }
                }
              }

              $("input[name*='-rubric-1']").data('original', 0).blur();

              $('[data-toggle="tooltip"]').tooltip();

              for (a = 0; a < response.statements.length; a++) {
                if (response.statements[a].hasOwnProperty('GEO') && response.statements[a]['GEO'] !== '') {
                  $("#slo"+(a+1)+"-geo",$slos).val(response.statements[a]['GEO']).change();
                }
                if (response.statements[a].hasOwnProperty('PLO') && response.statements[a]['PLO'] !== '') {
                  $("#slo"+(a+1)+"-plo",$slos).val(response.statements[a]['PLO']).change();
                }
                if (response.statements[a].hasOwnProperty('ILO') && response.statements[a]['ILO'] !== '') {
                  $("#slo"+(a+1)+"-ilo",$slos).val(response.statements[a]['ILO']).change();
                }
              }
              $.scrollTo($('.fieldset-slos'), 800);
            });
        });
    }
  );

  function validateAndUpdate(event) {
    ensureIsPositiveOrZero($(event.target));

    var label = event.target.name.split("-")[0];
    updateTotal(label);
    updateSufficient(label);
  }

  function updateTotal(label) {
    var totalEl = $("#"+label+"-total");
    var rubrics = inputsToInts($("input[name*='"+label+"-rubric-']"));
    totalEl.text(rubrics.reduce(function(a,b){return a+b;}));
  }

  function updateSufficient(label) {
    var sufficientEl = $("#"+label+"-sufficient");
    var rubrics = inputsToInts($("input[name*='"+label+"-rubric-']"));
    var total = sum(rubrics);
    var passed = rubrics[2]+rubrics[3];
    if(total === 0){
      sufficientEl.text(0);
    }
    else {
      sufficientEl.text(((passed/total)*100).toFixed(1));
    }
  }

  function addStatement(_, statement, PLOs, callback) {
    statement = statement || "";
    callback = callback || function(){};
    var subject = $("#subject").val();
    var num = textToInts($('.slo-number',$slos)).max() + 1;
    if (num === Number.NEGATIVE_INFINITY) num = 1;
    if (PLOs) {
      update(PLOs);
    } else {
      $.when(
        $.getJSON("plos", {program:$subject.val()})
      ).then(function(PLOs) {
          update(PLOs);
        });
    }

    function update(PLOs){
      $slos.find('tbody').append(rowTemplate({
        num: num,
        label: "slo" + num,
        plos: PLOs,
        statement: statement
      })).find('[data-toggle="tooltip"]').tooltip();
      callback($('.row-slo'+num));
    }
  }

  function submit(e) {
    e.preventDefault();

    var errors = [];
    var rubricInputs = $("input[name*='-rubric-']:enabled", $form);
    var rubricInputsAvailable = $("input[name*='-rubric-i']", $form);
    if (rubricInputsAvailable.toArray().length > 0 && rubricInputs.toArray().length <= 0) {
      errors.push("You must have at least one SLO assessed");
    }

    $("select:enabled, textarea:enabled", $form).each(function() {
      var el = $(this);
      if (el.val() === '') {
        var label = $("label[for='" + el.attr('id') + "']").text();
        errors.push("'" + label + "' must have a value");
      }
    });

    $.each(groupBySloId(rubricInputs), function(num, rubrics) {
      if (!rubrics) return;
      if (!rubrics.some(inputIsPositiveNonZero)) {
        errors.push("Statement " + num + " Rubrics must have a nonzero value");
      }
    });

    if (errors.length > 0) {
      $alert.html(validationTemplate({errors:errors}));
      $('html, body').animate({ scrollTop: $alert.offset().top }, 'slow');
      return;
    }

    postSLOs($form, $alert, successTemplate, failureTemplate);
    return false;
  }

  $slos.on("focus","input[name*='-rubric-']", recordValue);
  $slos.on("blur","input[name*='-rubric-']", validateAndUpdate);
  $slos.on("change","select[name*='-geo'], select[name*='-ilo'], select[name*='-plo']", updateTooltipFromTitle);
  $slos.on("click","button.slo-delete", sloDelete);
  $slos.on("click","button.slo-add", addStatement);
  $term.change(getSubjects);
  $subject.change(getClasses);
  $class.change(getSections);
  $section.change(getSLOs);
  $('#submit-button').click(submit);
});
