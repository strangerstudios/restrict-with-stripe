import './style.scss';

import apiFetch from '@wordpress/api-fetch';
import rwsLogo from '../../assets/restrict-with-stripe.png';

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
        if ( ! rwstripe.stripe_account_id ) {
            // User is not connected to Stripe.
            step1 = (
                <PanelBody title={ __( 'Connect to Stripe', 'restrict-with-stripe' ) }>
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
                <PanelBody title={ __( 'Connect to Stripe (Connected)', 'restrict-with-stripe' ) } initialOpen={false} >
                    <p>{ __('Connected to account: %d.', 'restrict-with-stripe').replace('%d', rwstripe.stripe_account_id) }</p>
                    <p><a href={rwstripe.stripe_dashboard_url} target="_blank">{__('Visit your Stripe account dashboard', 'restrict-with-stripe')}</a></p>
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
                <PanelBody title={ __( 'Connect to Stripe (Error)', 'restrict-with-stripe' ) } >
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
                           <img src={rwsLogo} alt="{__('Restrict with Stripe', 'restrict-with-stripe')}" />
                        </div>
                    </div>
                </div>

                <div className="rwstripe-settings__main">
                    {step1}
                    <PanelBody title={__('Create Products in Stripe', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_account_id && ! productList.length} >
                        <p>{__('Restrict with Stripe uses Stripe Products to track user access to site content.', 'restrict-with-stripe')}</p>
                        <p>{__('Create a unique Stripe Product for each piece of content you need to restrict, whether it be a single post or page, a category of posts, or something else.', 'restrict-with-stripe')}</p>
                        {
                            productList.length > 0 ?
                            <fragment>
                                <a href={rwstripe.stripe_manage_products_url} target="_blank">
                                    <Button isPrimary isLarge >
                                        { __('Manage %d Products', 'restrict-with-stripe').replace('%d', productList.length) }
                                    </Button>
                                </a>
                            </fragment>
                            :
                                <fragment>
                                    <a href={rwstripe.stripe_create_product_url} target="_blank">
                                        <Button isPrimary isLarge >
                                            {__('Create a New Product', 'restrict-with-stripe')}
                                        </Button>
                                    </a>
                                </fragment>
                        }
                    </PanelBody>
                    <PanelBody title={__('Restrict Site Content', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_account_id}>
                        <p>{__('Restrict a single piece of content or protect a group of posts by category or tag.', 'restrict-with-stripe')}</p>
                        <div className="columns">
                            <div className="column">
                                <h3>{__('For Posts and Pages', 'restrict-with-stripe', 'restrict-with-stripe')}</h3>
                                <ol>
                                    <li>{__('Edit the post or page', 'restrict-with-stripe')}</li>
                                    <li>{__('Open the Settings panel', 'restrict-with-stripe')}</li>
                                    <li>{__('Select Stripe Products in the "Restrict with Stripe" panel', 'restrict-with-stripe')}</li>
                                    <li>{__('Save changes', 'restrict-with-stripe')}</li>
                                </ol>
                                <a href={rwstripe.admin_url + 'edit.php?post_type=post'}>
                                    <Button isSecondary >
                                        {__('View Posts', 'restrict-with-stripe')}
                                    </Button>
                                </a> &nbsp;
                                <a href={rwstripe.admin_url + 'edit.php?post_type=page'}>
                                    <Button isSecondary >
                                        {__('View Pages', 'restrict-with-stripe')}
                                    </Button>
                                </a>
                            </div>
                            <div className="column">
                                gif here
                            </div>
                        </div>
                        <div className="columns">
                            <div className="column">
                                <h3>{__('For Categories and Tags', 'restrict-with-stripe', 'restrict-with-stripe')}</h3>
                                <ol>
                                    <li>{__('Edit the category or tag', 'restrict-with-stripe')}</li>
                                    <li>{__('Select Stripe Products', 'restrict-with-stripe')}</li>
                                    <li>{__('Save changes', 'restrict-with-stripe')}</li>
                                </ol>
                                <a href={rwstripe.admin_url + 'edit-tags.php?taxonomy=category'}>
                                    <Button isSecondary >
                                        {__('View Categories', 'restrict-with-stripe')}
                                    </Button>
                                </a> &nbsp;
                                <a href={rwstripe.admin_url + 'edit-tags.php?taxonomy=post_tag'}>
                                    <Button isSecondary >
                                        {__('View Tags', 'restrict-with-stripe')}
                                    </Button>
                                </a>
                            </div>
                            <div className="column">
                                gif here
                            </div>
                        </div>
                    </PanelBody>
                    <PanelBody title={__('Link to Stripe Customer Portal', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_account_id}>
                        <p>{__('The Customer Portal is a Stripe tool that allows customers to view previous payments and manage active subscriptions. Give customers a link to the portal using one of the methods below:', 'restrict-with-stripe')}</p>
                        <div className="columns">
                            <div className="column">
                                <h3>{__('Create a "Customer Portal" Menu Item', 'restrict-with-stripe', 'restrict-with-stripe')}</h3>
                                <ol>
                                    <li>{__('Edit the desired menu', 'restrict-with-stripe')}</li>
                                    <li>{__('In the "Restrict with Stripe" panel, select the "Stripe Customer Portal" menu item and click "Add to Menu"', 'restrict-with-stripe')}</li>
                                    <li>{__('Click "Save Menu"', 'restrict-with-stripe')}</li>
                                </ol>
                            </div>
                            <div className="column">
                                gif here
                            </div>
                        </div>
                        <div className="columns">
                            <div className="column">
                                <h3>{__('Use the "Stripe Customer Portal" Block', 'restrict-with-stripe', 'restrict-with-stripe')}</h3>
                                <ol>
                                    <li>{__('Edit the desired page', 'restrict-with-stripe')}</li>
                                    <li>{__('Insert the "Stripe Customer Portal" block', 'restrict-with-stripe')}</li>
                                    <li>{__('Save changes', 'restrict-with-stripe')}</li>
                                </ol>
                            </div>
                            <div className="column">
                                gif here
                            </div>
                        </div>
                    </PanelBody>
                    <PanelBody title={__('Customize Advanced Settings', 'restrict-with-stripe')} initialOpen={rwstripe.stripe_account_id} >
                        <p>{__('Confirm advanced settings for default behavior (optional).', 'restrict-with-stripe')}</p>
                        <ToggleControl
                            label={__('Show a content excerpt on restricted posts or pages', 'restrict-with-stripe')}
                            onChange={(rwstripe_show_excerpts) => this.setState({ rwstripe_show_excerpts })}
                            checked={rwstripe_show_excerpts}
                        />
                        <ToggleControl
                            label={__('Allow customers to choose a password during registration', 'restrict-with-stripe')}
                            onChange={(rwstripe_collect_password) => this.setState({ rwstripe_collect_password })}
                            checked={rwstripe_collect_password}
                        />
                        <p><Button
                            isPrimary
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
                        </Button></p>
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
