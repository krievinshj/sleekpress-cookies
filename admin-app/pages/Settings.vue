<script setup>
import { ref, computed } from 'vue';
import {
	SpPageHeader, SpCard, SpFormRow, SpToggle, SpTextInput, SpNumberInput, SpButton, SpNotice,
} from '@sleekpress/ui';
import { useStore } from '../store.js';

const store = useStore();
const form = ref( clone() );

function clone() {
	return JSON.parse( JSON.stringify( store.state.settings ) );
}

const dirty = computed( () => JSON.stringify( form.value ) !== JSON.stringify( store.state.settings ) );

async function save() {
	const ok = await store.saveSettings( form.value );
	if ( ok ) form.value = clone();
}
</script>

<template>
	<div class="sp-stack sp-stack--lg">
		<SpPageHeader title="Settings" subtitle="General behaviour, your tag IDs, and the optional AI categorisation key.">
			<template #actions>
				<SpButton variant="primary" :loading="store.state.saving" :disabled="!dirty" @click="save">Save changes</SpButton>
			</template>
		</SpPageHeader>

		<SpCard title="General">
			<SpFormRow label="Consent banner" description="Show the cookie banner on the front end.">
				<SpToggle v-model="form.enabled" label="Enabled" />
			</SpFormRow>
			<SpFormRow label="Hide banner for administrators" description="Handy while testing tags.">
				<SpToggle v-model="form.hide_for_admins" label="Don't show the banner to logged-in admins" />
			</SpFormRow>
			<SpFormRow label="Re-ask for consent after" hint="Days before a returning visitor sees the banner again.">
				<SpNumberInput v-model="form.consent_expiry_days" :min="1" :max="3650" suffix="days" />
			</SpFormRow>
		</SpCard>

		<SpCard title="Tags" description="Optional — leave empty if you already manage your tags (e.g. in GTM you installed yourself). Consent Mode signals still work either way.">
			<SpFormRow label="Google Tag Manager ID">
				<SpTextInput v-model="form.gtm_id" placeholder="GTM-XXXXXXX" />
				<template #hint>If set, the plugin prints the GTM container snippet for you, after the Consent Mode defaults.</template>
			</SpFormRow>
			<SpFormRow label="GA4 Measurement ID">
				<SpTextInput v-model="form.ga4_id" placeholder="G-XXXXXXXXXX" />
				<template #hint>Used only if no GTM ID is set above; the plugin will load <code>gtag.js</code> with this ID.</template>
			</SpFormRow>
		</SpCard>

		<SpCard title="AI categorisation (OpenAI)" description="Used on the Cookie scanner to auto-categorise and describe discovered cookies.">
			<SpNotice v-if="!store.state.aiReady" variant="info">No API key set yet — AI categorisation on the scanner is disabled until you add one below.</SpNotice>
			<SpNotice v-else variant="success">API key configured — AI categorisation is available on the Cookie scanner.</SpNotice>
			<SpFormRow label="OpenAI API key" hint="Stored in your WordPress database. Used only for the scanner's categorise action.">
				<SpTextInput v-model="form.openai_api_key" type="password" autocomplete="new-password" placeholder="sk-…" />
			</SpFormRow>
			<SpFormRow label="Model">
				<SpTextInput v-model="form.openai_model" placeholder="gpt-4o-mini" />
				<template #hint>e.g. <code>gpt-4o-mini</code> (cheap, good enough) or <code>gpt-4o</code>.</template>
			</SpFormRow>
		</SpCard>
	</div>
</template>
