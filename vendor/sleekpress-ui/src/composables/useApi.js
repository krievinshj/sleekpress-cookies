/**
 * Minimal REST client for SleekPress admin apps.
 *
 * In wp-admin it routes through window.wp.apiFetch when available — that gives
 * us WordPress's managed `wp_rest` nonce (auto-refreshed on expiry) for free.
 * Outside wp-admin (or if wp-api-fetch isn't loaded) it falls back to a plain
 * fetch() using the restBase / nonce supplied via configureApi().
 *
 * Config is resolved lazily on each request, so it doesn't matter whether
 * configureApi() runs before or after a module calls createApi().
 */

let defaults = { restBase: '', nonce: '' };

export function configureApi( cfg = {} ) {
	defaults = { ...defaults, ...cfg };
}

function wpApiFetch() {
	return typeof window !== 'undefined' && window.wp && typeof window.wp.apiFetch === 'function'
		? window.wp.apiFetch
		: null;
}

function normalizeError( e, fallbackStatus ) {
	if ( e instanceof Error && ! e.data ) {
		e.status = e.status || fallbackStatus;
		return e;
	}
	const msg = ( e && ( e.message || e.error || e.code ) ) || 'Request failed';
	const err = new Error( typeof msg === 'string' ? msg : 'Request failed' );
	err.status = ( e && e.data && e.data.status ) || fallbackStatus;
	err.data = e;
	return err;
}

export function createApi( opts = {} ) {
	function resolved() {
		return {
			restBase: ( opts.restBase || defaults.restBase || '' ).replace( /\/$/, '' ),
			nonce: opts.nonce || defaults.nonce || '',
		};
	}

	function fullUrl( path ) {
		const { restBase } = resolved();
		return restBase + ( path.startsWith( '/' ) ? path : '/' + path );
	}

	async function viaWp( method, path, body ) {
		const args = { url: fullUrl( path ), method };
		if ( body !== undefined ) {
			args.data = body;
		}
		try {
			return await wpApiFetch()( args );
		} catch ( e ) {
			throw normalizeError( e );
		}
	}

	async function viaFetch( method, path, body ) {
		const { nonce } = resolved();
		let url = fullUrl( path );
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
			throw normalizeError(
				( data && typeof data === 'object' ) ? data : { message: 'HTTP ' + res.status },
				res.status
			);
		}
		return data;
	}

	function request( method, path, body ) {
		return wpApiFetch() ? viaWp( method, path, body ) : viaFetch( method, path, body );
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
