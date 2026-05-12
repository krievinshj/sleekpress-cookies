<script setup>
import { ref, computed } from 'vue';
import {
	SpPageHeader, SpCard, SpButton, SpNotice, SpEmpty, SpTextInput, SpTextarea, SpSelect, SpBadge, SpSpinner,
} from '@sleekpress/ui';
import { useStore } from '../store.js';

const store = useStore();

const scanning = ref( false );
const aiBusy = ref( false );
const adding = ref( false );
const results = ref( null ); // { cookies:[], scanned_urls:[], errors:[] }
const note = ref( '' );
const noteKind = ref( 'info' );

const catOptions = computed( () => store.state.categories.map( ( c ) => ( { value: c.key, label: c.label } ) ) );
const lastScan = computed( () => store.state.lastScan );

function setNote( msg, kind = 'info' ) { note.value = msg; noteKind.value = kind; }

async function scan() {
	scanning.value = true;
	setNote( '' );
	results.value = null;
	try {
		const data = await store.runScan();
		// each cookie row gets a `_sel` flag for selection
		data.cookies = ( data.cookies || [] ).map( ( c ) => ( { ...c, _sel: false } ) );
		results.value = data;
		store.state.lastScan = { time: Math.floor( Date.now() / 1000 ), count: data.cookies.length, scanned_urls: data.scanned_urls };
	} catch ( e ) {
		setNote( 'Scan failed: ' + e.message, 'danger' );
	} finally {
		scanning.value = false;
	}
}

const selected = computed( () => ( results.value?.cookies || [] ).filter( ( c ) => c._sel ) );
const allSelected = computed( {
	get: () => results.value && results.value.cookies.length > 0 && results.value.cookies.every( ( c ) => c._sel ),
	set: ( v ) => { ( results.value?.cookies || [] ).forEach( ( c ) => { c._sel = v; } ); },
} );

async function aiCategorize() {
	if ( ! selected.value.length ) { setNote( 'Select at least one cookie first.', 'warning' ); return; }
	aiBusy.value = true;
	setNote( 'Asking the AI…' );
	try {
		const out = await store.aiCategorize( selected.value.map( ( c ) => ( { name: c.name, domain: c.domain, duration: c.duration, provider: c.provider } ) ) );
		const byName = Object.fromEntries( out.map( ( c ) => [ c.name, c ] ) );
		for ( const c of selected.value ) {
			const r = byName[ c.name ];
			if ( ! r ) continue;
			if ( r.category ) c.category = r.category;
			if ( r.provider ) c.provider = r.provider;
			if ( r.description ) c.description = r.description;
		}
		setNote( 'AI categorisation applied — review, then add to the list.', 'success' );
	} catch ( e ) {
		setNote( 'AI request failed: ' + e.message, 'danger' );
	} finally {
		aiBusy.value = false;
	}
}

async function addSelected() {
	if ( ! selected.value.length ) { setNote( 'Select at least one cookie first.', 'warning' ); return; }
	adding.value = true;
	try {
		await store.mergeCookies( selected.value.map( ( c ) => ( { name: c.name, category: c.category, domain: c.domain, duration: c.duration, description: c.description, provider: c.provider } ) ) );
		for ( const c of selected.value ) { c.known = true; c._sel = false; }
		setNote( 'Added to the cookie list.', 'success' );
		store.toast.success( 'Cookies added to the list.' );
	} catch ( e ) {
		setNote( 'Could not add: ' + e.message, 'danger' );
	} finally {
		adding.value = false;
	}
}
</script>

