import apiFetch from '@wordpress/api-fetch';

import {
    Button,
    Notice,
    Spinner,
    TextControl,
} from '@wordpress/components';

import { Fragment, useState } from '@wordpress/element';

import { __ } from '@wordpress/i18n';

const statusLabels = {
    ok: __( 'Granted', 'restrict-with-stripe' ),
    unverified: __( 'Verified on first use', 'restrict-with-stripe' ),
    missing: __( 'Missing', 'restrict-with-stripe' ),
    error: __( 'Could not check', 'restrict-with-stripe' ),
};

const accessLabel = ( access ) => 'write' === access ? __( 'Write', 'restrict-with-stripe' ) : __( 'Read', 'restrict-with-stripe' );

/**
 * Show the result of a restricted key permission check.
 */
const PermissionReport = ( { report } ) => {
    if ( ! report ) {
        return null;
    }
    if ( report.invalid_key ) {
        return (
            <Notice status="error" isDismissible={ false }>
                <p><strong>{ __( 'Check your restricted key.', 'restrict-with-stripe' ) }</strong> { __( 'Stripe rejected this key. Create a new restricted key in Stripe and save it here.', 'restrict-with-stripe' ) }</p>
                { report.message && <p>{ report.message }</p> }
            </Notice>
        );
    }
    return (
        <Fragment>
            <Notice status={ report.ok ? 'success' : 'warning' } isDismissible={ false }>
                <p>{ report.ok ? __( 'Your restricted key has every permission Restrict With Stripe can check.', 'restrict-with-stripe' ) : __( 'Your restricted key is missing permissions. Edit the key in Stripe, then check again.', 'restrict-with-stripe' ) }</p>
            </Notice>
            <table className="widefat striped rwstripe-permissions">
                <thead>
                    <tr>
                        <th>{ __( 'Permission', 'restrict-with-stripe' ) }</th>
                        <th>{ __( 'Status', 'restrict-with-stripe' ) }</th>
                        <th>{ __( 'Details', 'restrict-with-stripe' ) }</th>
                    </tr>
                </thead>
                <tbody>
                    { report.results.map( ( result ) => (
                        <tr key={ result.resource } className={ 'rwstripe-permission-' + result.status }>
                            <td>
                                <strong>{ result.label }: { accessLabel( result.access ) }</strong>
                                <br /><small>{ result.used_for }</small>
                            </td>
                            <td>{ statusLabels[ result.status ] || result.status }</td>
                            <td>{ result.message }</td>
                        </tr>
                    ) ) }
                </tbody>
            </table>
            { report.checked_at && (
                <p><small>{ __( 'Last checked:', 'restrict-with-stripe' ) } { new Date( report.checked_at * 1000 ).toLocaleString() }</small></p>
            ) }
        </Fragment>
    );
};

/**
 * Form to save the site's own restricted key.
 */
export const RestrictedKeyForm = () => {
    const [ key, setKey ] = useState( '' );
    const [ saving, setSaving ] = useState( false );
    const [ error, setError ] = useState( '' );

    const save = () => {
        setSaving( true );
        setError( '' );
        apiFetch( {
            path: 'rwstripe/v1/restricted_key',
            method: 'POST',
            data: { restricted_key: key.trim() },
        } ).then( () => {
            // Reload so every part of the page uses the new credential.
            window.location.reload();
        } ).catch( ( err ) => {
            setSaving( false );
            setError( err.message || __( 'The key could not be saved.', 'restrict-with-stripe' ) );
        } );
    };

    return (
        <div className="rwstripe-restricted-key">
            <h3>{ __( 'Or use your own restricted key', 'restrict-with-stripe' ) }</h3>
            <p>{ __( 'Create a restricted key in your Stripe Dashboard with these permissions, then paste it here. No Restrict With Stripe application fee is added to payments made with your own key.', 'restrict-with-stripe' ) }</p>
            <ul className="rwstripe-required-permissions">
                { ( rwstripe.required_permissions || [] ).map( ( permission ) => (
                    <li key={ permission.resource }>
                        <strong>{ permission.label }: { accessLabel( permission.access ) }</strong>
                        { ' ' }&mdash; { permission.used_for }
                    </li>
                ) ) }
            </ul>
            { error && (
                <Notice status="error" isDismissible={ false }>
                    <p>{ error }</p>
                </Notice>
            ) }
            <TextControl
                label={ __( 'Restricted key', 'restrict-with-stripe' ) }
                help={ __( 'Starts with rk_live_ or rk_test_. A secret key (sk_) also works, but Stripe recommends a restricted key. The environment is set by the key.', 'restrict-with-stripe' ) }
                type="password"
                autoComplete="off"
                value={ key }
                onChange={ setKey }
            />
            <Button isSecondary disabled={ saving || ! key.trim() } onClick={ save }>
                { saving ? __( 'Saving and checking permissions…', 'restrict-with-stripe' ) : __( 'Save Restricted Key', 'restrict-with-stripe' ) }
            </Button>
            { saving && <Spinner /> }
        </div>
    );
};

