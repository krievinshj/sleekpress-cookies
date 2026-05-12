<script setup>
import { ref, computed } from 'vue';
import {
	SpPageHeader, SpCard, SpFormRow, SpToggle, SpNumberInput, SpSelect, SpButton, SpTable, SpBadge,
} from '@sleekpress/ui';
import { useStore } from '../store.js';

const store = useStore();
const form = ref( clone() );

function clone() { return JSON.parse( JSON.stringify( store.state.settings ) ); }
const dirty = computed( () => JSON.stringify( form.value ) !== JSON.stringify( store.state.settings ) );

const stateOptions = [ { value: 'denied', label: 'Denied' }, { value: 'granted', label: 'Granted' } ];

async function save() {
	const ok = await store.saveSettings( form.value );
	if ( ok ) form.value = clone();
}
</script>

<template>
	<div class="sp-stack sp-stack--lg">
		<SpPageHeader title="Google Consent Mode v2" subtitle="Tags receive a “denied” signal before consent (cookieless modelling pings), and an “update” the moment the visitor chooses. Works whether your tags live in GTM or are hard-coded.">
			<template #actions>
				<SpButton variant="primary" :loading="store.state.saving" :disabled="!dirty" @click="save">Save changes</SpButton>
			</template>
		</SpPageHeader>

		<SpCard title="Consent Mode">
			<SpFormRow label="Enable Google Consent Mode v2" description="Push consent default/update signals to the dataLayer before any tag loads.">
				<SpToggle v-model="form.gcm_enabled" label="Enabled" />
			</SpFormRow>
			<SpFormRow label="Wait for update" hint="How long Google tags wait for a consent update before firing. 500 ms is typical.">
				<SpNumberInput v-model="form.gcm_wait_for_update" :min="0" :max="10000" suffix="ms" />
			</SpFormRow>
			<SpFormRow label="URL passthrough">
				<SpToggle v-model="form.gcm_url_passthrough" label="Pass ad-click & session info through URLs while consent is denied" />
			</SpFormRow>
			<SpFormRow label="Ads data redaction">
				<SpToggle v-model="form.gcm_ads_redaction" label="Redact ad identifiers when ad_storage is denied" />
			</SpFormRow>
		</SpCard>

		<SpCard title="Default consent state" description="GDPR-style setups should keep everything Denied. Setting a category to Granted disables consent gating for it.">
			<SpTable :columns="[ { key: 'cat', label: 'Category' }, { key: 'sig', label: 'Consent Mode signals' }, { key: 'def', label: 'Default before consent', width: '12rem' } ]">
				<tr v-for="c in store.state.categories" :key="c.key">
					<td><strong>{{ c.label }}</strong></td>
					<td>
						<code v-if="c.gcm && c.gcm.length">{{ c.gcm.join(', ') }}</code>
						<span v-else class="sp-muted">—</span>
					</td>
					<td>
						<SpBadge v-if="c.locked" variant="success">Granted (always)</SpBadge>
						<span v-else-if="!c.gcm || !c.gcm.length" class="sp-muted sp-small">Not signalled to Google</span>
						<SpSelect v-else v-model="form.gcm_default[c.key]" :options="stateOptions" />
					</td>
				</tr>
			</SpTable>
		</SpCard>
	</div>
</template>