<template>
	<div class="sp-stack sp-stack--lg">
		<SpPageHeader title="Cookie scanner" subtitle="Fetches your home page and recent pages, matches known third-party scripts against a built-in database, and lists cookies seen in real visitors' browsers. Review, optionally let the AI categorise, then add to your cookie list.">
			<template #actions>
				<SpButton variant="primary" :loading="scanning" @click="scan">Scan now</SpButton>
			</template>
		</SpPageHeader>

		<SpNotice v-if="note" :variant="noteKind">{{ note }}</SpNotice>
		<SpNotice v-if="lastScan && !results" variant="info">
			Last scan {{ new Date( lastScan.time * 1000 ).toLocaleString() }} — {{ lastScan.count }} cookies. Run a new scan to see them.
		</SpNotice>

		<SpCard :padded="false">
			<template #header><h3>Discovered cookies</h3></template>
			<template #actions>
				<SpButton v-if="store.state.aiReady" variant="default" size="sm" :loading="aiBusy" :disabled="!selected.length" @click="aiCategorize">Categorise selected with AI</SpButton>
				<span v-else class="sp-small sp-muted">Add an OpenAI key in Settings for AI categorisation.</span>
				<SpButton variant="default" size="sm" :loading="adding" :disabled="!selected.length" @click="addSelected">Add selected to cookie list</SpButton>
			</template>

			<div v-if="scanning" class="spc-scan-loading"><SpSpinner :size="20" /> <span class="sp-muted">Scanning…</span></div>

			<template v-else-if="results">
				<div v-if="results.errors && results.errors.length" class="spc-scan-warn">⚠ {{ results.errors.join( ' · ' ) }}</div>
				<div v-if="results.scanned_urls && results.scanned_urls.length" class="spc-scan-urls sp-small sp-muted">Scanned: {{ results.scanned_urls.join( ', ' ) }}</div>

				<SpEmpty v-if="!results.cookies.length" title="No cookies discovered" description="No known third-party scripts were detected and no visitor cookies have been reported yet." />

				<div v-else class="spc-scan-rows">
					<div class="spc-scan-row spc-scan-row--head">
						<input type="checkbox" v-model="allSelected" />
						<span>Cookie</span><span>Provider</span><span>Duration</span><span>Domain</span><span>Category</span><span>Description</span><span>Source</span><span>In list</span>
					</div>
					<div v-for="( c, i ) in results.cookies" :key="i" class="spc-scan-row" :class="{ 'is-known': c.known }">
						<input type="checkbox" v-model="c._sel" />
						<code class="spc-scan-name">{{ c.name }}</code>
						<SpTextInput v-model="c.provider" size="sm" />
						<SpTextInput v-model="c.duration" size="sm" />
						<SpTextInput v-model="c.domain" size="sm" />
						<SpSelect v-model="c.category" :options="catOptions" :inline="false" size="sm" />
						<SpTextarea v-model="c.description" :rows="2" />
						<span class="sp-small sp-muted">{{ c.source }}</span>
						<SpBadge v-if="c.known" variant="success">✓</SpBadge>
						<span v-else class="sp-muted">—</span>
					</div>
				</div>
			</template>

			<SpEmpty v-else title="Nothing scanned yet" description="Click “Scan now” to look for third-party scripts and cookies on your site.">
				<SpButton variant="primary" :loading="scanning" @click="scan">Scan now</SpButton>
			</SpEmpty>
		</SpCard>
	</div>
</template>

<style scoped>
.spc-scan-loading { display: flex; align-items: center; gap: var(--sp-space-3); padding: var(--sp-space-6); justify-content: center; }
.spc-scan-warn { padding: var(--sp-space-3) var(--sp-space-5); color: var(--sp-warning); font-size: var(--sp-text-sm); }
.spc-scan-urls { padding: 0 var(--sp-space-5) var(--sp-space-3); }
.spc-scan-rows { display: flex; flex-direction: column; }
.spc-scan-row {
	display: grid;
	grid-template-columns: 2rem 1.4fr 1fr 1fr 1fr 1fr 2.2fr 0.8fr 0.6fr;
	gap: var(--sp-space-2);
	align-items: start;
	padding: var(--sp-space-3) var(--sp-space-5);
	border-top: 1px solid var(--sp-border);
}
.spc-scan-row--head { font-size: var(--sp-text-xs); color: var(--sp-text-muted); font-weight: 600; border-top: 0; }
.spc-scan-row.is-known { background: color-mix( in srgb, var(--sp-success) 5%, transparent ); }
.spc-scan-name { align-self: center; word-break: break-all; }
@media ( max-width: 64rem ) {
	.spc-scan-row { grid-template-columns: 2rem 1fr 1fr; }
	.spc-scan-row--head { display: none; }
}
</style>
