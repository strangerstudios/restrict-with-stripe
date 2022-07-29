import './style.scss';

import apiFetch from '@wordpress/api-fetch';

import {
    Button,
    Panel,
    PanelBody,
    PanelRow,
    Placeholder,
    SnackbarList,
    Spinner,
    TextControl,
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
        ( select ) =>
            select( noticesStore )
                .getNotices()
                .filter( ( notice ) => notice.type === 'snackbar' ),
        []
    );
    const { removeNotice } = useDispatch( noticesStore );
    return (
        <SnackbarList
            className="edit-site-notices"
            notices={ notices }
            onRemove={ removeNotice }
        />
    );
};

class App extends Component {
    constructor() {
        super( ...arguments );

        this.state = {
            logged_out_message: '',
            logged_out_button_text: '',
            logged_in_message: '',
            logged_in_button_text: '',
            not_purchasable_message: '',
            isAPILoaded: false,
        };
    }

    componentDidMount() {
        // GET
        apiFetch( { path: '/wp/v2/settings' } ).then( ( settings ) => {
            if ( settings.rwstripe_restricted_content_message ) {
                this.setState( {
                    logged_out_message: settings.rwstripe_restricted_content_message.logged_out_message,
                    logged_out_button_text: settings.rwstripe_restricted_content_message.logged_out_button_text,
                    logged_in_message: settings.rwstripe_restricted_content_message.logged_in_message,
                    logged_in_button_text: settings.rwstripe_restricted_content_message.logged_in_button_text,
                    not_purchasable_message: settings.rwstripe_restricted_content_message.not_purchasable_message,
                    isAPILoaded: true,
                } );
            }
        } );
    }

    render() {
        const {
            logged_out_message,
            logged_out_button_text,
            logged_in_message,
            logged_in_button_text,
            not_purchasable_message,
            isAPILoaded,
        } = this.state;

        if ( ! isAPILoaded ) {
            return (
                <Placeholder>
                    <Spinner />
                </Placeholder>
            );
        }

        const stripe_connect_button = (
            rwstripe.stripe_user_id ?
                <a href={ rwstripe.stripe_connect_url } class="rwstripe-stripe-connect">
                    <span>
                        { __( 'Disconnect From Stripe', 'restrict-with-stripe' ) }
                    </span>
                </a> : 
                <a href={ rwstripe.stripe_connect_url } class="rwstripe-stripe-connect">
                    <span>
                        { __( 'Connect To Stripe', 'restrict-with-stripe' ) }
                    </span>
                </a>
        );

        return (
            <Fragment>
                <div className="rwstripe-settings__header">
                    <div className="rwstripe-settings__container">
                        <div className="rwstripe-settings__title">
                            <h1>{ __( 'Restrict With Stripe Settings', 'restrict-with-stripe' ) }</h1>
                        </div>
                    </div>
                </div>

                <div className="rwstripe-settings__main">
                    <Panel>
                        <PanelBody title={ __( 'Step 1: Connect to Stripe', 'restrict-with-stripe' ) } >
                            { stripe_connect_button }
                        </PanelBody>
                        <PanelBody title={ __( 'Step 2: Restrict Content', 'restrict-with-stripe' ) } >
                            { __( '[Add link to create Stripe Product]', 'restrict-with-stripe' ) }
                            { __( '[Add instructions for restricting site content]', 'restrict-with-stripe' ) }
                        </PanelBody>
                        <PanelBody
                            title={ __( 'Step 3: Advanced Settings', 'restrict-with-stripe' ) } >
                            <PanelRow>
                                <Panel>
                                    <PanelBody title={ __( 'Restricted Content Message for Logged Out Users', 'restrict-with-stripe' ) } initialOpen={ false } >
                                        <TextControl
                                            help={ __( 'Use !!login_url!! to generate a URL to the site\'s login page.', 'restrict-with-stripe' ) }
                                            label={ __( 'Message', 'restrict-with-stripe' ) }
                                            onChange={ ( logged_out_message ) => this.setState( { logged_out_message } ) }
                                            value={ logged_out_message }
                                        />
                                        <TextControl
                                            label={ __( 'Button Text', 'restrict-with-stripe' ) }
                                            onChange={ ( logged_out_button_text ) => this.setState( { logged_out_button_text } ) }
                                            value={ logged_out_button_text }
                                        />
                                    </PanelBody>
                                    <PanelBody title={ __( 'Restricted Content Message for Logged In Users', 'restrict-with-stripe' ) } initialOpen={ false } >
                                        <TextControl
                                            label={ __( 'Message', 'restrict-with-stripe' ) }
                                            onChange={ ( logged_in_message ) => this.setState( { examlogged_in_messagepleText2 } ) }
                                            value={ logged_in_message }
                                        />
                                        <TextControl
                                            label={ __( 'Button Text', 'restrict-with-stripe' ) }
                                            onChange={ ( logged_in_button_text ) => this.setState( { logged_in_button_text } ) }
                                            value={ logged_in_button_text }
                                        />
                                    </PanelBody>
                                    <PanelBody title={ __( 'Restricted Content Message when a Product is not Purchasable', 'restrict-with-stripe' ) } initialOpen={ false } >
                                        <TextControl
                                            label={ __( 'Message', 'restrict-with-stripe' ) }
                                            onChange={ ( not_purchasable_message ) => this.setState( { not_purchasable_message } ) }
                                            value={ not_purchasable_message }
                                        />
                                    </PanelBody>
                                </Panel>
                            </PanelRow>
                        </PanelBody>
                        <Button
                            isPrimary
                            isLarge
                            onClick={ () => {
                                const {
                                    logged_out_message,
                                    logged_out_button_text,
                                    logged_in_message,
                                    logged_in_button_text,
                                    not_purchasable_message,
                                } = this.state;

                                // POST
                                apiFetch( {
                                    path: '/wp/v2/settings',
                                    method: 'POST',
                                    data: {
                                        [ 'rwstripe_restricted_content_message' ]: {
                                            logged_out_message: logged_out_message,
                                            logged_out_button_text: logged_out_button_text,
                                            logged_in_message: logged_in_message,
                                            logged_in_button_text: logged_in_button_text,
                                            not_purchasable_message: not_purchasable_message,
                                        },
                                    },
                                } ).then( ( res ) => {
                                    dispatch('core/notices').createNotice(
                                        'success',
                                        __( 'Settings Saved', 'restrict-with-stripe' ),
                                        {
                                            type: 'snackbar',
                                            isDismissible: true,
                                        }
                                    );
                                }, ( err ) => {
                                    dispatch('core/notices').createNotice(
                                        'error',
                                        __( 'Save Failed', 'restrict-with-stripe' ),
                                        {
                                            type: 'snackbar',
                                            isDismissible: true,
                                        }
                                    );
                                } );
                            }}
                        >
                            { __( 'Save', 'restrict-with-stripe' ) }
                        </Button>
                    </Panel>
                </div>

                <div className="rwstripe-settings__notices">
                    <Notices/>
                </div>

            </Fragment>
        )
    }
}

document.addEventListener( 'DOMContentLoaded', () => {
    const htmlOutput = document.getElementById( 'rwstripe-settings' );

    if ( htmlOutput ) {
        render(
            <App />,
            htmlOutput
        );
    }
});
