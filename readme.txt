=== Restrict With Stripe - Sell Access to Posts and Pages with Stripe ===
Contributors: strangerstudios, dlparker1005
Tags: subscriptions, ecommerce, e-commerce, stripe, restrict access, restrict content
Requires at least: 5.2
Tested up to: 6.1
Requires PHP: 5.6
Stable tag: 1.0.2
License: GPLv3

Integrate with Stripe to sell access to restricted posts, pages, categories, and tags.

== Description ==

= The building power of WordPress combined with the payment processing power of Stripe =

WordPress has all the tools that you need to build content that is worth buying, but what is the best way to actually sell it?

Traditional e-commerce plugins typically integrate with payment gateways to process payments, but often try to recreate existing payment features such as order management directly on your WordPress website. Although handling some payment processes on-site is necessary for some e-commerce websites, it adds complexity that is not necessary for most websites.

Instead of recreating payment features within WordPress, Restrict With Stripe harnesses the full power of Stripe to handle the entire e-commerce workflow including:
* Hosted checkout page built to maximize conversions powered by Stripe Checkout
* Hosted customer portal where users can view their payment history and manage their subscriptions
* Streamlined dashboard for site administrators to manage products, customers, subscriptions, and more
* Integrated tax solution with powered by Stripe Tax
* Advanced reporting features

Build your content with WordPress. Let Stripe handle the payments.

= Streamlined Setup =

1. Connect to Stripe: Integrate your site with Stripe.
2. Create Products in Stripe: Create a product for each post, page, category, or tag that you want to sell access to.
3. Add Restrictions to Site Content: Restrict access to content you want to sell.
4. Link to Stripe Customer Portal: Link users to their payment history and let them manage their subscriptions.
5. Customize Advanced Settings: Customize the plugin to fit the needs of your specific site.

= Building a Membership Site? =
Restrict With Stripe is a great streamlined solution for selling access to posts and pages, but if you are trying to build a more advanced membership website and need additional features such as restricting other types of content, adding custom fields at checkout, building a member directory or integrating with other WordPress plugins, then [Paid Memberships Pro](https://www.paidmembershipspro.com/) (our sister plugin), may be a better option for your website.

== Installation ==

= Download, Install and Activate! =
1. Go to Plugins > Add New to find and install Restrict With Stripe.
2. Or, download the latest version of the plugin, then go to Plugins > Add New and click the "Upload Plugin" button to upload your .zip file.
3. Activate the plugin.

= Complete the Initial Plugin Setup =
Go to Settings > Restrict With Stripe in the WordPress admin to begin setup.

== Frequently Asked Questions ==

= I need help installing, configuring, or customizing the plugin. =
Please visit the [WordPress support forum](https://wordpress.org/support/plugin/restrict-with-stripe/) for more documentation and our support forums.

= I found a bug in the plugin. =
If you find an issue/bug, let us know by [creating a detailed GitHub issue](https://github.com/strangerstudios/restrict-with-stripe/issues/new).

= How can I test this plugin without processing real payments? =
You can connect to a Stripe account in test mode by adding the following line of code to a code snippet before connecting to Stripe:
`add_action( 'rwstripe_connect_in_test_mode', '__return_true' );`

== Changelog ==
= 1.0.1 - 2022-11-04 =
* ENHANCEMENT: Updated pot file.
* REFACTOR: Better escaping to avoid scanner issues.

= 1.0.1 - 2022-11-02 =
* SECURITY: Improved escaping and sanitization.

= 1.0 - 2022-10-31 =
* NOTE: Initial Release. Enjoy!
