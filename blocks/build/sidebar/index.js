/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

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
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
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
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*************************************!*\
  !*** ./blocks/src/sidebar/index.js ***!
  \*************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


(function (wp) {
  const {
    registerPlugin
  } = wp.plugins;
  const {
    PluginDocumentSettingPanel
  } = wp.editPost;
  const {
    Component
  } = wp.element;
  const {
    SelectControl,
    Spinner
  } = wp.components;
  const {
    withSelect,
    withDispatch
  } = wp.data;
  const {
    compose
  } = wp.compose;
  const RestrictionSelectControl = compose(withDispatch(function (dispatch, props) {
    return {
      setMetaValue: function (value) {
        dispatch('core/editor').editPost({
          meta: {
            [props.metaKey]: value
          }
        });
      }
    };
  }), withSelect(function (select, props) {
    return {
      metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey]
    };
  }))(function (props) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
      type: "text",
      label: props.label,
      value: props.metaValue,
      onChange: content => {
        props.setMetaValue(content);
      },
      options: [{
        label: '-- Not Restricted --',
        value: ''
      }].concat(props.products.map(product => {
        return {
          label: product.name,
          value: product.id
        };
      }))
    });
  });

  class RWStripeSidebar extends Component {
    constructor(props) {
      super(props);
      this.state = {
        productList: [],
        loadingProducts: true
      };
    }

    componentDidMount() {
      this.fetchProducts();
    }

    fetchProducts() {
      wp.apiFetch({
        path: 'rwstripe/v1/products'
      }).then(data => {
        this.setState({
          productList: data,
          loadingProducts: false
        });
      });
    }

    render() {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PluginDocumentSettingPanel, {
        name: "rwstripe-sidebar-panel",
        title: "Restrict With Stripe"
      }, this.state.loadingProducts ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Spinner, null) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RestrictionSelectControl, {
        label: "Stripe Product",
        metaKey: "rwstripe_stripe_product_ids",
        products: this.state.productList
      }));
    }

  }

  registerPlugin('mrwstripe-sidebar', {
    icon: 'lock',
    render: RWStripeSidebar
  });
})(window.wp);
})();

/******/ })()
;
//# sourceMappingURL=index.js.map