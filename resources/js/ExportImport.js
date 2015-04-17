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
  var failureTemplate = Handlebars.compile($("#failure-template").html());
  var warningTemplate = Handlebars.compile($("#warning-template").html());
  var $alert = $('#alert');
  var $form = $('#export-form');

  function submit(e) {
    e.preventDefault();

    var l = $("#submit-button").ladda();
    l.ladda('start');
    $.fileDownload($form.prop('action'), {
      successCallback: function() {
        l.ladda('stop');
      },
      failCallback: function(responseHtml) {
        try {
          var error = JSON.parse(responseHtml);
          $alert.append(warningTemplate(error));
        } catch (e) {
          $alert.append(failureTemplate());
        } finally {
          l.ladda('stop');
        }
      },
      httpMethod: "POST",
      data: $form.serialize()
    });
    return false;
  }

  $('#submit-button').click(submit);
});