(()=>{"use strict";var e,t={29:(e,t,r)=>{const i=window.wp.element,s=window.wp.apiFetch;var n=r.n(s);const c=r.p+"images/restrict-with-stripe.f445c776.png",a=r.p+"images/rwstripe-edit-post-page-metabox.600f528b.png",l=r.p+"images/rwstripe-edit-term-restrict-setting.98cd8d85.png",o=r.p+"images/rwstripe-customer-portal-nav-menu-item.89eadea1.png",p=r.p+"images/rwstripe-customer-portal-block.bdfc2d4c.png",m=window.wp.components,_=window.wp.data,h=window.wp.i18n,d=window.wp.notices,u=()=>{const e=(0,_.useSelect)((e=>e(d.store).getNotices().filter((e=>"snackbar"===e.type))),[]),{removeNotice:t}=(0,_.useDispatch)(d.store);return(0,i.createElement)(m.SnackbarList,{className:"edit-site-notices",notices:e,onRemove:t})};class w extends i.Component{constructor(){super(...arguments),this.state={rwstripe_show_excerpts:!0,rwstripe_collect_password:!0,isAPILoaded:!1,productList:[],areProductsLoaded:!1}}componentDidMount(){n()({path:"/wp/v2/settings"}).then((e=>{e.hasOwnProperty("rwstripe_show_excerpts")&&this.setState({rwstripe_show_excerpts:e.rwstripe_show_excerpts,rwstripe_collect_password:e.rwstripe_collect_password,isAPILoaded:!0})})),wp.apiFetch({path:"rwstripe/v1/products"}).then((e=>{this.setState({productList:e,areProductsLoaded:!0})})).catch((e=>{this.setState({areProductsLoaded:e.message})}))}render(){const{rwstripe_show_excerpts:e,rwstripe_collect_password:t,isAPILoaded:r,productList:s,areProductsLoaded:d}=this.state;if(!r||!d)return(0,i.createElement)(m.Placeholder,null,(0,i.createElement)(m.Spinner,null));var w;if(rwstripe.stripe_account_id)if(!0===d){var E=(0,h.__)("Connect to Stripe (Connected)","restrict-with-stripe");"live"!==rwstripe.stripe_environment&&(E=(0,h.__)("Connect to Stripe (Connected in Test Mode)","restrict-with-stripe")),w=(0,i.createElement)(m.PanelBody,{title:E,initialOpen:!1},(0,i.createElement)("p",null,(0,h.__)("Connected to account: %d.","restrict-with-stripe").replace("%d",rwstripe.stripe_account_id)),(0,i.createElement)("p",null,(0,i.createElement)("a",{href:rwstripe.stripe_dashboard_url,target:"_blank"},(0,h.__)("Visit your Stripe account dashboard","restrict-with-stripe"))),(0,i.createElement)("a",{href:rwstripe.stripe_connect_url,class:"rwstripe-stripe-connect"},(0,i.createElement)("span",null,(0,h.__)("Disconnect From Stripe","restrict-with-stripe"))))}else E=(0,h.__)("Connect to Stripe (Error)","restrict-with-stripe"),"live"!==rwstripe.stripe_environment&&(E=(0,h.__)("Connect to Stripe (Error in Test Mode)","restrict-with-stripe")),w=(0,i.createElement)(m.PanelBody,{title:E},(0,i.createElement)("p",null,(0,h.__)("The following error is received when trying to communicate with Stripe:","restrict-with-stripe")),(0,i.createElement)("p",null,d),(0,i.createElement)("a",{href:rwstripe.stripe_connect_url,class:"rwstripe-stripe-connect"},(0,i.createElement)("span",null,(0,h.__)("Disconnect From Stripe","restrict-with-stripe"))));else{var g=(0,h.__)("Connect to Stripe","restrict-with-stripe");rwstripe.connect_in_test_mode&&(g+=" ("+(0,h.__)("Test Mode","restrict-with-stripe")+")"),w=(0,i.createElement)(m.PanelBody,{title:(0,h.__)("Connect to Stripe","restrict-with-stripe")},rwstripe.connection_error&&(0,i.createElement)(m.Notice,{status:"error"},(0,i.createElement)("p",null,rwstripe.connection_error)),(0,i.createElement)("a",{href:rwstripe.stripe_connect_url,class:"rwstripe-stripe-connect"},(0,i.createElement)("span",null,g)))}return(0,i.createElement)(i.Fragment,null,(0,i.createElement)("div",{className:"rwstripe-settings__header"},(0,i.createElement)("div",{className:"rwstripe-settings__container"},(0,i.createElement)("div",{className:"rwstripe-settings__title"},(0,i.createElement)("img",{src:c,alt:"{__('Restrict With Stripe', 'restrict-with-stripe')}"})))),(0,i.createElement)("div",{className:"rwstripe-settings__main"},w,(0,i.createElement)(m.PanelBody,{title:(0,h.__)("Create Products in Stripe","restrict-with-stripe"),initialOpen:rwstripe.stripe_account_id&&!s.length},(0,i.createElement)("p",null,(0,h.__)("Restrict With Stripe uses Stripe Products to track user access to site content.","restrict-with-stripe")),(0,i.createElement)("p",null,(0,h.__)("Create a unique Stripe Product for each piece of content you need to restrict, whether it be a single post or page, a category of posts, or something else.","restrict-with-stripe")),s.length>0?(0,i.createElement)("fragment",null,(0,i.createElement)("a",{href:rwstripe.stripe_dashboard_url+"products/?active=true",target:"_blank"},(0,i.createElement)(m.Button,{isPrimary:!0,isLarge:!0},(0,h.__)("Manage %d Products","restrict-with-stripe").replace("%d",s.length)))):(0,i.createElement)("fragment",null,(0,i.createElement)("a",{href:rwstripe.stripe_dashboard_url+"products/create",target:"_blank"},(0,i.createElement)(m.Button,{isPrimary:!0,isLarge:!0},(0,h.__)("Create a New Product","restrict-with-stripe"))))),(0,i.createElement)(m.PanelBody,{title:(0,h.__)("Restrict Site Content","restrict-with-stripe"),initialOpen:rwstripe.stripe_account_id},(0,i.createElement)("p",null,(0,h.__)("Restrict a single piece of content or protect a group of posts by category or tag.","restrict-with-stripe")),(0,i.createElement)("div",{className:"columns"},(0,i.createElement)("div",{className:"column"},(0,i.createElement)("h3",null,(0,h.__)("For Posts and Pages","restrict-with-stripe","restrict-with-stripe")),(0,i.createElement)("ol",null,(0,i.createElement)("li",null,(0,h.__)("Edit the post or page","restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)("Open the Settings panel","restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)('Select Stripe Products in the "Restrict With Stripe" panel',"restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)("Save changes","restrict-with-stripe"))),(0,i.createElement)("a",{href:rwstripe.admin_url+"edit.php?post_type=post"},(0,i.createElement)(m.Button,{isSecondary:!0},(0,h.__)("View Posts","restrict-with-stripe"))),"  ",(0,i.createElement)("a",{href:rwstripe.admin_url+"edit.php?post_type=page"},(0,i.createElement)(m.Button,{isSecondary:!0},(0,h.__)("View Pages","restrict-with-stripe")))),(0,i.createElement)("div",{className:"column"},(0,i.createElement)("img",{src:a,alt:"{__('Restrict With Stripe panel on the Edit Post or Edit Page screen.', 'restrict-with-stripe')}"}),(0,i.createElement)("p",null,(0,h.__)("Example of the Restrict With Stripe panel on the Edit Post or Edit Page screen.","restrict-with-stripe")))),(0,i.createElement)("div",{className:"columns"},(0,i.createElement)("div",{className:"column"},(0,i.createElement)("h3",null,(0,h.__)("For Categories and Tags","restrict-with-stripe","restrict-with-stripe")),(0,i.createElement)("ol",null,(0,i.createElement)("li",null,(0,h.__)("Edit the category or tag","restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)("Select Stripe Products","restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)("Save changes","restrict-with-stripe"))),(0,i.createElement)("a",{href:rwstripe.admin_url+"edit-tags.php?taxonomy=category"},(0,i.createElement)(m.Button,{isSecondary:!0},(0,h.__)("View Categories","restrict-with-stripe"))),"  ",(0,i.createElement)("a",{href:rwstripe.admin_url+"edit-tags.php?taxonomy=post_tag"},(0,i.createElement)(m.Button,{isSecondary:!0},(0,h.__)("View Tags","restrict-with-stripe")))),(0,i.createElement)("div",{className:"column"},(0,i.createElement)("img",{src:l,alt:"{__('Restrict With Stripe settings on the Edit Category or Tag screen.', 'restrict-with-stripe')}"}),(0,i.createElement)("p",null,(0,h.__)("Example of the Restrict With Stripe setting for Categories and Tags.","restrict-with-stripe"))))),(0,i.createElement)(m.PanelBody,{title:(0,h.__)("Link to Stripe Customer Portal","restrict-with-stripe"),initialOpen:rwstripe.stripe_account_id},(0,i.createElement)("p",null,(0,h.__)("The Customer Portal is a Stripe tool that allows customers to view previous payments and manage active subscriptions. Give customers a link to the portal using one of the methods below:","restrict-with-stripe")),(0,i.createElement)("div",{className:"columns"},(0,i.createElement)("div",{className:"column"},(0,i.createElement)("h3",null,(0,h.__)('Create a "Customer Portal" Menu Item',"restrict-with-stripe","restrict-with-stripe")),(0,i.createElement)("ol",null,(0,i.createElement)("li",null,(0,h.__)("Edit the desired menu","restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)('In the "Restrict With Stripe" panel, select the "Stripe Customer Portal" menu item and click "Add to Menu"',"restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)('Click "Save Menu"',"restrict-with-stripe")))),(0,i.createElement)("div",{className:"column"},(0,i.createElement)("img",{src:o,alt:"{__('Restrict With Stripe custom nav menu item for Stripe Customer Portal.', 'restrict-with-stripe')}"}),(0,i.createElement)("p",null,(0,h.__)("Example of adding the Stripe Customer Portal menu item to a nav menu location.","restrict-with-stripe")))),(0,i.createElement)("div",{className:"columns"},(0,i.createElement)("div",{className:"column"},(0,i.createElement)("h3",null,(0,h.__)('Use the "Stripe Customer Portal" Block',"restrict-with-stripe","restrict-with-stripe")),(0,i.createElement)("ol",null,(0,i.createElement)("li",null,(0,h.__)("Edit the desired page","restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)('Insert the "Stripe Customer Portal" block',"restrict-with-stripe")),(0,i.createElement)("li",null,(0,h.__)("Save changes","restrict-with-stripe")))),(0,i.createElement)("div",{className:"column"},(0,i.createElement)("img",{src:p,alt:"{__('Stripe Customer Portal block on the Edit Page screen.', 'restrict-with-stripe')}"}),(0,i.createElement)("p",null,(0,h.__)("Example of the Stripe Customer Portal block on the Edit Page screen.","restrict-with-stripe"))))),(0,i.createElement)(m.PanelBody,{title:(0,h.__)("Customize Advanced Settings","restrict-with-stripe"),initialOpen:rwstripe.stripe_account_id},(0,i.createElement)("p",null,(0,h.__)("Confirm advanced settings for default behavior (optional).","restrict-with-stripe")),(0,i.createElement)(m.ToggleControl,{label:(0,h.__)("Show a content excerpt on restricted posts or pages","restrict-with-stripe"),onChange:e=>this.setState({rwstripe_show_excerpts:e}),checked:e}),(0,i.createElement)(m.ToggleControl,{label:(0,h.__)("Allow customers to choose a password during registration","restrict-with-stripe"),onChange:e=>this.setState({rwstripe_collect_password:e}),checked:t}),(0,i.createElement)("p",null,(0,i.createElement)(m.Button,{isPrimary:!0,onClick:()=>{const{rwstripe_show_excerpts:e,rwstripe_collect_password:t}=this.state;n()({path:"/wp/v2/settings",method:"POST",data:{rwstripe_show_excerpts:Boolean(e),rwstripe_collect_password:Boolean(t)}}).then((e=>{(0,_.dispatch)("core/notices").createNotice("success",(0,h.__)("Settings Saved","restrict-with-stripe"),{type:"snackbar",isDismissible:!0})}),(e=>{(0,_.dispatch)("core/notices").createNotice("error",(0,h.__)("Save Failed","restrict-with-stripe"),{type:"snackbar",isDismissible:!0})}))}},(0,h.__)("Save","restrict-with-stripe"))))),(0,i.createElement)("div",{className:"rwstripe-settings__notices"},(0,i.createElement)(u,null)))}}document.addEventListener("DOMContentLoaded",(()=>{const e=document.getElementById("rwstripe-settings");e&&(0,i.render)((0,i.createElement)(w,null),e)}))}},r={};function i(e){var s=r[e];if(void 0!==s)return s.exports;var n=r[e]={exports:{}};return t[e](n,n.exports,i),n.exports}i.m=t,e=[],i.O=(t,r,s,n)=>{if(!r){var c=1/0;for(p=0;p<e.length;p++){for(var[r,s,n]=e[p],a=!0,l=0;l<r.length;l++)(!1&n||c>=n)&&Object.keys(i.O).every((e=>i.O[e](r[l])))?r.splice(l--,1):(a=!1,n<c&&(c=n));if(a){e.splice(p--,1);var o=s();void 0!==o&&(t=o)}}return t}n=n||0;for(var p=e.length;p>0&&e[p-1][2]>n;p--)e[p]=e[p-1];e[p]=[r,s,n]},i.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return i.d(t,{a:t}),t},i.d=(e,t)=>{for(var r in t)i.o(t,r)&&!i.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},i.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e;i.g.importScripts&&(e=i.g.location+"");var t=i.g.document;if(!e&&t&&(t.currentScript&&(e=t.currentScript.src),!e)){var r=t.getElementsByTagName("script");r.length&&(e=r[r.length-1].src)}if(!e)throw new Error("Automatic publicPath is not supported in this browser");e=e.replace(/#.*$/,"").replace(/\?.*$/,"").replace(/\/[^\/]+$/,"/"),i.p=e+"../"})(),(()=>{var e={412:0,785:0};i.O.j=t=>0===e[t];var t=(t,r)=>{var s,n,[c,a,l]=r,o=0;if(c.some((t=>0!==e[t]))){for(s in a)i.o(a,s)&&(i.m[s]=a[s]);if(l)var p=l(i)}for(t&&t(r);o<c.length;o++)n=c[o],i.o(e,n)&&e[n]&&e[n][0](),e[n]=0;return i.O(p)},r=globalThis.webpackChunkrestrict_with_stripe=globalThis.webpackChunkrestrict_with_stripe||[];r.forEach(t.bind(null,0)),r.push=t.bind(null,r.push.bind(r))})();var s=i.O(void 0,[785],(()=>i(29)));s=i.O(s)})();