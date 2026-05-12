/**
 * Minimal REST client for SleekPress admin apps.
 *
 * Two transports, picked automatically from the config:
 *  - admin-ajax  (preferred in wp-admin): if `ajaxUrl` + `ajaxAction` are
 *    present, requests go through /wp-admin/admin-ajax.php. That URL is under
 *    the /wp-admin/ cookie path, so the auth cookie is always sent — far more
 *    reliable than /wp-json/ on some hosts. The server-side handler proxies to
 *    the matching REST route.
 *  - REST        (fallback): plain fetch() against `restBase`, sending the
 *    `wp_rest` nonce as both the X-WP-Nonce header and a _wpnonce query param.
 *
 * Config is resolved lazily on each request, so it doesn't matter whether
 * configureApi() runs before or after createApi() (ES module load order).
 */

let defaults = { restBase: '', nonce: '', ajaxUrl: '', ajaxAction: '', ajaxNonce: '' };

export function configureApi( cfg = {} ) {
	defaults = { ...defaults, ...cfg };
}

function buildError( payload, status ) {
	const obj = payload && typeof payload === 'object' ? payload : { message: 'HTTP ' + status };
	const msg = obj.message || obj.error || obj.code || ( 'HTTP ' + status );
	const err = new Error( typeof msg === 'string' ? msg : ( 'HTTP ' + status ) );
	err.status = ( obj.data && obj.data.status ) || status;
	err.code = obj.code;
	err.data = obj;
	return err;
}

export function createApi( opts = {} ) {
	function cfg() {
		return {
			restBase: ( opts.restBase || defaults.restBase || '' ).replace( /\/$/, '' ),
			nonce: opts.nonce || defaults.nonce || '',
			ajaxUrl: opts.ajaxUrl || defaults.ajaxUrl || '',
			ajaxAction: opts.ajaxAction || defaults.ajaxAction || '',
			ajaxNonce: opts.ajaxNonce || defaults.ajaxNonce || '',
		};
	}

	async function viaAjax( c, method, path, body ) {
		const form = new URLSearchParams();
		form.set( 'action', c.ajaxAction );
		form.set( 'nonce', c.ajaxNonce );
		form.set( 'route', path.replace( /^\/+/, '' ) );
		form.set( 'method', method );
		if ( body !== undefined ) {
			form.set( 'payload', JSON.stringify( body ) );
		}
		const res = await fetch( c.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: form.toString(),
		} );
		const text = await res.text();
		let data = null;
		try { data = text ? JSON.parse( text ) : null; } catch ( e ) { data = text; }
		// admin-ajax returns "0"/"-1" for bad action / failed nonce.
		if ( data === 0 || data === '0' || data === -1 || data === '-1' ) {
			throw buildError( { code: 'spc_ajax_rejected', message: 'Request rejected (bad nonce or action). Try reloading the page.' }, res.status || 403 );
		}
		if ( ! res.ok ) {
			throw buildError( data, res.status );
		}
		return data;
	}

	async function viaRest( c, method, path, body ) {
		let url = c.restBase + ( path.startsWith( '/' ) ? path : '/' + path );
		if ( c.nonce ) {
			url += ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + '_wpnonce=' + encodeURIComponent( c.nonce );
		}
		const headers = { 'Content-Type': 'application/json' };
		if ( c.nonce ) {
			headers['X-WP-Nonce'] = c.nonce;
		}
		const res = await fetch( url, {
			method,
			credentials: 'same-origin',
			headers,
			body: body !== undefined ? JSON.stringify( body ) : undefined,
		} );
		const text = await res.text();
		let data = null;
		try { data = text ? JSON.parse( text ) : null; } catch ( e ) { data = text; }
		if ( ! res.ok ) {
			throw buildError( data, res.status );
		}
		return data;
	}

	function request( method, path, body ) {
		const c = cfg();
		return ( c.ajaxUrl && c.ajaxAction )
			? viaAjax( c, method, path, body )
			: viaRest( c, method, path, body );
	}

	return {
		get: ( path ) => request( 'GET', path ),
		post: ( path, body ) => request( 'POST', path, body ?? {} ),
		put: ( path, body ) => request( 'PUT', path, body ?? {} ),
		del: ( path ) => request( 'DELETE', path ),
		raw: request,
	};
}

export function useApi( opts ) {
	return createApi( opts );
}
