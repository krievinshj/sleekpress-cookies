<script setup>
import { ref, computed } from 'vue';
import {
	SpPageHeader, SpCard, SpFormRow, SpTextInput, SpTextarea, SpNumberInput, SpSelect, SpToggle, SpColorInput, SpButton, SpNotice,
} from '@sleekpress/ui';
import { useStore } from '../store.js';

const store = useStore();
const form = ref( clone() );

function clone() { return JSON.parse( JSON.stringify( store.state.settings ) ); }
const dirty = computed( () => JSON.stringify( form.value ) !== JSON.stringify( store.state.settings ) );

const positions = [
	{ value: 'bottom-left', label: 'Box — bottom left' },
	{ value: 'bottom-right', label: 'Box — bottom right' },
	{ value: 'bottom-bar', label: 'Full-width bar — bottom' },
];
const themes = [ { value: 'light', label: 'Light' }, { value: 'dark', label: 'Dark' } ];

const previewVars = computed( () => ( {
	'--p-bg': form.value.color_bg,
	'--p-text': form.value.color_text,
	'--p-primary': form.value.color_primary,
	'--p-primary-text': form.value.color_primary_text,
	'--p-secondary': form.value.color_secondary,
	'--p-secondary-text': form.value.color_secondary_text,
	'--p-radius': form.value.border_radius + 'px',
} ) );

const privacyResolved = computed( () => form.value.privacy_url || store.state.wpPrivacyUrl || '' );

async function save() {
	const ok = await store.saveSettings( form.value );
	if ( ok ) form.value = clone();
}
</script>

<template>
	<div class="sp-stack sp-stack--lg">
		<SpPageHeader title="Banner &amp; design" subtitle="Wording, colours and layout of the consent banner and preferences modal.">
			<template #actions>
				<SpButton variant="primary" :loading="store.state.saving" :disabled="!dirty" @click="save">Save changes</SpButton>
			</template>
		</SpPageHeader>

		<div class="spc-banner-grid">
			<div class="sp-stack sp-stack--lg">
				<SpCard title="Content">
					<SpFormRow label="Banner title"><SpTextInput v-model="form.title" /></SpFormRow>
					<SpFormRow label="Intro message" stacked hint="Basic HTML allowed. A link to your privacy policy is appended automatically.">
						<SpTextarea v-model="form.message" :rows="4" />
					</SpFormRow>
					<SpFormRow label="Privacy policy URL">
						<SpTextInput v-model="form.privacy_url" placeholder="https://…" />
						<template #hint>
							<template v-if="form.privacy_url">Using this URL.</template>
							<template v-else-if="store.state.wpPrivacyUrl">Leave empty to use the WordPress privacy page: <code>{{ store.state.wpPrivacyUrl }}</code></template>
							<template v-else>No WordPress privacy policy page is set (Settings → Privacy). Enter a URL here, or the link is hidden.</template>
						</template>
					</SpFormRow>
					<SpFormRow label="Privacy link text"><SpTextInput v-model="form.privacy_link_text" /></SpFormRow>
					<SpFormRow label="“Accept” button"><SpTextInput v-model="form.btn_accept_text" /></SpFormRow>
					<SpFormRow label="“Decline” button"><SpTextInput v-model="form.btn_decline_text" /></SpFormRow>
					<SpFormRow label="“Adjust” button"><SpTextInput v-model="form.btn_adjust_text" /></SpFormRow>
					<SpFormRow label="“Save preferences” button"><SpTextInput v-model="form.btn_save_text" /></SpFormRow>
				</SpCard>

				<SpCard title="Layout">
					<SpFormRow label="Position"><SpSelect v-model="form.position" :options="positions" /></SpFormRow>
					<SpFormRow label="Theme"><SpSelect v-model="form.theme" :options="themes" /></SpFormRow>
					<SpFormRow label="Corner radius"><SpNumberInput v-model="form.border_radius" :min="0" :max="40" suffix="px" /></SpFormRow>
					<SpFormRow label="Banner width" hint="Box layouts only — the full-width bar ignores this.">
						<SpNumberInput v-model="form.banner_width" :min="16" :max="60" :step="0.25" suffix="rem" />
					</SpFormRow>
					<SpFormRow label="Floating “cookie settings” badge">
						<SpToggle v-model="form.show_revisit_badge" label="Show a small button so visitors can reopen their preferences" />
					</SpFormRow>
					<SpFormRow label="Branding">
						<SpToggle v-model="form.show_branding" label="Show “Powered by SleekPress Cookies” in the banner" />
					</SpFormRow>
				</SpCard>

				<SpCard title="Colours">
					<SpFormRow label="Background"><SpColorInput v-model="form.color_bg" /></SpFormRow>
					<SpFormRow label="Text"><SpColorInput v-model="form.color_text" /></SpFormRow>
					<SpFormRow label="Accept button — background"><SpColorInput v-model="form.color_primary" /></SpFormRow>
					<SpFormRow label="Accept button — text"><SpColorInput v-model="form.color_primary_text" /></SpFormRow>
					<SpFormRow label="Decline / Adjust — background"><SpColorInput v-model="form.color_secondary" /></SpFormRow>
					<SpFormRow label="Decline / Adjust — text"><SpColorInput v-model="form.color_secondary_text" /></SpFormRow>
				</SpCard>
			</div>

			<div class="spc-preview-col">
				<SpCard title="Preview" :padded="false">
					<div class="spc-preview" :style="previewVars">
						<div class="spc-preview__banner" :class="'is-' + form.position">
							<div class="spc-preview__title">{{ form.title }}</div>
							<div class="spc-preview__text">
								<span v-html="form.message"></span>
								<a v-if="privacyResolved" class="spc-preview__link" href="#" @click.prevent>{{ form.privacy_link_text }}</a>
							</div>
							<div class="spc-preview__actions">
								<button class="spc-preview__btn is-secondary">{{ form.btn_adjust_text }}</button>
								<button class="spc-preview__btn is-secondary">{{ form.btn_decline_text }}</button>
								<button class="spc-preview__btn is-primary">{{ form.btn_accept_text }}</button>
							</div>
							<div v-if="form.show_branding" class="spc-preview__brand">Powered by SleekPress Cookies</div>
						</div>
					</div>
				</SpCard>
				<SpNotice variant="info" class="sp-mt-4">
					Reopen the preferences modal anywhere with the <code>[sleekpress_cookie_settings]</code> shortcode, a link with <code>class="spc-open-prefs"</code>, or a link to <code>#cookies-settings</code>.
				</SpNotice>
			</div>
		</div>
	</div>
