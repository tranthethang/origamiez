/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/script.js":
/*!*****************************!*\
  !*** ./assets/js/script.js ***!
  \*****************************/
/***/ (() => {

jQuery(document).ready(function ($) {
  'use strict';

  Origamier.initMainMenu();
  Origamier.initCarouselPostRelated();
  Origamier.initResponsive();
  Origamier.initCarouselPostsSlider();
  Origamier.convertFlatMenuToDropdown();
  Origamier.fixGalleryPopupMissingTitle();
});
jQuery(window).load(function ($) {
  'use strict';

  Origamier.initImageEffect();
  Origamier.initMobileMenu();
  Origamier.initLightboxEffect();
});
var Origamier = {
  fixGalleryPopupMissingTitle: function fixGalleryPopupMissingTitle() {
    var items = jQuery('.gallery-item');
    if (items.length) {
      jQuery.each(items, function () {
        var caption = jQuery.trim(jQuery(this).find('.gallery-caption').html());
        if (caption !== undefined) {
          jQuery(this).find('img').attr('title', caption);
        }
      });
    }
  },
  initCarouselPostsSlider: function initCarouselPostsSlider() {
    var carousels = jQuery('.origamiez-widget-posts-slider .owl-carousel');
    if (0 < carousels.length) {
      jQuery.each(carousels, function () {
        var owl = jQuery(this);
        owl.owlCarousel({
          items: 1,
          nav: false,
          pagination: true,
          autoplaySpeed: 700,
          loop: true,
          margin: 5,
          responsive: {
            0: {
              items: 1
            },
            320: {
              items: 1
            },
            480: {
              items: 1
            },
            640: {
              items: 1
            },
            720: {
              items: 1
            },
            1024: {
              items: 1
            }
          }
        });
      });
    }
  },
  initMainMenu: function initMainMenu() {
    var mainMenu = jQuery('#main-menu');
    if (mainMenu.length) {
      mainMenu.superfish({
        cssArrows: false,
        delay: 0,
        speed: 300,
        speedOut: 300,
        animation: {
          opacity: 'show',
          marginTop: 0
        },
        animationOut: {
          opacity: 'hide',
          marginTop: 40
        },
        disableHI: true
      });
    }
    var mobileMenuHandle = jQuery('#origamiez-mobile-menu-icon');
  },
  initResponsive: function initResponsive() {
    jQuery("#origamiez-post-wrap, .post, .origamiez-widget-content").fitVids();
  },
  initCarouselPostRelated: function initCarouselPostRelated() {
    var post_related = jQuery('#origamiez-post-related .owl-carousel');
    if (0 < post_related.length) {
      var args = {
        items: 3,
        navigation: false,
        pagination: false,
        slideSpeed: 700,
        itemsDesktop: [1199, 3],
        itemsDesktopSmall: [979, 3],
        itemsTablet: [768, 2],
        itemsTabletSmall: [640, 2]
      };
      if (jQuery('body').hasClass('origamiez-layout-full-width')) {
        args.items = 4;
        args.itemsDesktop = [1199, 4];
        args.itemsDesktopSmall = [979, 4];
        args.itemsTablet = [768, 3];
        args.itemsTabletSmall = [640, 2];
      }
      post_related.owlCarousel(args);
      var widget = post_related.parents('.widget');
      widget.find('.fa-angle-left').click(function () {
        post_related.trigger('owl.prev');
      });
      widget.find('.fa-angle-right').click(function () {
        post_related.trigger('owl.next');
      });
      var figure = post_related.find('figure');
      figure.hover(function () {
        var figcaption = jQuery(this).find('figcaption');
        figcaption.stop().transition({
          bottom: 15
        });
      });
      figure.mouseleave(function () {
        var figcaption = jQuery(this).find('figcaption');
        figcaption.stop().transition({
          bottom: 0
        });
      });
    }
  },
  initMobileMenu: function initMobileMenu() {
    jQuery('#mobile-menu').navgoco({
      caretHtml: '<span class="origamiez-mobile-caret fa fa-chevron-circle-down"></span>',
      accordion: false,
      openClass: 'open',
      save: true,
      slide: {
        duration: 400,
        easing: 'swing'
      }
    });
    jQuery('#origamiez-mobile-wrap').on('click', function () {
      jQuery('nav#origamiez-mobile-nav').toggleClass('is-active');
    });
    jQuery('#origmiez-mobile-nav__toggle').on('click', function () {
      jQuery('nav#origamiez-mobile-nav').removeClass('is-active');
    });
  },
  initImageEffect: function initImageEffect() {
    var images = jQuery(".image-effect, .image-overlay");
    if (0 < images.length) {
      jQuery('.image-effect').hover(function () {
        jQuery(this).stop().transition({
          scale: [1.1, 1.1]
        });
      }).mouseleave(function () {
        jQuery(this).stop().transition({
          scale: [1.0, 1.0]
        });
      });
      jQuery(".image-overlay").hover(function () {
        if (OrigamierUtil.getViewportWidth() >= 768) {
          jQuery(this).find('.overlay').stop().transition({
            opacity: 1
          });
          jQuery(this).find('.fa').stop().css('marginLeft', '-22.5px').css('left', '50%').transition({
            opacity: 1
          });
          jQuery(this).find('.overlay-link').stop().css('marginRight', '-22.5px').css('right', '50%').transition({
            opacity: 1
          });
        }
      }).mouseleave(function () {
        if (OrigamierUtil.getViewportWidth() >= 768) {
          jQuery(this).find('.overlay').stop().transition({
            opacity: [0]
          });
          jQuery(this).find('.fa').stop().transition({
            left: '100%',
            marginLeft: 0
          });
          jQuery(this).find('.overlay-link').stop().transition({
            right: '100%',
            marginRight: 0
          });
        }
      });
    }
  },
  initLightboxEffect: function initLightboxEffect() {
    if (1 === parseInt(origamiez_vars.config.is_enable_lightbox)) {
      var blogposts = jQuery('#origamiez-blogposts .entry-thumb');
      var gallery = {};
      if (1 === parseInt(origamiez_vars.config.is_use_gallery_popup)) {
        gallery = jQuery('#origamiez-post-wrap .gallery');
      }
      var photos = jQuery('.origamiez-widget-posts-by-photos .origamiez-photos-wrap');
      var media = jQuery('.origamiez-lighbox');
      if (0 < blogposts.length || 0 < gallery.length || 0 < photos.length || 0 < media.length) {
        var args = {
          baseZIndex: 10001,
          useBodyOverflow: false,
          usePopupEasyClose: false,
          overlayColor: '#1f2328',
          overlayOpacity: 0.65,
          usePopupDefaultStyling: false,
          usePopupCaption: true,
          popupLoaderText: '',
          usePopupNav: false,
          popupBlankCaptionText: false
        };
        var args_hidePopupNav = args;
        args_hidePopupNav.usePopupNav = false;
        if (0 < blogposts.length) {
          blogposts.poptrox(args_hidePopupNav);
        }
        if (0 < media.length) {
          media.poptrox(args_hidePopupNav);
        }
        var args_usePopupNav = args;
        args_usePopupNav.usePopupNav = true;
        if (0 < gallery.length) {
          gallery.poptrox(args_usePopupNav);
        }
        if (0 < photos.length) {
          photos.poptrox(args_usePopupNav);
        }
      }
    }
  },
  convertFlatMenuToDropdown: function convertFlatMenuToDropdown() {
    if (1 === parseInt(origamiez_vars.config.is_enable_convert_flat_menus)) {
      var topNav = jQuery('#top-nav');
      if (topNav.length) {
        Origamier.createMobileMenu(topNav, 'top-mobile-menu', 'show-only-screen-and-max-width-639');
      }
      var bottomNav = jQuery('#bottom-nav');
      if (bottomNav.length) {
        Origamier.createMobileMenu(bottomNav, 'bottom-mobile-menu', 'show-only-screen-and-max-width-639');
      }
    }
  },
  createMobileMenu: function createMobileMenu(menu_id, mobile_menu_id, mobile_menu_class) {
    jQuery('<select />').appendTo(menu_id);
    jQuery(menu_id).find('select').first().attr('id', mobile_menu_id).attr('class', mobile_menu_class);
    jQuery(menu_id).find('a').each(function () {
      var depth, el, i, selected, space;
      el = jQuery(this);
      selected = '';
      if (el.parent().hasClass('current-menu-item') === true) {
        selected = 'selected=\'selected\'';
      }
      depth = el.parents('ul').size();
      space = '';
      if (depth > 1) {
        i = 1;
        while (i < depth) {
          space += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
          i++;
        }
      }
      jQuery('<option ' + selected + ' value=\'' + el.attr('href') + '\'>' + space + el.text() + '</option>').appendTo(jQuery(menu_id).find('select').first());
    });
    jQuery(menu_id).find('select').first().change(function () {
      window.location = jQuery(this).find('option:selected').val();
    });
  }
};
var OrigamierUtil = {
  getViewport: function getViewport(w) {
    w = w || window;
    if (w.innerWidth !== null) return {
      w: w.innerWidth,
      h: w.innerHeight
    };
    var d = w.document;
    if (document.compatMode === "CSS1Compat") return {
      w: d.documentElement.clientWidth,
      h: d.documentElement.clientHeight
    };
    return {
      w: d.body.clientWidth,
      h: d.body.clientHeight
    };
  },
  getViewportWidth: function getViewportWidth(w) {
    var viewport = OrigamierUtil.getViewport(w);
    return viewport.w;
  }
};

/***/ }),

