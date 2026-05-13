<script setup>
import { computed, ref } from 'vue';

/**
 * Colour input that accepts both literal hex (e.g. "#2563eb") and CSS variable
 * references (e.g. "var(--theme-palette-color-1)") so plugins can opt into the
 * active theme's design tokens. The swatch is a styled element whose
 * `background` is the raw value — when that value is a `var(...)` defined in
 * the page's CSS cascade, the browser resolves it and the swatch shows the
 * real theme colour. Clicking the swatch opens a hidden native colour picker;
 * picking from it writes a hex (and replaces a previous `var(...)` value —
 * which is what the user expects).
 */

const props = defineProps( {
	modelValue: { type: String, default: '' },
	disabled: { type: Boolean, default: false },
} );
const emit = defineEmits( [ 'update:modelValue' ] );

const pickerRef = ref( null );
const hex = computed( () => {
	const v = props.modelValue || '';
	return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( v ) ? v : '#000000';
} );

function setVal( v ) { emit( 'update:modelValue', v ); }
function openPicker() {
	if ( props.disabled ) { return; }
	const el = pickerRef.value;
	if ( el && typeof el.click === 'function' ) {
		el.click();
	}
}
function onKey( e ) {
	if ( e.key === 'Enter' || e.key === ' ' ) {
		e.preventDefault();
		openPicker();
	}
}
</script>

<template>
	<span class="sp-color" :class="{ 'is-disabled': props.disabled }">
		<span
			class="sp-color__swatch"
			:style="{ background: props.modelValue || '' }"
			role="button"
			:tabindex="props.disabled ? -1 : 0"
			:aria-label="( 'Pick colour. Current value: ' + ( props.modelValue || 'unset' ) )"
			@click="openPicker"
			@keydown="onKey"
		/>
		<input
			ref="pickerRef"
			type="color"
			class="sp-color__picker"
			:value="hex"
			:disabled="props.disabled"
			tabindex="-1"
			aria-hidden="true"
			@input="setVal( $event.target.value )"
		/>
		<input
			type="text"
			class="sp-control sp-control--sm sp-color__hex"
			:value="props.modelValue"
			:disabled="props.disabled"
			spellcheck="false"
			placeholder="#… or var(--…)"
			@input="setVal( $event.target.value )"
		/>
	</span>
</template>

<style scoped>
.sp-color { display: inline-flex; align-items: center; gap: var(--sp-space-2); position: relative; }
.sp-color.is-disabled { opacity: 0.6; }

/* Swatch: a checkerboard "no colour" pattern is the fallback when the value
 * is unset or unresolvable (e.g. a var() not defined in the cascade). Any
 * valid value supplied as an inline `background:` overrides this completely. */
.sp-color__swatch {
	display: inline-block;
	width: 2.25rem; height: 2.25rem;
	border: 1px solid var(--sp-border-strong);
	border-radius: var(--sp-control-radius);
	cursor: pointer;
	background:
		linear-gradient( 45deg, rgba( 0, 0, 0, 0.08 ) 25%, transparent 25%, transparent 75%, rgba( 0, 0, 0, 0.08 ) 75% ) 0 0 / 0.625rem 0.625rem,
		linear-gradient( 45deg, rgba( 0, 0, 0, 0.08 ) 25%, transparent 25%, transparent 75%, rgba( 0, 0, 0, 0.08 ) 75% ) 0.3125rem 0.3125rem / 0.625rem 0.625rem,
		#ffffff;
}
.sp-color.is-disabled .sp-color__swatch { cursor: not-allowed; }
.sp-color__swatch:focus-visible { outline: 2px solid var(--sp-primary); outline-offset: 2px; }

/* Native picker stays in the DOM (so .click() works) but is visually hidden. */
.sp-color__picker {
	position: absolute;
	width: 0; height: 0;
	opacity: 0;
	pointer-events: none;
	border: 0;
}

.sp-color__hex {
	width: 13rem;
	font-family: var(--sp-font-mono);
}
</style>
