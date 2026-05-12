<script setup>
import SpSpinner from './SpSpinner.vue';

const props = defineProps( {
	variant: { type: String, default: 'default' }, // primary | default | ghost | danger | link
	size: { type: String, default: 'md' }, // sm | md
	type: { type: String, default: 'button' },
	loading: { type: Boolean, default: false },
	disabled: { type: Boolean, default: false },
	block: { type: Boolean, default: false },
} );
</script>

<template>
	<button
		:type="type"
		class="sp-btn"
		:class="[ `sp-btn--${ props.variant }`, `sp-btn--${ props.size }`, { 'sp-btn--block': props.block, 'sp-btn--loading': props.loading } ]"
		:disabled="props.disabled || props.loading"
	>
		<SpSpinner v-if="props.loading" :size="props.size === 'sm' ? 12 : 14" class="sp-btn__spinner" />
		<span class="sp-btn__label"><slot /></span>
	</button>
</template>

<style scoped>
.sp-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: var(--sp-space-2);
	font-family: inherit;
	font-weight: 600;
	font-size: var(--sp-text-base);
	line-height: 1;
	height: var(--sp-control-h);
	padding: 0 var(--sp-space-4);
	border-radius: var(--sp-radius);
	border: 1px solid transparent;
	cursor: pointer;
	transition: background-color 0.15s, border-color 0.15s, color 0.15s, box-shadow 0.15s;
	white-space: nowrap;
}
.sp-btn:focus-visible { outline: 2px solid var(--sp-primary); outline-offset: 1px; }
.sp-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.sp-btn--sm { height: var(--sp-control-h-sm); font-size: var(--sp-text-sm); padding: 0 var(--sp-space-3); border-radius: var(--sp-radius-sm); }
.sp-btn--block { display: flex; width: 100%; }

.sp-btn--primary { background: var(--sp-primary); color: var(--sp-primary-contrast); border-color: var(--sp-primary); }
.sp-btn--primary:not(:disabled):hover { background: var(--sp-primary-hover); border-color: var(--sp-primary-hover); }

.sp-btn--default { background: var(--sp-surface); color: var(--sp-text); border-color: var(--sp-border-strong); box-shadow: var(--sp-shadow-sm); }
.sp-btn--default:not(:disabled):hover { background: var(--sp-surface-2); }

.sp-btn--ghost { background: transparent; color: var(--sp-text-soft); border-color: transparent; }
.sp-btn--ghost:not(:disabled):hover { background: var(--sp-surface-2); color: var(--sp-text); }

.sp-btn--danger { background: var(--sp-danger); color: #fff; border-color: var(--sp-danger); }
.sp-btn--danger:not(:disabled):hover { filter: brightness( 0.93 ); }

.sp-btn--link { background: transparent; border-color: transparent; color: var(--sp-primary); height: auto; padding: 0; font-weight: 600; }
.sp-btn--link:not(:disabled):hover { text-decoration: underline; }

.sp-btn--loading .sp-btn__label { opacity: 0.85; }
</style>
