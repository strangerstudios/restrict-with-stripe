/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/src/settings/index.js":
/*!**************************************!*\
  !*** ./blocks/src/settings/index.js ***!
  \**************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./blocks/src/settings/style.scss");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _assets_restrict_with_stripe_png__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../assets/restrict-with-stripe.png */ "./blocks/assets/restrict-with-stripe.png");
/* harmony import */ var _assets_rwstripe_edit_post_page_metabox_png__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../assets/rwstripe-edit-post-page-metabox.png */ "./blocks/assets/rwstripe-edit-post-page-metabox.png");
/* harmony import */ var _assets_rwstripe_edit_term_restrict_setting_png__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../../assets/rwstripe-edit-term-restrict-setting.png */ "./blocks/assets/rwstripe-edit-term-restrict-setting.png");
/* harmony import */ var _assets_rwstripe_customer_portal_nav_menu_item_png__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../../assets/rwstripe-customer-portal-nav-menu-item.png */ "./blocks/assets/rwstripe-customer-portal-nav-menu-item.png");
/* harmony import */ var _assets_rwstripe_customer_portal_block_png__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../../assets/rwstripe-customer-portal-block.png */ "./blocks/assets/rwstripe-customer-portal-block.png");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @wordpress/notices */ "@wordpress/notices");
/* harmony import */ var _wordpress_notices__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_wordpress_notices__WEBPACK_IMPORTED_MODULE_11__);














const Notices = () => {
  const notices = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_9__.useSelect)(select => select(_wordpress_notices__WEBPACK_IMPORTED_MODULE_11__.store).getNotices().filter(notice => notice.type === 'snackbar'), []);
  const {
    removeNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_9__.useDispatch)(_wordpress_notices__WEBPACK_IMPORTED_MODULE_11__.store);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.SnackbarList, {
    className: "edit-site-notices",
    notices: notices,
    onRemove: removeNotice
  });
};

class App extends _wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Component {
  constructor() {
    super(...arguments);
    this.state = {
      rwstripe_show_excerpts: true,
      rwstripe_collect_password: true,
      isAPILoaded: false,
      productList: [],
      areProductsLoaded: false
    };
  }

