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
(function($, window, document, Logger) {
  var flashTemplate = null;
  var $flash = null;
  var errors = [];
  var MaximumErrorCount = 10;
  var errorCount = 0;

  Logger.setLevel(Logger.INFO);
  Logger.setHandler(function (args, context) {
    // Prepend the logger's name to the log message for easy identification.
    if (context.name) {
      args[0] = "[" + context.name + "] " + args[0];
    }
    $.post('jslogger', JSON.stringify({ message: args[0], arguments: Array.prototype.slice.call(args, 1) }));
  });

  // send the error to the server and post in the flash
  // rate limit it per page
  function error(message, obj, e) {
    if (errorCount <= MaximumErrorCount) {
      if (e) {
        Logger.error(message + e.message, obj, e);
      } else {
        Logger.error(message, obj);
      }
    }
    flash(message);
  }

  // posts the error to a dynamic flash error component
  // adds errors to a list if it hasn't closed yet
  function flash(error) {
    // Only pull these when we error. Not always loaded in time.
    if ($flash === null || !$flash.length) $flash = $('#flash');
    if (flashTemplate === null) flashTemplate = Handlebars.compile($("#flash-template").html());
    errors.push(error);
    $flash.html(flashTemplate({errors: errors}));
    $flash.find("button").click(function() { errors = []; });
    $('html, body').animate({ scrollTop: $flash.offset().top }, 'slow');
  }

  // General browser error handler
  window.onerror = function(errorMsg, file, lineNumber) {
    error(errorMsg, {
      errorMessage:   errorMsg,
      file:           file,
      lineNumber:     lineNumber,
      url:            window.location.href,
      ua:             navigator.userAgent
    });
  };

  // This is the default error handler for ajax request.
  $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
    // Extract all the information required to understand.
    var requestResponse = {
      url: ajaxSettings.url,
      method: ajaxSettings.type,
      data: ajaxSettings.data,
      httpStatus: jqXHR.status + "/" + jqXHR.statusText,
      response: {
        "text": jqXHR.responseText,
        "xml": jqXHR.responseXML,
        "json": jqXHR.responseJSON
      },
      error: thrownError || jqXHR.statusText
    };

    var errorMessage = "Ajax Error";

    if (jqXHR.responseJSON && jqXHR.responseJSON.error && jqXHR.responseJSON.error.text) {
      errorMessage = jqXHR.responseJSON.error.text;
    }

    if (window.console && window.console.error) {
      window.console.error(requestResponse);
    }

    // Report it back for fixing
    error(errorMessage, {
      errorMessage: errorMessage,
      details: requestResponse
    })
  });

  // Wraps functions passed into jQuery's ready() with try/catch to report errors
  var origReady = jQuery.fn.ready;
  jQuery.fn.ready = function(fn) {
    return origReady.call(this, function($) {
      try {
        fn($);
      } catch (e) {
        error("$.ready() error", e);
      }
    });
  };

  // override jQuery.fn.bind to wrap every provided function in try/catch
  var jQueryBind = jQuery.fn.bind;
  jQuery.fn.bind = function( type, data, fn ) {
    if ( !fn && data && typeof data == 'function' )
    {
      fn = data;
      data = null;
    }
    if ( fn )
    {
      var origFn = fn;
      fn = function() {
        try
        {
          origFn.apply( this, arguments );
        }
        catch ( ex )
        {
          error("$.bind() error", e);
          throw ex;
        }
      };
    }
    return jQueryBind.call( this, type, data, fn );
  };
})(jQuery, window, document, Logger);