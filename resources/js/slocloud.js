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
moment.locale('en');

Handlebars.registerHelper('trimString', function( string, start, end ) {
  var result = string.substring(start, end);
  if( string.length > end ) {
    result += '...';
  }
  return new Handlebars.SafeString(result);
});

(function ($, Logger) {
  // prevent session timeout if they are on one of our pages
  // assumes PHP session.gc_maxlifetime is set to 24 minutes (the default)
  // goes off every ten minutes
  var heart = setInterval(function() {
    $.ajax("beat", {"cache":false,"global":false})
      .fail(function(jqXHR, textstatus, errorThrown) {
        Logger.error(errorThrown || jqXHR.statusText, {
          httpStatus: jqXHR.status + "/" + jqXHR.statusText,
          "XHR":{
            readyState: jqXHR.readyState
          },
          response: {
            "text": jqXHR.responseText,
            "xml": jqXHR.responseXML,
            "json": jqXHR.responseJSON
          },
          error: errorThrown || jqXHR.statusText
        });
        alert(
          "Failed to extend session. This session may timeout in 14 minutes. "+
          "Save your work and navigate to another page to extend this session."
        );
        clearInterval(heart);
      });
  }, 10*60*1000);
})(jQuery, Logger);

/**
 * Looks like PHP doesn't send full ISO 8601 dates, or at least moment doesn't think so.
 * So we specify what PHP sends for ISO 8601
 * @param $dateTime
 * @returns {moment}
 */
function getPHPIso8601WithMoment($dateTime) {
  return moment($dateTime, "YYYY-MM-DD[T]HH:mm:ssZZ");
}

/**
 * get a list of subjects from the server and populate the provided select
 * @param {jQuery} $subject
 * @param {string} url
 * @param {object} params
 */
function getSubjectsList($subject, url, params) {
  $subject.prop("disabled", "disabled").html("<option>Loading...</option>");

  $.getJSON(
    url,
    params,
    function (response) {
      var newHTML = "<option value=\"\">-- Select One --</option>";
      for(var a in response) {
        if (response.hasOwnProperty(a)) {
          newHTML += "<option>" + response[a].id + "</option>";
        }
      }
      $subject.html(newHTML).prop("disabled","");
    }
  );
}

/**
 * get a list of classes from the server and populate the provided select
 * @param {jQuery} $class
 * @param {string} url
 * @param {object} params
 */
function getClassesList($class, url, params) {
  $class.prop("disabled", "disabled").html("<option>Loading...</option>");

  $.getJSON(
    url,
    params,
    function (response) {
      var newHTML = "<option value=\"\">-- Select One --</option>";
      for (var a = 0; a < response.length; a++) {
        newHTML += "<option>" + response[a] + "</option>";
      }
      $class.html(newHTML).prop("disabled", "");
    }
  );
}

/**
 * get a list of sections from the server and populate the provided select
 * @param {jQuery} $section
 * @param {string} url
 * @param {object} params
 */
function getSectionsList($section, url, params) {
  $section.prop("disabled", "disabled").html("<option>Loading...</option>");

  $.getJSON(
    url,
    params,
    function (response) {
      var newHTML = "<option value=\"\">-- Select One --</option>";
      for (var a in response) {
        if (response.hasOwnProperty(a)) {
          var section = response[a];
          var when = getPHPIso8601WithMoment(section['when']);
          newHTML += "<option value=\"" + section['name'] + "\">"
          + section['name'] + " (Submitted: " + (when.isValid() ? when.format("L LT") : "Never") + ")"
          + "</option>";
        }
      }
      $section.html(newHTML).prop("disabled", "");
    }
  );
}

/**
 * get the report summary and populate the provided div
 * @param {jQuery} $slos
 * @param {string} url
 * @param {object} params
 * @param {object} extraTemplateVars extra variables to pass to the handlebar template
 */
function getReportSummary($slos, url, params, extraTemplateVars) {
  var tableTemplate, rowTemplate;
  tableTemplate = Handlebars.compile($("#slo-table-template").html());
  rowTemplate = Handlebars.compile($("#slo-row-template").html());

  $slos.html("<p>Loading...</p>");

  $.getJSON(
    url,
    params,
    function (response) {
      var rows = "";
      var proposed = response['proposed'];
      var statements = response['statements'];
      var reporting = response['reporting'];
      var notReporting = response['notReporting'];

      for (var i = 0; i < statements.length; i++) {
        if ($slos.hasClass("rubric")) {
          rows += rowTemplate({
            num: i+1,
            label: "slo" + (i + 1),
            statement: statements[i]['statement'],
            rubric1: statements[i]['rubric1'],
            rubric2: statements[i]['rubric2'],
            rubric3: statements[i]['rubric3'],
            rubric4: statements[i]['rubric4'],
            threeOrHigher: statements[i]['3-or-higher'],
            percentThreeOrHigher: statements[i]['%-3-or-higher']
          });
        } else {
          rows += rowTemplate({
            num: i+1,
            label: "slo" + (i + 1),
            statement: statements[i]['statement'],
            assessed: statements[i]['assessed'],
            targetMet: statements[i]['met-target'],
            percentTargetMet: statements[i]['%-met-target']
          });
        }
      }

      for (var j = 0; j < reporting.length; j++) {
        reporting[j].when = getPHPIso8601WithMoment(reporting[j].when).format("L LT");
      }

      var templateVars = Object.assign({
        rows: rows,
        proposed: proposed,
        reporting: reporting,
        notReporting: notReporting
      }, extraTemplateVars || {});

      $slos.html(tableTemplate(templateVars));
    });
}

