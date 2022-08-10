import './style.scss';

import apiFetch from '@wordpress/api-fetch';

import {
    Button,
    PanelBody,
    Placeholder,
    SnackbarList,
    Spinner,
    ToggleControl,
    Icon,
} from '@wordpress/components';

import {
    dispatch,
    useDispatch,
    useSelect,
} from '@wordpress/data';

import {
    Fragment,
    render,
    Component,
} from '@wordpress/element';

import { __ } from '@wordpress/i18n';

import { store as noticesStore } from '@wordpress/notices';

const Notices = () => {
    const notices = useSelect(
        (select) =>
            select(noticesStore)
                .getNotices()
                .filter((notice) => notice.type === 'snackbar'),
        []
    );
    const { removeNotice } = useDispatch(noticesStore);
    return (
        <SnackbarList
            className="edit-site-notices"
            notices={notices}
            onRemove={removeNotice}
        />
    );
};

class App extends Component {
    constructor() {
        super(...arguments);

        this.state = {
            rwstripe_show_excerpts: true,
            rwstripe_collect_password: true,
            isAPILoaded: false,
            productList: [],
            areProductsLoaded: false,
        };
    }

    componentDidMount() {
        // Load site settings.
        apiFetch({ path: '/wp/v2/settings' }).then((settings) => {
            console.log(settings);
            if ( settings.hasOwnProperty( 'rwstripe_show_excerpts' ) ) {
                this.setState({
                    rwstripe_show_excerpts: settings.rwstripe_show_excerpts,
                    rwstripe_collect_password: settings.rwstripe_collect_password,
                    isAPILoaded: true,
                });
            }
        });

        // Load Stripe products.
        wp.apiFetch( {
			path: 'rwstripe/v1/products',
		} ).then( ( data ) => {
			this.setState( {
                productList: data,
                areProductsLoaded: true,
            } );
		} ).catch( (error) => {
            this.setState( {
                areProductsLoaded: error.message,
            } );
        });
    }