  componentDidMount() {
    // Load site settings.
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/wp/v2/settings'
    }).then(settings => {
      console.log(settings);

      if (settings.hasOwnProperty('rwstripe_show_excerpts')) {
        this.setState({
          rwstripe_show_excerpts: settings.rwstripe_show_excerpts,
          rwstripe_collect_password: settings.rwstripe_collect_password,
          isAPILoaded: true
        });
      }
    }); // Load Stripe products.

    wp.apiFetch({
      path: 'rwstripe/v1/products'
    }).then(data => {
      this.setState({
        productList: data,
        areProductsLoaded: true
      });
    }).catch(error => {
      this.setState({
        areProductsLoaded: error.message
      });
    });
  }

  render() {
    const {
      rwstripe_show_excerpts,
      rwstripe_collect_password,
      isAPILoaded,
      productList,
      areProductsLoaded
    } = this.state;

    if (!isAPILoaded || !areProductsLoaded) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Placeholder, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Spinner, null));
    } // Build step 1:


    var step1;

    if (!rwstripe.stripe_account_id) {
      // User is not connected to Stripe.
      var buttonText = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connect to Stripe', 'restrict-with-stripe');

      if (rwstripe.connect_in_test_mode) {
        buttonText += ' (' + (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Test Mode', 'restrict-with-stripe') + ')';
      }

      step1 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connect to Stripe', 'restrict-with-stripe')
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        href: rwstripe.stripe_connect_url,
        class: "rwstripe-stripe-connect"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, buttonText)));
    } else if (true === areProductsLoaded) {
      // We can successfully communicate with Stripe.
      var titleText = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connect to Stripe (Connected)', 'restrict-with-stripe');

      if ('live' !== rwstripe.stripe_environment) {
        titleText = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connect to Stripe (Connected in Test Mode)', 'restrict-with-stripe');
      }

      step1 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
        title: titleText,
        initialOpen: false
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connected to account: %d.', 'restrict-with-stripe').replace('%d', rwstripe.stripe_account_id)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        href: rwstripe.stripe_dashboard_url,
        target: "_blank"
      }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Visit your Stripe account dashboard', 'restrict-with-stripe'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        href: rwstripe.stripe_connect_url,
        class: "rwstripe-stripe-connect"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Disconnect From Stripe', 'restrict-with-stripe'))));
    } else {
      // User is connected to Stripe, but we can't use the API.
      var titleText = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connect to Stripe (Error)', 'restrict-with-stripe');

      if ('live' !== rwstripe.stripe_environment) {
        titleText = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Connect to Stripe (Error in Test Mode)', 'restrict-with-stripe');
      }

      step1 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
        title: titleText
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('The following error is received when trying to communicate with Stripe:', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, areProductsLoaded), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
        href: rwstripe.stripe_connect_url,
        class: "rwstripe-stripe-connect"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Disconnect From Stripe', 'restrict-with-stripe'))));
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "rwstripe-settings__header"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "rwstripe-settings__container"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "rwstripe-settings__title"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: _assets_restrict_with_stripe_png__WEBPACK_IMPORTED_MODULE_3__,
      alt: "{__('Restrict With Stripe', 'restrict-with-stripe')}"
    })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "rwstripe-settings__main"
    }, step1, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Create Products in Stripe', 'restrict-with-stripe'),
      initialOpen: rwstripe.stripe_account_id && !productList.length
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Restrict With Stripe uses Stripe Products to track user access to site content.', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Create a unique Stripe Product for each piece of content you need to restrict, whether it be a single post or page, a category of posts, or something else.', 'restrict-with-stripe')), productList.length > 0 ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fragment", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: rwstripe.stripe_dashboard_url + 'products/?active=true',
      target: "_blank"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isPrimary: true,
      isLarge: true
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Manage %d Products', 'restrict-with-stripe').replace('%d', productList.length)))) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fragment", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: rwstripe.stripe_dashboard_url + 'products/create',
      target: "_blank"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isPrimary: true,
      isLarge: true
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Create a New Product', 'restrict-with-stripe'))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Restrict Site Content', 'restrict-with-stripe'),
      initialOpen: rwstripe.stripe_account_id
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Restrict a single piece of content or protect a group of posts by category or tag.', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "columns"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('For Posts and Pages', 'restrict-with-stripe', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ol", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Edit the post or page', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Open the Settings panel', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Select Stripe Products in the "Restrict With Stripe" panel', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Save changes', 'restrict-with-stripe'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: rwstripe.admin_url + 'edit.php?post_type=post'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isSecondary: true
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('View Posts', 'restrict-with-stripe'))), " \xA0", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: rwstripe.admin_url + 'edit.php?post_type=page'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isSecondary: true
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('View Pages', 'restrict-with-stripe')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: _assets_rwstripe_edit_post_page_metabox_png__WEBPACK_IMPORTED_MODULE_4__,
      alt: "{__('Restrict With Stripe panel on the Edit Post or Edit Page screen.', 'restrict-with-stripe')}"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Example of the Restrict With Stripe panel on the Edit Post or Edit Page screen.', 'restrict-with-stripe')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "columns"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('For Categories and Tags', 'restrict-with-stripe', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ol", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Edit the category or tag', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Select Stripe Products', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Save changes', 'restrict-with-stripe'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: rwstripe.admin_url + 'edit-tags.php?taxonomy=category'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isSecondary: true
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('View Categories', 'restrict-with-stripe'))), " \xA0", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: rwstripe.admin_url + 'edit-tags.php?taxonomy=post_tag'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isSecondary: true
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('View Tags', 'restrict-with-stripe')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: _assets_rwstripe_edit_term_restrict_setting_png__WEBPACK_IMPORTED_MODULE_5__,
      alt: "{__('Restrict With Stripe settings on the Edit Category or Tag screen.', 'restrict-with-stripe')}"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Example of the Restrict With Stripe setting for Categories and Tags.', 'restrict-with-stripe'))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Link to Stripe Customer Portal', 'restrict-with-stripe'),
      initialOpen: rwstripe.stripe_account_id
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('The Customer Portal is a Stripe tool that allows customers to view previous payments and manage active subscriptions. Give customers a link to the portal using one of the methods below:', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "columns"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Create a "Customer Portal" Menu Item', 'restrict-with-stripe', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ol", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Edit the desired menu', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('In the "Restrict With Stripe" panel, select the "Stripe Customer Portal" menu item and click "Add to Menu"', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Click "Save Menu"', 'restrict-with-stripe')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: _assets_rwstripe_customer_portal_nav_menu_item_png__WEBPACK_IMPORTED_MODULE_6__,
      alt: "{__('Restrict With Stripe custom nav menu item for Stripe Customer Portal.', 'restrict-with-stripe')}"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Example of adding the Stripe Customer Portal menu item to a nav menu location.', 'restrict-with-stripe')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "columns"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Use the "Stripe Customer Portal" Block', 'restrict-with-stripe', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ol", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Edit the desired page', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Insert the "Stripe Customer Portal" block', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Save changes', 'restrict-with-stripe')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "column"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: _assets_rwstripe_customer_portal_block_png__WEBPACK_IMPORTED_MODULE_7__,
      alt: "{__('Stripe Customer Portal block on the Edit Page screen.', 'restrict-with-stripe')}"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Example of the Stripe Customer Portal block on the Edit Page screen.', 'restrict-with-stripe'))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Customize Advanced Settings', 'restrict-with-stripe'),
      initialOpen: rwstripe.stripe_account_id
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Confirm advanced settings for default behavior (optional).', 'restrict-with-stripe')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Show a content excerpt on restricted posts or pages', 'restrict-with-stripe'),
      onChange: rwstripe_show_excerpts => this.setState({
        rwstripe_show_excerpts
      }),
      checked: rwstripe_show_excerpts
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Allow customers to choose a password during registration', 'restrict-with-stripe'),
      onChange: rwstripe_collect_password => this.setState({
        rwstripe_collect_password
      }),
      checked: rwstripe_collect_password
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__.Button, {
      isPrimary: true,
      onClick: () => {
        const {
          rwstripe_show_excerpts,
          rwstripe_collect_password
        } = this.state; // POST

        _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: '/wp/v2/settings',
          method: 'POST',
          data: {
            rwstripe_show_excerpts: rwstripe_show_excerpts,
            rwstripe_collect_password: rwstripe_collect_password
          }
        }).then(res => {
          (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_9__.dispatch)('core/notices').createNotice('success', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Settings Saved', 'restrict-with-stripe'), {
            type: 'snackbar',
            isDismissible: true
          });
        }, err => {
          (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_9__.dispatch)('core/notices').createNotice('error', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Save Failed', 'restrict-with-stripe'), {
            type: 'snackbar',
            isDismissible: true
          });
        });
      }
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_10__.__)('Save', 'restrict-with-stripe'))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "rwstripe-settings__notices"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Notices, null)));
  }

}

