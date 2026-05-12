/* global SPCAdmin */
( function () {
	'use strict';

	/* ---------------- Cookie scanner tab ---------------- */

	var scanBtn = document.getElementById( 'spc-scan-btn' );
	var aiBtn = document.getElementById( 'spc-ai-btn' );
	var addBtn = document.getElementById( 'spc-add-btn' );
	var statusEl = document.getElementById( 'spc-scan-status' );
	var resultsEl = document.getElementById( 'spc-scan-results' );

	function api( path, body ) {
		return fetch( SPCAdmin.restBase + path, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': SPCAdmin.nonce,
			},
			credentials: 'same-origin',
			body: JSON.stringify( body || {} ),
		} ).then( function ( r ) {
			return r.json().then( function ( j ) {
				if ( ! r.ok ) {
					throw new Error( ( j && j.error ) || ( 'HTTP ' + r.status ) );
				}
				return j;
			} );
		} );
	}

	function setStatus( msg, kind ) {
		if ( ! statusEl ) { return; }
		statusEl.textContent = msg || '';
		statusEl.className = 'spc-status' + ( kind ? ' spc-status--' + kind : '' );
	}

	function catSelect( value ) {
		var sel = document.createElement( 'select' );
		Object.keys( SPCAdmin.categories ).forEach( function ( k ) {
			var o = document.createElement( 'option' );
			o.value = k;
			o.textContent = SPCAdmin.categories[ k ];
			if ( k === value ) { o.selected = true; }
			sel.appendChild( o );
		} );
		return sel;
	}

	function td( child ) {
		var c = document.createElement( 'td' );
		if ( typeof child === 'string' ) { c.textContent = child; }
		else if ( child ) { c.appendChild( child ); }
		return c;
	}

	function renderResults( data ) {
		resultsEl.innerHTML = '';
		var cookies = data.cookies || [];
		if ( data.scanned_urls && data.scanned_urls.length ) {
			var p = document.createElement( 'p' );
			p.className = 'description';
			p.textContent = 'Scanned: ' + data.scanned_urls.join( ', ' );
			resultsEl.appendChild( p );
		}
		if ( data.errors && data.errors.length ) {
			var pe = document.createElement( 'p' );
			pe.className = 'spc-status spc-status--warn';
			pe.textContent = 'Warnings: ' + data.errors.join( ' | ' );
			resultsEl.appendChild( pe );
		}
		if ( ! cookies.length ) {
			resultsEl.appendChild( document.createTextNode( SPCAdmin.i18n.noResults ) );
			return;
		}

		var table = document.createElement( 'table' );
		table.className = 'widefat striped';
		table.innerHTML = '<thead><tr>' +
			'<th><input type="checkbox" id="spc-check-all"></th>' +
			'<th>Cookie</th><th>Provider</th><th>Duration</th><th>Domain</th>' +
			'<th>Category</th><th>Description</th><th>Source</th><th>In list?</th>' +
			'</tr></thead>';
		var tbody = document.createElement( 'tbody' );

		cookies.forEach( function ( ck, i ) {
			var tr = document.createElement( 'tr' );
			tr.dataset.idx = i;

			var cbCell = document.createElement( 'td' );
			var cb = document.createElement( 'input' );
			cb.type = 'checkbox';
			cb.className = 'spc-row-check';
			cb.addEventListener( 'change', refreshButtons );
			cbCell.appendChild( cb );
			tr.appendChild( cbCell );

			var nameStrong = document.createElement( 'code' );
			nameStrong.textContent = ck.name;
			tr.appendChild( td( nameStrong ) );

			var provInput = document.createElement( 'input' );
			provInput.type = 'text'; provInput.value = ck.provider || ''; provInput.className = 'spc-f-provider'; provInput.style.width = '120px';
			tr.appendChild( td( provInput ) );

			var durInput = document.createElement( 'input' );
			durInput.type = 'text'; durInput.value = ck.duration || ''; durInput.className = 'spc-f-duration'; durInput.style.width = '110px';
			tr.appendChild( td( durInput ) );

			var domInput = document.createElement( 'input' );
			domInput.type = 'text'; domInput.value = ck.domain || ''; domInput.className = 'spc-f-domain'; domInput.style.width = '110px';
			tr.appendChild( td( domInput ) );

			var sel = catSelect( ck.category || 'others' );
			sel.className = 'spc-f-category';
			tr.appendChild( td( sel ) );

			var descArea = document.createElement( 'textarea' );
			descArea.rows = 2; descArea.value = ck.description || ''; descArea.className = 'spc-f-description'; descArea.style.minWidth = '220px';
			tr.appendChild( td( descArea ) );

			tr.appendChild( td( ck.source || '' ) );
			tr.appendChild( td( ck.known ? '✓' : '—' ) );

			tbody.appendChild( tr );
		} );

		table.appendChild( tbody );
		resultsEl.appendChild( table );

		var all = document.getElementById( 'spc-check-all' );
		all.addEventListener( 'change', function () {
			tbody.querySelectorAll( '.spc-row-check' ).forEach( function ( c ) { c.checked = all.checked; } );
			refreshButtons();
		} );
		refreshButtons();
	}

	function selectedRows() {
		var out = [];
		resultsEl.querySelectorAll( 'tbody tr' ).forEach( function ( tr ) {
			var cb = tr.querySelector( '.spc-row-check' );
			if ( cb && cb.checked ) {
				out.push( {
					tr: tr,
					name: tr.querySelector( 'code' ).textContent,
					provider: tr.querySelector( '.spc-f-provider' ).value,
					duration: tr.querySelector( '.spc-f-duration' ).value,
					domain: tr.querySelector( '.spc-f-domain' ).value,
					category: tr.querySelector( '.spc-f-category' ).value,
					description: tr.querySelector( '.spc-f-description' ).value,
				} );
			}
		} );
		return out;
	}

	function refreshButtons() {
		var n = selectedRows().length;
		if ( aiBtn ) { aiBtn.disabled = ! n; }
		if ( addBtn ) { addBtn.disabled = ! n; }
	}

	if ( scanBtn ) {
		scanBtn.addEventListener( 'click', function () {
			scanBtn.disabled = true;
			setStatus( SPCAdmin.i18n.scanning );
			resultsEl.innerHTML = '';
			api( '/scan' ).then( function ( data ) {
				setStatus( '' );
				renderResults( data );
			} ).catch( function ( e ) {
				setStatus( SPCAdmin.i18n.error + ' ' + e.message, 'warn' );
			} ).finally( function () {
				scanBtn.disabled = false;
			} );
		} );
	}

	if ( aiBtn ) {
		aiBtn.addEventListener( 'click', function () {
			var rows = selectedRows();
			if ( ! rows.length ) { setStatus( SPCAdmin.i18n.pickRows, 'warn' ); return; }
			aiBtn.disabled = true;
			setStatus( SPCAdmin.i18n.aiWorking );
			api( '/ai-categorize', { cookies: rows.map( function ( r ) {
				return { name: r.name, domain: r.domain, duration: r.duration, provider: r.provider };
			} ) } ).then( function ( res ) {
				var byName = {};
				( res.cookies || [] ).forEach( function ( c ) { byName[ c.name ] = c; } );
				rows.forEach( function ( r ) {
					var c = byName[ r.name ];
					if ( ! c ) { return; }
					if ( c.category ) { r.tr.querySelector( '.spc-f-category' ).value = c.category; }
					if ( c.provider ) { r.tr.querySelector( '.spc-f-provider' ).value = c.provider; }
					if ( c.description ) { r.tr.querySelector( '.spc-f-description' ).value = c.description; }
				} );
				setStatus( 'AI categorisation applied — review and then add to the list.', 'ok' );
			} ).catch( function ( e ) {
				setStatus( SPCAdmin.i18n.error + ' ' + e.message, 'warn' );
			} ).finally( function () {
				refreshButtons();
			} );
		} );
	}

	if ( addBtn ) {
		addBtn.addEventListener( 'click', function () {
			var rows = selectedRows();
			if ( ! rows.length ) { setStatus( SPCAdmin.i18n.pickRows, 'warn' ); return; }
			addBtn.disabled = true;
			api( '/merge', { rows: rows.map( function ( r ) {
				return { name: r.name, category: r.category, domain: r.domain, duration: r.duration, description: r.description, provider: r.provider };
			} ) } ).then( function () {
				setStatus( SPCAdmin.i18n.added + ' ', 'ok' );
				rows.forEach( function ( r ) {
					r.tr.querySelector( '.spc-row-check' ).checked = false;
					var lastTd = r.tr.lastElementChild;
					if ( lastTd ) { lastTd.textContent = '✓'; }
				} );
			} ).catch( function ( e ) {
				setStatus( SPCAdmin.i18n.error + ' ' + e.message, 'warn' );
			} ).finally( function () {
				refreshButtons();
			} );
		} );
	}

	/* ---------------- Cookie list tab: add-row buttons ---------------- */

	var rowCounter = 100000;
	document.querySelectorAll( '.spc-add-row' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var cat = btn.dataset.cat;
			var tpl = document.getElementById( 'spc-row-tpl' );
			if ( ! tpl ) { return; }
			var html = tpl.innerHTML
				.replace( /__INDEX__/g, 'new_' + ( rowCounter++ ) )
				.replace( /__CAT__/g, cat );
			var tbody = document.querySelector( '.spc-rows[data-cat="' + cat + '"]' );
			if ( ! tbody ) { return; }
			var tmp = document.createElement( 'tbody' );
			tmp.innerHTML = html;
			var tr = tmp.firstElementChild;
			// Set the category select to this category.
			var sel = tr.querySelector( 'select' );
			if ( sel ) { sel.value = cat; }
			tbody.appendChild( tr );
		} );
	} );
} )();