/***/ "./node_modules/bootstrap/scss/bootstrap.scss":
/*!****************************************************!*\
  !*** ./node_modules/bootstrap/scss/bootstrap.scss ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./node_modules/@fortawesome/fontawesome-free/css/all.css":
/*!****************************************************************!*\
  !*** ./node_modules/@fortawesome/fontawesome-free/css/all.css ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./node_modules/owl.carousel/dist/assets/owl.carousel.css":
/*!****************************************************************!*\
  !*** ./node_modules/owl.carousel/dist/assets/owl.carousel.css ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./node_modules/owl.carousel/dist/assets/owl.theme.default.css":
/*!*********************************************************************!*\
  !*** ./node_modules/owl.carousel/dist/assets/owl.theme.default.css ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./node_modules/superfish/dist/css/superfish.css":
/*!*******************************************************!*\
  !*** ./node_modules/superfish/dist/css/superfish.css ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./style.scss":
/*!********************!*\
  !*** ./style.scss ***!
  \********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/sass/responsive.scss":
/*!*************************************!*\
  !*** ./assets/sass/responsive.scss ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/sass/typography/default.scss":
/*!*********************************************!*\
  !*** ./assets/sass/typography/default.scss ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/sass/skin/default.scss":
/*!***************************************!*\
  !*** ./assets/sass/skin/default.scss ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/sass/skin/custom.scss":
/*!**************************************!*\
  !*** ./assets/sass/skin/custom.scss ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/script": 0,
/******/ 			"skin/custom": 0,
/******/ 			"skin/default": 0,
/******/ 			"typography/default": 0,
/******/ 			"css/responsive": 0,
/******/ 			"style": 0,
/******/ 			"css/superfish": 0,
/******/ 			"css/owl.theme.default": 0,
/******/ 			"css/owl.carousel": 0,
/******/ 			"css/fontawesome": 0,
/******/ 			"css/bootstrap": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkmedmag"] = self["webpackChunkmedmag"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./assets/js/script.js")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./style.scss")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./assets/sass/responsive.scss")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./assets/sass/typography/default.scss")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./assets/sass/skin/default.scss")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./assets/sass/skin/custom.scss")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./node_modules/bootstrap/scss/bootstrap.scss")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./node_modules/@fortawesome/fontawesome-free/css/all.css")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./node_modules/owl.carousel/dist/assets/owl.carousel.css")))
/******/ 	__webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./node_modules/owl.carousel/dist/assets/owl.theme.default.css")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["skin/custom","skin/default","typography/default","css/responsive","style","css/superfish","css/owl.theme.default","css/owl.carousel","css/fontawesome","css/bootstrap"], () => (__webpack_require__("./node_modules/superfish/dist/css/superfish.css")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;