document.addEventListener('DOMContentLoaded', () => {
  const htmlOutput = document.getElementById('rwstripe-settings');

  if (htmlOutput) {
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(App, null), htmlOutput);
  }
});

/***/ }),

/***/ "./blocks/src/settings/style.scss":
/*!****************************************!*\
  !*** ./blocks/src/settings/style.scss ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./blocks/assets/restrict-with-stripe.png":
/*!************************************************!*\
  !*** ./blocks/assets/restrict-with-stripe.png ***!
  \************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = __webpack_require__.p + "images/restrict-with-stripe.f445c776.png";

/***/ }),

/***/ "./blocks/assets/rwstripe-customer-portal-block.png":
/*!**********************************************************!*\
  !*** ./blocks/assets/rwstripe-customer-portal-block.png ***!
  \**********************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = __webpack_require__.p + "images/rwstripe-customer-portal-block.bdfc2d4c.png";

/***/ }),

/***/ "./blocks/assets/rwstripe-customer-portal-nav-menu-item.png":
/*!******************************************************************!*\
  !*** ./blocks/assets/rwstripe-customer-portal-nav-menu-item.png ***!
  \******************************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = __webpack_require__.p + "images/rwstripe-customer-portal-nav-menu-item.89eadea1.png";

/***/ }),

/***/ "./blocks/assets/rwstripe-edit-post-page-metabox.png":
/*!***********************************************************!*\
  !*** ./blocks/assets/rwstripe-edit-post-page-metabox.png ***!
  \***********************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = __webpack_require__.p + "images/rwstripe-edit-post-page-metabox.600f528b.png";

/***/ }),

/***/ "./blocks/assets/rwstripe-edit-term-restrict-setting.png":
/*!***************************************************************!*\
  !*** ./blocks/assets/rwstripe-edit-term-restrict-setting.png ***!
  \***************************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = __webpack_require__.p + "images/rwstripe-edit-term-restrict-setting.98cd8d85.png";

/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ (function(module) {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "@wordpress/notices":
/*!*********************************!*\
  !*** external ["wp","notices"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["notices"];

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
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
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
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	!function() {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
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
/******/ 	/* webpack/runtime/publicPath */
/******/ 	!function() {
/******/ 		var scriptUrl;
/******/ 		if (__webpack_require__.g.importScripts) scriptUrl = __webpack_require__.g.location + "";
/******/ 		var document = __webpack_require__.g.document;
/******/ 		if (!scriptUrl && document) {
/******/ 			if (document.currentScript)
/******/ 				scriptUrl = document.currentScript.src
/******/ 			if (!scriptUrl) {
/******/ 				var scripts = document.getElementsByTagName("script");
/******/ 				if(scripts.length) scriptUrl = scripts[scripts.length - 1].src
/******/ 			}
/******/ 		}
/******/ 		// When supporting browsers where an automatic publicPath is not supported you must specify an output.publicPath manually via configuration
/******/ 		// or pass an empty string ("") and set the __webpack_public_path__ variable from your code to use your own logic.
/******/ 		if (!scriptUrl) throw new Error("Automatic publicPath is not supported in this browser");
/******/ 		scriptUrl = scriptUrl.replace(/#.*$/, "").replace(/\?.*$/, "").replace(/\/[^\/]+$/, "/");
/******/ 		__webpack_require__.p = scriptUrl + "../";
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"settings/admin": 0,
/******/ 			"settings/style-admin": 0
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
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
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
/******/ 		var chunkLoadingGlobal = self["webpackChunkrestrict_with_stripe"] = self["webpackChunkrestrict_with_stripe"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["settings/style-admin"], function() { return __webpack_require__("./blocks/src/settings/index.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=admin.js.map