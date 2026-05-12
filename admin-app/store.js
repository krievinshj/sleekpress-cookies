import { reactive, computed } from 'vue';
import { createApi, toast } from '@sleekpress/ui';

const api = createApi();

const state = reactive( {
	loaded: false,
	loading: false,
	loadFailed: false,
	loadError: '',
	saving: false,
	settings: null,
	cookies: null, // { category: [rows] }
	categories: [], // [{ key, label, description, locked, gcm }]
	privacyAuto: '',
	wpPrivacyUrl: '',
	aiReady: false,
	version: '',
	lastScan: null,
} );

function applySettingsPayload( p ) {
	state.settings = p.settings;
	state.categories = p.categories || [];
	state.privacyAuto = p.privacyAuto || '';
	state.wpPrivacyUrl = p.wpPrivacyUrl || '';
	state.aiReady = !! p.aiReady;
	state.version = p.version || '';
	state.lastScan = p.lastScan || null;
}

async function load() {
	if ( state.loading ) return;
	state.loading = true;
	state.loadFailed = false;
	state.loadError = '';
	try {
		const [ s, c ] = await Promise.all( [ api.get( '/settings' ), api.get( '/cookies' ) ] );
		applySettingsPayload( s );
		state.cookies = c.cookies;
		if ( ! state.categories.length ) state.categories = c.categories || [];
		state.loaded = true;
	} catch ( e ) {
		state.loadFailed = true;
		state.loadError = ( e.status ? '[HTTP ' + e.status + '] ' : '' ) + ( e.code ? e.code + ' — ' : '' ) + ( e.message || 'unknown error' );
		toast.error( 'Failed to load: ' + e.message );
	} finally {
		state.loading = false;
	}
}

async function saveSettings( patch ) {
	state.saving = true;
	try {
		const merged = { ...state.settings, ...patch };
		const res = await api.post( '/settings', { settings: merged } );
		applySettingsPayload( res );
		toast.success( 'Settings saved.' );
		return true;
	} catch ( e ) {
		toast.error( 'Could not save: ' + e.message );
		return false;
	} finally {
		state.saving = false;
	}
}

async function saveCookies( table ) {
	state.saving = true;
	try {
		const res = await api.post( '/cookies', { cookies: table } );
		state.cookies = res.cookies;
		toast.success( 'Cookie list saved.' );
		return true;
	} catch ( e ) {
		toast.error( 'Could not save: ' + e.message );
		return false;
	} finally {
		state.saving = false;
	}
}

async function runScan() {
	return api.post( '/scan' );
}

async function aiCategorize( cookies ) {
	const res = await api.post( '/ai-categorize', { cookies } );
	return res.cookies || [];
}

async function mergeCookies( rows ) {
	const res = await api.post( '/merge', { rows } );
	if ( res.cookies ) state.cookies = res.cookies;
	// refresh derived bits
	const c = await api.get( '/cookies' );
	state.cookies = c.cookies;
	return res;
}

export function useStore() {
	return {
		state,
		categoryMap: computed( () => Object.fromEntries( state.categories.map( ( c ) => [ c.key, c ] ) ) ),
		load,
		saveSettings,
		saveCookies,
		runScan,
		aiCategorize,
		mergeCookies,
		api,
		toast,
	};
}
