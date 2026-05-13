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
	'--p-radius': ( ( form.value.border_radius || 0 ) / 16 ) + 'rem',
	'--p-width': ( form.value.banner_width || 26.25 ) + 'rem',
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
.spc-banner-grid { display: grid; grid-template-columns: minmax( 0, 1fr ) 30rem; gap: var(--sp-space-5); align-items: start; }
@media ( max-width: 64rem ) { .spc-banner-grid { grid-template-columns: 1fr; } }
.spc-preview-col { position: sticky; top: var(--sp-space-5); }

/* The frame around the preview — a tiled "viewport" so the banner has some
 * surrounding visual context. */
.spc-preview {
	background:
		repeating-linear-gradient( 45deg, var(--sp-surface-2), var(--sp-surface-2) 0.625rem, var(--sp-surface) 0.625rem, var(--sp-surface) 1.25rem );
	padding: var(--sp-space-5);
	min-height: 18rem;
	display: flex;
	align-items: flex-end;
	border-radius: 0 0 var(--sp-radius-lg) var(--sp-radius-lg);
}

/* These rules deliberately mirror .spc-banner / .spc-btn / etc. in
 * assets/css/spc-banner.css 1:1 (same paddings, font sizes, radii) so the
 * preview is a faithful representation, not a scaled-down sketch. */
.spc-preview__banner {
	background: var(--p-bg);
	color: var(--p-text);
	border-radius: var(--p-radius);
	box-shadow: 0 0.625rem 2.5rem rgba( 0, 0, 0, 0.18 );
	padding: 1.25rem 1.375rem;
	width: var(--p-width);
	max-width: 100%;
	font-size: 0.875rem;
	line-height: 1.5;
	box-sizing: border-box;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.spc-preview__banner * { box-sizing: border-box; }
.spc-preview__banner.is-bottom-left { margin-right: auto; }
.spc-preview__banner.is-bottom-right { margin-left: auto; }
.spc-preview__banner.is-bottom-bar {
	width: 100%;
	max-width: 100%;
	border-radius: 0;
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 0.5rem 1.5rem;
	padding: 1rem 1.5rem;
}
.spc-preview__banner.is-bottom-bar .spc-preview__title { width: 100%; }
.spc-preview__banner.is-bottom-bar .spc-preview__text { flex: 1 1 20rem; margin: 0; }
.spc-preview__banner.is-bottom-bar .spc-preview__actions { margin-top: 0; }

.spc-preview__title {
	margin: 0 0 0.5rem;
	font-size: 1.25rem;
	font-weight: 700;
	line-height: 1.3;
	color: var(--p-text);
}
.spc-preview__text {
	margin: 0 0 1rem;
	color: var(--p-text);
	opacity: 0.92;
}
.spc-preview__link {
	color: var(--p-primary);
	text-decoration: underline;
	margin-left: 0.25rem;
}
.spc-preview__actions {
	display: flex;
	flex-wrap: wrap;
	gap: 0.5rem;
}
.spc-preview__btn {
	-webkit-appearance: none;
	appearance: none;
	border: 0;
	cursor: default;
	font-family: inherit;
	font-weight: 600;
	font-size: 0.875rem;
	line-height: 1.2;
	padding: 0.5625rem 1rem;
	border-radius: calc( var(--p-radius) - 0.25rem );
	flex: 1 1 auto;
	min-width: 5.25rem;
}
.spc-preview__btn.is-primary { background: var(--p-primary); color: var(--p-primary-text); }
.spc-preview__btn.is-secondary { background: var(--p-secondary); color: var(--p-secondary-text); }
.spc-preview__brand {
	margin-top: 0.75rem;
	font-size: 0.6875rem;
	opacity: 0.5;
}
</style>