    render() {
        const {
            rwstripe_show_excerpts,
            rwstripe_collect_password,
            isAPILoaded,
            productList,
            areProductsLoaded,
        } = this.state;

        if ( ! isAPILoaded || ! areProductsLoaded ) {
            return (
                <Placeholder>
                    <Spinner />
                </Placeholder>
            );
        }

        // Build step 1:
        var step1;
        if ( ! rwstripe.stripe_user_id ) {
            // User is not connected to Stripe.
            step1 = (
                <PanelBody title={ __( 'Step 1: Connect to Stripe', 'restrict-with-stripe' ) }>
                    <a href={rwstripe.stripe_connect_url} class="rwstripe-stripe-connect">
                    <span>
                        {__('Connect To Stripe', 'restrict-with-stripe')}
                    </span>
                </a>
                </PanelBody>
            );
        } else if ( true === areProductsLoaded ) {
            // We can successfully communicate with Stripe.
            step1 = (
                <PanelBody title={ __( 'Step 1: Connect to Stripe (Connected)', 'restrict-with-stripe' ) } initialOpen={false} >
                    <a href="https://dashboard.stripe.com/" target="_blank">
                        <Button isPrimary isLarge >
                            {__('Go To Stripe Dashboard', 'restrict-with-stripe')}
                        </Button>
                    </a><br /><br />
                    <a href={rwstripe.stripe_connect_url} class="rwstripe-stripe-connect">
                        <span>
                            {__('Disconnect From Stripe', 'restrict-with-stripe')}
                        </span>
                    </a>
                </PanelBody>
            );
        } else {
            // User is connected to Stripe, but we can't use the API.
            step1 = (
                <PanelBody title={ __( 'Step 1: Connect to Stripe (Error)', 'restrict-with-stripe' ) } >
                    <p>{ __('The following error is received when trying to communicate with Stripe:', 'restrict-with-stripe')}</p>
                    <p>{areProductsLoaded}</p>
                    <a href={rwstripe.stripe_connect_url} class="rwstripe-stripe-connect">
                        <span>
                            {__('Disconnect From Stripe', 'restrict-with-stripe')}
                        </span>
                    </a>
                </PanelBody>
            );
        }

        return (
            <Fragment>
                <div className="rwstripe-settings__header">
                    <div className="rwstripe-settings__container">
                        <div className="rwstripe-settings__title">
                            <h1>{__('Restrict With Stripe Set Up', 'restrict-with-stripe')} <Icon icon="lock" /></h1>
                        </div>
                    </div>
                </div>

                <div className="rwstripe-settings__main">
                    {step1}
                    <PanelBody title={__('Step 2: Create Products in Stripe', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_user_id && ! productList.length} >
                        <p>{__('Restrict With Stripe uses Stripe Products to track which site content a user has access to.', 'restrict-with-stripe')}</p>
                        <p>{__('A Product should be created in Stripe for each set of content that you would like users to be able to purchase, whether it be a single post or a group of posts.', 'restrict-with-stripe')}</p>
                        {
                            productList.length > 0 ?
                            <fragment>
                                <a href="https://dashboard.stripe.com/products" target="_blank">
                                    <Button isPrimary isLarge >
                                        { __('Manage %d Products', 'restrict-with-stripe').replace('%d', productList.length) }
                                    </Button>
                                </a>
                            </fragment>
                            :
                                <fragment>
                                    <a href="https://dashboard.stripe.com/products/create" target="_blank">
                                        <Button isPrimary isLarge >
                                            {__('Create a New Product', 'restrict-with-stripe')}
                                        </Button>
                                    </a>
                                </fragment>
                        }
                    </PanelBody>
                    <PanelBody title={__('Step 3: Add Restrictions to Site Content', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_user_id}>
                        <PanelBody title={__('Restricting Post and Pages', 'restrict-with-stripe')} initialOpen={false} >
                            <ol>
                                <li>{__('Edit the page or post that you would like to restrict', 'restrict-with-stripe')}</li>
                                <li>{__('Open the settings toolbar', 'restrict-with-stripe')}</li>
                                <li>{__('Open the "Restrict With Stripe" panel', 'restrict-with-stripe')}</li>
                                <li>{__('Select the Stripe Product to restrict the page or post by', 'restrict-with-stripe')}</li>
                                <li>{__('Save the page or post', 'restrict-with-stripe')}</li>
                            </ol>
                            <a href={rwstripe.admin_url + 'edit.php?post_type=post'}>
                                <Button isPrimary isLarge >
                                    {__('View Posts', 'restrict-with-stripe')}
                                </Button>
                            </a> &nbsp;
                            <a href={rwstripe.admin_url + 'edit.php?post_type=page'}>
                                <Button isPrimary isLarge >
                                    {__('View Pages', 'restrict-with-stripe')}
                                </Button>
                            </a>

                        </PanelBody>
                        <PanelBody title={__('Restricting Categories and Tags', 'restrict-with-stripe')} initialOpen={false} >
                            <ol>
                                <li>{__('Edit the category or tag that you would like to restrict', 'restrict-with-stripe')}</li>
                                <li>{__('Select the Stripe Product to restrict the page or post by', 'restrict-with-stripe')}</li>
                                <li>{__('Save the category or tag', 'restrict-with-stripe')}</li>
                            </ol>
                            <a href={rwstripe.admin_url + 'edit-tags.php?taxonomy=category'}>
                                <Button isPrimary isLarge >
                                    {__('View Categories', 'restrict-with-stripe')}
                                </Button>
                            </a> &nbsp;
                            <a href={rwstripe.admin_url + 'edit-tags.php?taxonomy=post_tag'}>
                                <Button isPrimary isLarge >
                                    {__('View Tags', 'restrict-with-stripe')}
                                </Button>
                            </a>
                        </PanelBody>
                    </PanelBody>
                    <PanelBody title={__('Step 4: Link to Stripe Customer Portal', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_user_id}>
                        <p>{__('The Stripe Customer Portal is a tool created by Stripe to allow customers to view their previous payments and manage their active subscriptions. It is important to link to the Customer Portal to give your users access to this information.', 'restrict-with-stripe')}</p>
                        <PanelBody title={__('Creating a Customer Portal Menu Item', 'restrict-with-stripe')} initialOpen={false} >
                            <ol>
                                <li>{__('Edit the menu where you would like to add a menu item linking to the Stripe Customer Portal', 'restrict-with-stripe')}</li>
                                <li>{__('In the "Restrict With Stripe" panel, select the "Stripe Customer Portal" menu item and click "Add to Menu"', 'restrict-with-stripe')}</li>
                                <li>{__('Click "Save Menu"', 'restrict-with-stripe')}</li>
                            </ol>
                            <a href={rwstripe.admin_url + "nav-menus.php"}>
                                <Button isPrimary isLarge >
                                    {__('Edit Menus', 'restrict-with-stripe')}
                                </Button>
                            </a>
                        </PanelBody>
                        <PanelBody title={__('Using the Stripe Customer Portal Block', 'restrict-with-stripe')} initialOpen={false} >
                            <ol>
                                <li>{__('Edit the page or post that you would like to add the Customer Portal Block to', 'restrict-with-stripe')}</li>
                                <li>{__('Insert the "Stripe Customer Portal" block to the page or post', 'restrict-with-stripe')}</li>
                                <li>{__('Save the page or post', 'restrict-with-stripe')}</li>
                            </ol>
                            <p>{__('For more customzation options, follow the "Creating a Customized Customer Portal Block" instructions below.', 'restrict-with-stripe')}</p>
                        </PanelBody>
                        <PanelBody title={__('Creating a Customized Customer Portal Block', 'restrict-with-stripe')} initialOpen={false} >
                            <ol>
                            <li>{__('Edit the page or post that you would like to add the Customer Portal Block to', 'restrict-with-stripe')}</li>
                                <li>{__('Insert a "Button" block to the page or post', 'restrict-with-stripe')}</li>
                                <li>{__('Customize the style of the button as desired', 'restrict-with-stripe')}</li>
                                <li>{__('Open the sidebar settings for the block using the "Show more settings" option', 'restrict-with-stripe')}</li>
                                <li>{__('Under the "Advanced" tab, add "rwstripe-customer-portal-button" into the "Additional CSS Class(es)" text box', 'restrict-with-stripe')}</li>
                                <li>{__('Save the page or post', 'restrict-with-stripe')}</li>
                            </ol>
                        </PanelBody>
                    </PanelBody>
                    <PanelBody title={__('Step 5: Customize Advanced Settings', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_user_id} >
                        <ToggleControl
                            label={__('Show Excerpts', 'restrict-with-stripe')}
                            onChange={(rwstripe_show_excerpts) => this.setState({ rwstripe_show_excerpts })}
                            checked={rwstripe_show_excerpts}
                        />
                        <ToggleControl
                            label={__('Collect Password During Registration', 'restrict-with-stripe')}
                            onChange={(rwstripe_collect_password) => this.setState({ rwstripe_collect_password })}
                            checked={rwstripe_collect_password}
                        />
                        <Button
                            isPrimary
                            isLarge
                            onClick={() => {
                                const {
                                    rwstripe_show_excerpts,
                                    rwstripe_collect_password,
                                } = this.state;

                                // POST
                                apiFetch({
                                    path: '/wp/v2/settings',
                                    method: 'POST',
                                    data: {
                                        rwstripe_show_excerpts: rwstripe_show_excerpts,
                                        rwstripe_collect_password: rwstripe_collect_password,
                                    },
                                }).then((res) => {
                                    dispatch('core/notices').createNotice(
                                        'success',
                                        __('Settings Saved', 'restrict-with-stripe'),
                                        {
                                            type: 'snackbar',
                                            isDismissible: true,
                                        }
                                    );
                                }, (err) => {
                                    dispatch('core/notices').createNotice(
                                        'error',
                                        __('Save Failed', 'restrict-with-stripe'),
                                        {
                                            type: 'snackbar',
                                            isDismissible: true,
                                        }
                                    );
                                });
                            }}
                        >
                            {__('Save', 'restrict-with-stripe')}
                        </Button>
                    </PanelBody>
                </div>

                <div className="rwstripe-settings__notices">
                    <Notices />
                </div>

            </Fragment>
        )
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const htmlOutput = document.getElementById('rwstripe-settings');

    if (htmlOutput) {
        render(
            <App />,
            htmlOutput
        );
    }
});