/**
 * post the SLO form to the server and report success or failure
 * @param {jQuery} $form
 * @param {jQuery} $alert
 * @param {Function} successTemplate
 * @param {Function} failureTemplate
 */
function postSLOs($form, $alert, successTemplate, failureTemplate) {
  var l = $("#submit-button").ladda();
  l.ladda('start');
  $.post("", $form.serialize(), null, "json")
    .done(function (message) {
      $(".fieldset-subjects, .fieldset-classes, .fieldset-sections, .fieldset-slos").hide();
      l.ladda('stop'); // Stop the spinner
      $form[0].reset();   // Clear the form
      console.log("Successful save: " + message.success.text);
      $alert.html(successTemplate());
    })
    .fail(function (response) {
      l.ladda('stop');

      try {
        var message = JSON.parse(response.responseText);
        console.log("Error saving form: " + message.error.text);
        if (message.error.errors.length > 0) {
          console.log("Reasons given:\n  " + message.error.errors.join("\n  "));
        }

        $alert.html(failureTemplate({error: message.error}));
      } catch (e) {
        $alert.html(failureTemplate({error: e.message}));
      }
      $('html, body').animate({scrollTop: $alert.offset().top}, 'slow');
    });
}

function printPage() { window.print(); return false; }

/**
 * used to toggle the visibility of our form controls based on the values of other form controls
 * @param {jQuery} toToggle the jQuery elements to toggle viability of
 * @param {jQuery|null} toAlwaysHide if not null, always hide these jQuery elements. They should not be needed in the form yet.
 * @param {Function} hideTest returns true if we should hide, false if we should show
 * @param {Function|null} ifShown run this function if we show
 * @param {Function|null} nextTest returns true if we should run the next function
 * @param {Function|null} next run this function if nextTest returns true.
 * @returns {Function}
 */
function toggleVisibility(toToggle, toAlwaysHide, hideTest, ifShown, nextTest, next) {
  return function() {
    if (toAlwaysHide !== null && typeof toAlwaysHide === "object") {
      toAlwaysHide.hide();
    }
    if (hideTest()) {
      toToggle.hide();
      return;
    }
    toToggle.show();

    if (typeof ifShown === "function") ifShown();

    if (typeof nextTest === "function" && nextTest()) {
      next();
    }
  };
}

function sloDelete(event) {
  var el = $(event.target).closest('button');
  var row = el.parents().filter('tr');
  var sloClass = row.attr('class').split(' ')
    .filter(function (_class) { return _class.includes('row-slo'); })[0];

  $(row).toggleClass('slo-disabled');

  $('span',el).toggleClass('glyphicon-remove').toggleClass('glyphicon-plus');

  var newTitle = "";
  if ($('span',el).hasClass('glyphicon-remove')) {
    newTitle = el.data('disable-label');
  } else {
    newTitle = el.data('enable-label');
  }
  updateTooltip(el, newTitle);

  $('input, select, textarea', $('tr.'+sloClass)).prop("disabled", function(i, val) {
    return !val;
  });
}

function recordValue(event) {
  var el = $(event.target);
  el.data('original',el.val());
}

function updateTooltipFromTitle(event) {
  var el = $(event.target);
  var newTitle = el.find(':selected').attr('title');
  updateTooltip(el, newTitle);
}

function updateTooltip(el, newTitle) {
  el = $(el);
  el.attr("aria-label", newTitle)
    .attr('title', newTitle)
    .tooltip('fixTitle')
    .data('bs.tooltip');
  $("#"+el.attr('aria-describedby'))
    .find(".tooltip-inner")
    .text(newTitle);
}

function textToInt(el) {
  return parseInt($(el).text());
}

function textToInts(els) {
  return elsMap(els,textToInt);
}

function inputsToInts(els) {
  return elsMap(els,function(el){return parseInt($(el).val());});
}

function ensureIsPositiveOrZero(el) {
  if(!inputIsPositiveOrZero(el)) {
    el.val(el.data('original'));
  } else {
    // If they try something that looks like a number, it will still parse (incorrectly)
    // In that case, just save it
    el.val(parseInt(el.val()));
  }
}

function inputIsPositiveOrZero(el) {
  var val = parseInt($(el).val());
  return !Number.isNaN(val) && val >= 0;
}

function inputIsPositiveNonZero(el) {
  var val = parseInt($(el).val());
  return !Number.isNaN(val) && val > 0;
}

function inputIsInt(el) {
  return !Number.isNaN(parseInt($(el).val()));
}

function inputIsNotInt(el) {
  return !inputIsInt(el);
}

function elsMap(els,fn) {
  return els.toArray().map(fn);
}

function sum(array) {
  return array.reduce(function(a,b) {return a+b;});
}

function groupBySloId(els) {
  var items = [];
  $.each(els, function() {
    var id = $(this).attr('id');
    var part = id.split('-')[0];
    var num = parseInt(part.replace(/[^\d]/g,''));
    if (!items[num]) {items[num] = [];}
    items[num].push(this);
  });
  return items;
}

function isMetMoreThanAssessed(inputs) {
  var assessed = 0;
  var met = 0;

  $.each(inputs, function (index, input) {
    var id = $(input).attr('id');
    var val = parseInt($(input).val());
    if (id.includes("-assessed")) {
      assessed = val;
    } else {
      met = val;
    }
  });

  return met <= assessed;
}