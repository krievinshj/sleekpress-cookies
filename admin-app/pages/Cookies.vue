<script setup>
import { ref, computed } from 'vue';
import {
	SpPageHeader, SpCard, SpButton, SpTextInput, SpTextarea, SpSelect, SpBadge,
} from '@sleekpress/ui';
import { useStore } from '../store.js';

const store = useStore();

// Local working copy: array of { _cat, name, provider, duration, domain, description }
const rows = ref( flatten() );

function flatten() {
	const out = [];
	const cookies = store.state.cookies || {};
	for ( const cat of store.state.categories ) {
		for ( const r of ( cookies[ cat.key ] || [] ) ) {
			out.push( { _cat: cat.key, name: r.name || '', provider: r.provider || '', duration: r.duration || '', domain: r.domain || '', description: r.description || '' } );
		}
	}
	return out;
}

function nest() {
	const table = {};
	for ( const cat of store.state.categories ) table[ cat.key ] = [];
	for ( const r of rows.value ) {
		if ( ! r.name.trim() ) continue;
		const cat = table[ r._cat ] ? r._cat : 'others';
		( table[ cat ] || ( table[ cat ] = [] ) ).push( { name: r.name, provider: r.provider, duration: r.duration, domain: r.domain, description: r.description } );
	}
	return table;
}

const catOptions = computed( () => store.state.categories.map( ( c ) => ( { value: c.key, label: c.label } ) ) );
const dirty = computed( () => JSON.stringify( nest() ) !== JSON.stringify( normalizedStored() ) );

function normalizedStored() {
	const cookies = store.state.cookies || {};
	const table = {};
	for ( const cat of store.state.categories ) {
		table[ cat.key ] = ( cookies[ cat.key ] || [] ).map( ( r ) => ( { name: r.name || '', provider: r.provider || '', duration: r.duration || '', domain: r.domain || '', description: r.description || '' } ) );
	}
	return table;
}

function addRow( catKey ) {
	rows.value.push( { _cat: catKey, name: '', provider: '', duration: '', domain: '', description: '' } );
}
function removeRow( i ) { rows.value.splice( i, 1 ); }

function rowsFor( catKey ) {
	// keep stable indices into rows.value
	return rows.value.map( ( r, i ) => ( { r, i } ) ).filter( ( x ) => x.r._cat === catKey );
}

async function save() {
	const ok = await store.saveCookies( nest() );
	if ( ok ) rows.value = flatten();
}
</script>

<template>
	<div class="sp-stack sp-stack--lg">
		<SpPageHeader title="Cookie list" subtitle="These cookies are shown to visitors in the preferences modal, grouped by category. Necessary cookies are always active.">
			<template #actions>
				<SpButton variant="primary" :loading="store.state.saving" :disabled="!dirty" @click="save">Save cookie list</SpButton>
			</template>
		</SpPageHeader>

		<SpCard
			v-for="cat in store.state.categories"
			:key="cat.key"
			:title="cat.label"
			:description="cat.description"
		>
			<template #actions>
				<SpBadge v-if="cat.locked" variant="success">Always active</SpBadge>
				<SpBadge v-else variant="neutral">{{ rowsFor( cat.key ).length }} cookie(s)</SpBadge>
			</template>

			<div v-if="rowsFor( cat.key ).length" class="spc-cookie-rows">
				<div class="spc-cookie-row spc-cookie-row--head">
					<span>Cookie</span><span>Provider</span><span>Duration</span><span>Domain</span><span>Description</span><span>Category</span><span></span>
				</div>
				<div v-for="{ r, i } in rowsFor( cat.key )" :key="i" class="spc-cookie-row">
					<SpTextInput v-model="r.name" />
					<SpTextInput v-model="r.provider" />
					<SpTextInput v-model="r.duration" />
					<SpTextInput v-model="r.domain" />
					<SpTextarea v-model="r.description" :rows="2" />
					<SpSelect v-model="r._cat" :options="catOptions" :inline="false" />
					<SpButton variant="ghost" size="sm" @click="removeRow( i )" title="Delete">✕</SpButton>
				</div>
			</div>
			<p v-else class="sp-muted sp-small">No cookies in this category yet.</p>

			<template #footer>
				<SpButton variant="default" size="sm" @click="addRow( cat.key )">+ Add cookie</SpButton>
			</template>
		</SpCard>
	</div>
</template>

<style scoped>
.spc-cookie-rows { display: flex; flex-direction: column; gap: var(--sp-space-2); }
.spc-cookie-row {
	display: grid;
	grid-template-columns: 1.3fr 1fr 1fr 1fr 2fr 1fr auto;
	gap: var(--sp-space-2);
	align-items: start;
}
.spc-cookie-row--head { font-size: var(--sp-text-xs); color: var(--sp-text-muted); font-weight: 600; padding-bottom: var(--sp-space-1); }
@media ( max-width: 60rem ) {
	.spc-cookie-row { grid-template-columns: 1fr 1fr; }
	.spc-cookie-row--head { display: none; }
}
</style>
