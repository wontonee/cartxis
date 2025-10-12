/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(self, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/app/layouts/demo1.js":
/*!**********************************!*\
  !*** ./src/app/layouts/demo1.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nvar KTLayout = /*#__PURE__*/function () {\n  function KTLayout() {\n    _classCallCheck(this, KTLayout);\n  }\n  return _createClass(KTLayout, null, [{\n    key: \"_isSidebarCollapse\",\n    value: function _isSidebarCollapse() {\n      return document.body.classList.contains('sidebar-collapse');\n    }\n  }, {\n    key: \"_handleMegaMenu\",\n    value: function _handleMegaMenu() {\n      var megaMenuEl = document.querySelector('#mega_menu');\n      if (!megaMenuEl) return;\n      var menu = KTMenu.getInstance(megaMenuEl);\n      menu.disable();\n      setTimeout(function () {\n        menu.enable();\n      }, 500);\n    }\n  }, {\n    key: \"_handleSidebar\",\n    value: function _handleSidebar() {\n      var _this = this;\n      var sidebarToggle = KTToggle.getInstance(this.sidebarToggleEl);\n      sidebarToggle === null || sidebarToggle === void 0 || sidebarToggle.on('toggle', function () {\n        _this.sidebarEl.classList.add('animating');\n        _this._handleMegaMenu();\n        KTDom.transitionEnd(_this.sidebarEl, function () {\n          _this.sidebarEl.classList.remove('animating');\n        });\n      });\n    }\n  }, {\n    key: \"_handleSidebarMenu\",\n    value: function _handleSidebarMenu() {\n      var menuEl = document.querySelector('#sidebar_menu');\n      var scrollableEl = document.querySelector('#sidebar_scrollable');\n      var menuActiveItemEl = menuEl.querySelector('.menu-item.active');\n      if (!menuActiveItemEl || KTDom.isVisibleInParent(menuActiveItemEl, scrollableEl)) {\n        return;\n      }\n      scrollableEl.scroll({\n        top: KTDom.getRelativeTopPosition(menuActiveItemEl, scrollableEl) - 100,\n        behavior: 'instant'\n      });\n    }\n  }, {\n    key: \"init\",\n    value: function init() {\n      this.sidebarEl = document.querySelector('#sidebar');\n      this.sidebarWrapperEl = document.querySelector('#sidebar_wrapper');\n      this.headerEl = document.querySelector('#header');\n      this.sidebarToggleEl = document.querySelector('#sidebar_toggle');\n      if (this.sidebarEl && this.sidebarToggleEl) {\n        this._handleSidebar();\n        this._handleSidebarMenu();\n      }\n    }\n  }, {\n    key: \"isSidebarCollapse\",\n    value: function isSidebarCollapse() {\n      return this._isSidebarCollapse();\n    }\n  }]);\n}();\nKTDom.ready(function () {\n  KTLayout.init();\n});\n/* harmony default export */ __webpack_exports__[\"default\"] = (KTLayout);\n\n//# sourceURL=webpack://metronic-tailwind-html/./src/app/layouts/demo1.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./src/app/layouts/demo1.js"](0, __webpack_exports__, __webpack_require__);
/******/ 	
/******/ 	return __webpack_exports__;
/******/ })()
;
});