/**
 * Details, permission check, and removal for a saved restricted key.
 */
export const RestrictedKeyDetails = ( { details } ) => {
    const [ report, setReport ] = useState( details.permissions );
    const [ busy, setBusy ] = useState( false );
    const [ error, setError ] = useState( '' );

    const check = () => {
        setBusy( true );
        setError( '' );
        apiFetch( {
            path: 'rwstripe/v1/restricted_key/check',
            method: 'POST',
        } ).then( ( result ) => {
            setReport( result );
            setBusy( false );
        } ).catch( ( err ) => {
            setError( err.message || __( 'The key could not be checked.', 'restrict-with-stripe' ) );
            setBusy( false );
        } );
    };

    const remove = () => {
        if ( ! window.confirm( __( 'Remove your restricted key? Restrict With Stripe will stop working until you save a new key or connect with Stripe.', 'restrict-with-stripe' ) ) ) {
            return;
        }
        setBusy( true );
        apiFetch( {
            path: 'rwstripe/v1/restricted_key',
            method: 'DELETE',
        } ).then( () => {
            window.location.reload();
        } ).catch( ( err ) => {
            setError( err.message || __( 'The key could not be removed.', 'restrict-with-stripe' ) );
            setBusy( false );
        } );
    };

    return (
        <div className="rwstripe-restricted-key">
            <p>
                <strong>{ __( 'Active credential:', 'restrict-with-stripe' ) }</strong>{ ' ' }
                { 'secret' === details.key_type ? __( 'Your secret key', 'restrict-with-stripe' ) : __( 'Your restricted key', 'restrict-with-stripe' ) }{ ' ' }
                <code>{ details.masked_key }</code>{ ' ' }
                ({ 'live' === details.environment ? __( 'Live Mode', 'restrict-with-stripe' ) : __( 'Test Mode', 'restrict-with-stripe' ) })
            </p>
            { details.account_id && (
                <p>{ __( 'Stripe account:', 'restrict-with-stripe' ) } { details.account_id }</p>
            ) }
            <p><small className="description">{ __( 'Stripe Connect is not in use. No Restrict With Stripe application fee is added to payments made with your own key.', 'restrict-with-stripe' ) }</small></p>
            { 'secret' === details.key_type && (
                <Notice status="warning" isDismissible={ false }>
                    <p>{ __( 'You saved a secret key, which has full access to your Stripe account. Stripe recommends a restricted key with only the permissions this plugin needs.', 'restrict-with-stripe' ) }</p>
                </Notice>
            ) }
            { error && (
                <Notice status="error" isDismissible={ false }>
                    <p>{ error }</p>
                </Notice>
            ) }
            <PermissionReport report={ report } />
            <p>
                <Button isSecondary disabled={ busy } onClick={ check }>
                    { __( 'Check Key Permissions', 'restrict-with-stripe' ) }
                </Button>{ ' ' }
                <Button isDestructive variant="tertiary" disabled={ busy } onClick={ remove }>
                    { __( 'Remove Key', 'restrict-with-stripe' ) }
                </Button>
                { busy && <Spinner /> }
            </p>
        </div>
    );
};
