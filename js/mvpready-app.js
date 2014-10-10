/* ========================================================
*
* MVP Ready - Lightweight & Responsive Admin Template
*
* ========================================================
*
* File: mvpready-app.js
* Version: 1.0.0
* Author: Jumpstart Themes
* Website: http://mvpready.com
*
* ======================================================== */

var mvpready_app = function () {

  "use strict"

  var isLayoutCollapsed = function () {
    return $('.navbar-toggle').css ('display') == 'block'
  }

  var initLayoutToggles = function () {
    $('.navbar-toggle, .mainnav-toggle').click (function (e) {
      $(this).toggleClass ('is-open')
    })
  }

  var initNoticeBar = function () {
    $('.noticebar > li > a').click (function (e) {
      if (isLayoutCollapsed ()) {
        window.location = $(this).prop ('href')
      }
    })
  }

  return {
    init: function () {
      // Layouts
      mvpready_core.navEnhancedInit ()
      mvpready_core.navHoverInit ({ delay: { show: 250, hide: 350 } })
      
      initLayoutToggles ()
      initNoticeBar ()

      // Components
      mvpready_core.initAccordions ()		
      mvpready_core.initFormValidation ()
      mvpready_core.initTooltips ()
      mvpready_core.initBackToTop ()		
      mvpready_core.initLightbox ()
    }
  }

}()

$(function () {
  mvpready_app.init ()
})