</template>

<style scoped>
.spc-banner-grid { display: grid; grid-template-columns: 1fr 22rem; gap: var(--sp-space-5); align-items: start; }
@media ( max-width: 64rem ) { .spc-banner-grid { grid-template-columns: 1fr; } }
.spc-preview-col { position: sticky; top: var(--sp-space-5); }

.spc-preview {
	background:
		repeating-linear-gradient( 45deg, var(--sp-surface-2), var(--sp-surface-2) 10px, var(--sp-surface) 10px, var(--sp-surface) 20px );
	padding: var(--sp-space-5);
	min-height: 16rem;
	display: flex;
	border-radius: 0 0 var(--sp-radius-lg) var(--sp-radius-lg);
}
.spc-preview__banner {
	background: var(--p-bg);
	color: var(--p-text);
	border-radius: var(--p-radius);
	box-shadow: 0 10px 30px rgba( 0,0,0,0.18 );
	padding: var(--sp-space-4);
	width: 100%;
	align-self: flex-end;
	font-size: 0.78rem;
}
.spc-preview__banner.is-bottom-left { align-self: flex-end; margin-right: auto; max-width: 17rem; }
.spc-preview__banner.is-bottom-right { align-self: flex-end; margin-left: auto; max-width: 17rem; }
.spc-preview__banner.is-bottom-bar { border-radius: 0; max-width: 100%; }
.spc-preview__title { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.35rem; }
.spc-preview__text { opacity: 0.92; margin-bottom: 0.6rem; }
.spc-preview__link { color: var(--p-primary); text-decoration: underline; margin-left: 0.25rem; }
.spc-preview__actions { display: flex; gap: 0.35rem; flex-wrap: wrap; }
.spc-preview__btn { border: 0; cursor: default; font-weight: 600; font-size: 0.72rem; padding: 0.4rem 0.6rem; border-radius: calc( var(--p-radius) - 4px ); flex: 1 1 auto; }
.spc-preview__btn.is-primary { background: var(--p-primary); color: var(--p-primary-text); }
.spc-preview__btn.is-secondary { background: var(--p-secondary); color: var(--p-secondary-text); }
.spc-preview__brand { margin-top: 0.5rem; font-size: 0.6rem; opacity: 0.5; }
</style>
