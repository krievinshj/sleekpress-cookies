/**
 * Minimal REST client for SleekPress admin apps.
 *
 * Plain fetch() against a (preferably path-relative, same-origin) restBase,
 * sending the WordPress `wp_rest` nonce as both the X-WP-Nonce header and a
 * _wpnonce query param. Config is resolved lazily on each request, so it
 * doesn't matter whether configureApi() runs before or after createApi().
 *
 * Note: deliberately NOT routed through wp.apiFetch — its automatic
 * nonce-refresh-and-retry can spin into a request loop on sites where the
 * nonce never verifies (host/cookie mismatch, stale admin-page cache, …).
 */

let defaults = { restBase: '', nonce: '' };

export function configureApi( cfg = {} ) {
	defaults = { ...defaults, ...cfg };
}

export function createApi( opts = {} ) {
	function resolved() {
		return {
			restBase: ( opts.restBase || defaults.restBase || '' ).replace( /\/$/, '' ),
			nonce: opts.nonce || defaults.nonce || '',
		};
	}

	async function request( method, path, body ) {
		const { restBase, nonce } = resolved();
		let url = restBase + ( path.startsWith( '/' ) ? path : '/' + path );
		if ( nonce ) {
			url += ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + '_wpnonce=' + encodeURIComponent( nonce );
		}
		const headers = { 'Content-Type': 'application/json' };
		if ( nonce ) {
			headers['X-WP-Nonce'] = nonce;
		}
		const res = await fetch( url, {
			method,
			credentials: 'same-origin',
			headers,
			body: body !== undefined ? JSON.stringify( body ) : undefined,
		} );
		let data = null;
		const text = await res.text();
		try { data = text ? JSON.parse( text ) : null; } catch ( e ) { data = text; }
		if ( ! res.ok ) {
			const msg = ( data && typeof data === 'object' && ( data.message || data.error || data.code ) ) || ( 'HTTP ' + res.status );
			const err = new Error( typeof msg === 'string' ? msg : ( 'HTTP ' + res.status ) );
			err.status = res.status;
			err.code = data && typeof data === 'object' ? data.code : undefined;
			err.data = data;
			throw err;
		}
		return data;
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
