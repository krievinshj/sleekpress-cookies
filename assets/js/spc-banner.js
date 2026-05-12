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

	function showBadge() {
		if ( ! SPC.showBadge || badgeEl ) {
			return;
		}
		badgeEl = el( 'button', { type: 'button', class: 'spc-badge spc-pos-' + SPC.position, 'aria-label': SPC.texts.prefTitle, html: '<span class="spc-badge__icon">🍪</span>' } );
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
