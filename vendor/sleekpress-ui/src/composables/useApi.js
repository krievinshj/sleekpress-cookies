/**
 * Minimal REST client for SleekPress admin apps.
 *
 * Reads defaults from a global config object that the PHP loader prints
 * (window[ configKey ] = { restBase, nonce }). The config is resolved lazily on
 * each request, so it doesn't matter whether configureApi() runs before or
 * after a module calls createApi() (ES module load order).
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
		const url = restBase + ( path.startsWith( '/' ) ? path : '/' + path );
		const res = await fetch( url, {
			method,
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: body !== undefined ? JSON.stringify( body ) : undefined,
		} );
		let data = null;
		const text = await res.text();
		try { data = text ? JSON.parse( text ) : null; } catch ( e ) { data = text; }
		if ( ! res.ok ) {
			const msg = ( data && ( data.message || data.error ) ) || `HTTP ${ res.status }`;
			const err = new Error( msg );
			err.status = res.status;
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
