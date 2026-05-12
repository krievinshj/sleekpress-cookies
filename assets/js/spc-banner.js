/* global SPC */
( function () {
	'use strict';

	if ( typeof SPC === 'undefined' ) {
		return;
	}

	var root = document.getElementById( 'spc-root' );
	if ( ! root ) {
		return;
	}

	window.dataLayer = window.dataLayer || [];
	function gtag() {
		window.dataLayer.push( arguments );
	}

	/* ---------- cookie helpers ---------- */

	function readConsentCookie() {
		var m = document.cookie.match( new RegExp( '(?:^|; )' + SPC.cookieName + '=([^;]*)' ) );
		if ( ! m ) {
			return null;
		}
		try {
			return JSON.parse( decodeURIComponent( m[ 1 ] ) );
		} catch ( e ) {
			return null;
		}
	}

	function writeConsentCookie( state ) {
		var days = SPC.expiryDays || 365;
		var d = new Date();
		d.setTime( d.getTime() + days * 864e5 );
		var secure = location.protocol === 'https:' ? '; Secure' : '';
		document.cookie =
			SPC.cookieName + '=' + encodeURIComponent( JSON.stringify( state ) ) +
			'; expires=' + d.toUTCString() + '; path=/; SameSite=Lax' + secure;
	}

	/* ---------- consent state ---------- */

	function defaultState() {
		var s = {};
		SPC.categories.forEach( function ( c ) {
			s[ c.key ] = !! c.locked; // locked (necessary) => true, rest => false.
		} );
		return s;
	}

	function allState( value ) {
		var s = {};
		SPC.categories.forEach( function ( c ) {
			s[ c.key ] = c.locked ? true : value;
		} );
		return s;
	}

	function applyConsent( state, persist ) {
		state = state || {};
		state.t = Math.floor( Date.now() / 1000 );
		state.v = 1;

		if ( persist ) {
			writeConsentCookie( state );
		}

		if ( SPC.gcmEnabled ) {
			var signals = {};
			Object.keys( SPC.gcmMap ).forEach( function ( cat ) {
				var granted = !! state[ cat ];
				( SPC.gcmMap[ cat ] || [] ).forEach( function ( sig ) {
					signals[ sig ] = granted ? 'granted' : 'denied';
				} );
			} );
			signals.security_storage = 'granted';
			gtag( 'consent', 'update', signals );
		}

		window.dataLayer.push( { event: 'spc_consent_update', spc_consent: state } );
	}

	/* ---------- report observed cookies (for the admin scanner) ---------- */

	function reportCookies() {
		if ( ! SPC.rest || ! SPC.rest.observe ) {
			return;
		}
		try {
			var names = {};
			document.cookie.split( ';' ).forEach( function ( pair ) {
				var name = pair.split( '=' )[ 0 ].trim();
				if ( name ) {
					names[ name ] = location.hostname;
				}
			} );
			if ( ! Object.keys( names ).length ) {
				return;
			}
			// Throttle: at most once per session.
			if ( sessionStorage.getItem( 'spc_reported' ) ) {
				return;
			}
			sessionStorage.setItem( 'spc_reported', '1' );
			fetch( SPC.rest.observe, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify( { cookies: names } ),
				credentials: 'same-origin',
				keepalive: true,
			} ).catch( function () {} );
		} catch ( e ) {}
	}

	/* ---------- DOM building ---------- */

	function el( tag, attrs, children ) {
		var n = document.createElement( tag );
		attrs = attrs || {};
		Object.keys( attrs ).forEach( function ( k ) {
			if ( k === 'class' ) {
				n.className = attrs[ k ];
			} else if ( k === 'html' ) {
				n.innerHTML = attrs[ k ];
			} else if ( k === 'text' ) {
				n.textContent = attrs[ k ];
			} else {
				n.setAttribute( k, attrs[ k ] );
			}
		} );
		( children || [] ).forEach( function ( c ) {
			if ( c ) {
				n.appendChild( c );
			}
		} );
		return n;
	}

	function privacyLink() {
		if ( ! SPC.privacyUrl ) {
			return null;
		}
		return el( 'a', { href: SPC.privacyUrl, class: 'spc-link', target: '_blank', rel: 'noopener', text: SPC.texts.privacyText } );
	}

	function buildBanner() {
		var box = el( 'div', { class: 'spc-banner spc-pos-' + SPC.position, role: 'dialog', 'aria-label': SPC.texts.title } );

		box.appendChild( el( 'h2', { class: 'spc-banner__title', text: SPC.texts.title } ) );

		var msg = el( 'div', { class: 'spc-banner__text' } );
		msg.innerHTML = SPC.texts.message;
		var pl = privacyLink();
		if ( pl ) {
			msg.appendChild( document.createTextNode( ' ' ) );
			msg.appendChild( pl );
		}
		box.appendChild( msg );

		var actions = el( 'div', { class: 'spc-banner__actions' } );
		var adjust = el( 'button', { type: 'button', class: 'spc-btn spc-btn--secondary', text: SPC.texts.adjust } );
		var decline = el( 'button', { type: 'button', class: 'spc-btn spc-btn--secondary', text: SPC.texts.decline } );
		var accept = el( 'button', { type: 'button', class: 'spc-btn spc-btn--primary', text: SPC.texts.accept } );
		actions.appendChild( adjust );
		actions.appendChild( decline );
		actions.appendChild( accept );
		box.appendChild( actions );

		if ( SPC.showBrand ) {
			box.appendChild( el( 'div', { class: 'spc-banner__brand', text: 'Powered by SleekPress Cookies' } ) );
		}

		accept.addEventListener( 'click', function () {
			applyConsent( allState( true ), true );
			closeBanner();
			showBadge();
		} );
		decline.addEventListener( 'click', function () {
			applyConsent( allState( false ), true );
			closeBanner();
			showBadge();
		} );
		adjust.addEventListener( 'click', function () {
			openModal();
		} );

		return box;
	}

	function buildModal( prefill ) {
		var overlay = el( 'div', { class: 'spc-modal', role: 'dialog', 'aria-modal': 'true', 'aria-label': SPC.texts.prefTitle } );
		var boxEl = el( 'div', { class: 'spc-modal__box' } );

		var head = el( 'div', { class: 'spc-modal__head' } );
		head.appendChild( el( 'h2', { text: SPC.texts.prefTitle } ) );
		var closeX = el( 'button', { type: 'button', class: 'spc-modal__close', 'aria-label': 'Close', html: '&times;' } );
		head.appendChild( closeX );
		boxEl.appendChild( head );

		var body = el( 'div', { class: 'spc-modal__body' } );
		var state = prefill || readConsentCookie() || defaultState();
		var inputs = {};

		SPC.categories.forEach( function ( c ) {
			var item = el( 'div', { class: 'spc-cat' } );
			var bar = el( 'div', { class: 'spc-cat__bar' } );
			bar.appendChild( el( 'span', { class: 'spc-cat__label', text: c.label } ) );

			if ( c.locked ) {
				bar.appendChild( el( 'span', { class: 'spc-cat__always', text: SPC.texts.always } ) );
			} else {
				var lbl = el( 'label', { class: 'spc-switch' } );
				var input = el( 'input', { type: 'checkbox' } );
				if ( state[ c.key ] ) {
					input.checked = true;
				}
				inputs[ c.key ] = input;
				lbl.appendChild( input );
				lbl.appendChild( el( 'span', { class: 'spc-switch__track' } ) );
				bar.appendChild( lbl );
			}
			item.appendChild( bar );
			item.appendChild( el( 'p', { class: 'spc-cat__desc', text: c.description } ) );

			if ( c.cookies && c.cookies.length ) {
				var toggle = el( 'button', { type: 'button', class: 'spc-cat__toggle', text: SPC.texts.showCookies + ' (' + c.cookies.length + ')' } );
				var list = el( 'div', { class: 'spc-cookies' } );
				list.style.display = 'none';
				c.cookies.forEach( function ( ck ) {
					var row = el( 'div', { class: 'spc-cookie-item' } );
					row.appendChild( el( 'div', { class: 'spc-cookie-item__name', text: ck.name } ) );
					var meta = [];
					if ( ck.provider ) { meta.push( ck.provider ); }
					if ( ck.duration ) { meta.push( ck.duration ); }
					if ( meta.length ) {
						row.appendChild( el( 'div', { class: 'spc-cookie-item__meta', text: meta.join( ' · ' ) } ) );
					}
					if ( ck.description ) {
						row.appendChild( el( 'div', { class: 'spc-cookie-item__desc', text: ck.description } ) );
					}
					list.appendChild( row );
				} );
				toggle.addEventListener( 'click', function () {
					var open = list.style.display !== 'none';
					list.style.display = open ? 'none' : 'block';
					toggle.textContent = ( open ? SPC.texts.showCookies : SPC.texts.hideCookies ) + ' (' + c.cookies.length + ')';
				} );
				item.appendChild( toggle );
				item.appendChild( list );
			}
			body.appendChild( item );
		} );
		boxEl.appendChild( body );

		var foot = el( 'div', { class: 'spc-modal__foot' } );
		var declineBtn = el( 'button', { type: 'button', class: 'spc-btn spc-btn--secondary', text: SPC.texts.decline } );
		var saveBtn = el( 'button', { type: 'button', class: 'spc-btn spc-btn--secondary', text: SPC.texts.save } );
		var acceptBtn = el( 'button', { type: 'button', class: 'spc-btn spc-btn--primary', text: SPC.texts.accept } );
		foot.appendChild( declineBtn );
		foot.appendChild( saveBtn );
		foot.appendChild( acceptBtn );
		boxEl.appendChild( foot );

		var pl = privacyLink();
		if ( pl ) {
			var plWrap = el( 'div', { class: 'spc-modal__privacy' } );
			plWrap.appendChild( pl );
			boxEl.appendChild( plWrap );
		}

		overlay.appendChild( boxEl );

		function done( newState ) {
			applyConsent( newState, true );
			overlay.remove();
			closeBanner();
			showBadge();
		}
		acceptBtn.addEventListener( 'click', function () { done( allState( true ) ); } );
		declineBtn.addEventListener( 'click', function () { done( allState( false ) ); } );
		saveBtn.addEventListener( 'click', function () {
			var s = defaultState();
			Object.keys( inputs ).forEach( function ( k ) { s[ k ] = inputs[ k ].checked; } );
			done( s );
		} );
		closeX.addEventListener( 'click', function () { overlay.remove(); } );
		overlay.addEventListener( 'click', function ( e ) { if ( e.target === overlay ) { overlay.remove(); } } );

		return overlay;
	}

	/* ---------- show / hide ---------- */

	var bannerEl = null;
	var badgeEl = null;

	function closeBanner() {
		if ( bannerEl ) {
			bannerEl.remove();
			bannerEl = null;
		}
	}

	function openBanner() {
		if ( bannerEl ) {
			return;
		}
		bannerEl = buildBanner();
		root.appendChild( bannerEl );
	}

	function openModal( prefill ) {
		var m = buildModal( prefill );
		root.appendChild( m );
	}

	var COOKIE_ICON = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true" focusable="false"><path d="M89.284 54.498c-7.469-1.128-13.726-5.931-16.876-12.515-.105.01-.2.01-.295.01-12.398 0-22.426-10.038-22.426-22.415 0-2.644.463-5.183 1.306-7.542.822-2.286-.758-4.762-3.192-4.772h-.231C21.625 7.264.789 29.195 2.61 55.53c1.57 22.68 20.257 40.85 42.968 41.83 23.743 1.022 43.641-16.328 46.675-39.018.253-1.864-1.106-3.57-2.97-3.844m-68.291 8.7c-.411.39-.938.58-1.454.58-.558 0-1.117-.21-1.527-.643l-3.171-3.318c-.8-.843-.77-2.18.074-2.98.842-.802 2.18-.77 2.98.073l3.16 3.307c.812.843.78 2.181-.062 2.982m5.888-20.52a5.09 5.09 0 0 1-5.088-5.087 5.1 5.1 0 0 1 5.088-5.088 5.09 5.09 0 0 1 5.088 5.088 5.085 5.085 0 0 1-5.088 5.088m9.08-24.649 3.445-.895a2.1 2.1 0 0 1 2.57 1.496 2.09 2.09 0 0 1-1.496 2.57l-3.455.906c-.179.053-.358.074-.527.074-.937 0-1.79-.632-2.043-1.57a2.115 2.115 0 0 1 1.506-2.58m3.982 70.44a5.035 5.035 0 0 1-5.035-5.035 5.033 5.033 0 0 1 5.035-5.025 5.033 5.033 0 0 1 5.035 5.025 5.035 5.035 0 0 1-5.035 5.035m11.208-31.707a2.1 2.1 0 0 1-1.99 1.422c-.233 0-.454-.031-.675-.105l-4.192-1.422a2.104 2.104 0 1 1 1.358-3.982l4.182 1.412a2.107 2.107 0 0 1 1.317 2.675m9.997 3.95a4.635 4.635 0 1 1 9.27 0 4.635 4.635 0 0 1-9.27 0M74.23 83.435c-.411.464-.98.695-1.56.695-.505 0-1.01-.179-1.41-.537l-2.255-2.044a2.1 2.1 0 0 1-.148-2.97 2.1 2.1 0 0 1 2.971-.148l2.254 2.033c.864.78.938 2.107.148 2.971M70.365 17.281c4.003-1.506 8.142-5.72 8.522-7.342.927-3.95-3.814-7.342-8.522-7.342s-8.522 3.287-8.522 7.342c0 4.056 4.108 8.986 8.522 7.342M85.628 25.656c-2.728 1.58-4.445 6.268-1.622 8.595 2.56 2.107 7.016 3.245 8.248 2.876 3.013-.916 3.455-5.425 1.622-8.595-1.833-3.16-5.52-4.456-8.248-2.876M94.003 7.538a3.504 3.504 0 0 0-3.508 3.497 3.507 3.507 0 0 0 3.508 3.508 3.502 3.502 0 0 0 0-7.005"/></svg>';

	function showBadge() {
		if ( ! SPC.showBadge || badgeEl ) {
			return;
		}
		badgeEl = el( 'button', { type: 'button', class: 'spc-badge spc-pos-' + SPC.position, 'aria-label': SPC.texts.prefTitle, html: COOKIE_ICON } );
		badgeEl.addEventListener( 'click', function () { openModal(); } );
		root.appendChild( badgeEl );
	}

	// Expose for the [sleekpress_cookie_settings] shortcode / .spc-open-prefs links.
	document.addEventListener( 'click', function ( e ) {
		var t = e.target.closest ? e.target.closest( '.spc-open-prefs' ) : null;
		if ( t ) {
			e.preventDefault();
			openModal();
		}
	} );

	/* ---------- init ---------- */

	var stored = SPC.consent || readConsentCookie();
	if ( stored ) {
		// The <head> script already replayed the consent update + dataLayer
		// event when the cookie was present server-side AND Consent Mode is on.
		// Only (re)apply here if that didn't happen — e.g. a cached page where
		// the cookie was set after the HTML was generated, or GCM disabled.
		if ( ! ( SPC.hasConsent && SPC.gcmEnabled ) ) {
			applyConsent( stored, false );
		}
		showBadge();
	} else {
		openBanner();
	}

	// Report cookies seen in this browser (deferred, low priority).
	if ( 'requestIdleCallback' in window ) {
		requestIdleCallback( reportCookies );
	} else {
		setTimeout( reportCookies, 3000 );
	}
} )();
