<script setup>
import { computed } from 'vue';

const props = defineProps( {
	modelValue: { type: String, default: '#000000' },
	disabled: { type: Boolean, default: false },
} );
const emit = defineEmits( [ 'update:modelValue' ] );

const hex = computed( () => /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( props.modelValue || '' ) ? props.modelValue : '#000000' );

function setVal( v ) {
	emit( 'update:modelValue', v );
}
</script>

<template>
	<span class="sp-color" :class="{ 'is-disabled': props.disabled }">
		<input type="color" class="sp-color__swatch" :value="hex" :disabled="props.disabled" @input="setVal( $event.target.value )" />
		<input type="text" class="sp-control sp-control--sm sp-color__hex" :value="props.modelValue" :disabled="props.disabled" spellcheck="false" @input="setVal( $event.target.value )" />
	</span>
</template>

<style scoped>
.sp-color { display: inline-flex; align-items: center; gap: var(--sp-space-2); }
.sp-color__swatch {
	width: 2.25rem; height: 2.25rem;
	padding: 0;
	border: 1px solid var(--sp-border-strong);
	border-radius: var(--sp-radius);
	background: none;
	cursor: pointer;
}
.sp-color__swatch::-webkit-color-swatch-wrapper { padding: 0.15rem; }
.sp-color__swatch::-webkit-color-swatch { border: 0; border-radius: var(--sp-radius-sm); }
.sp-color__hex { width: 6rem; font-family: var(--sp-font-mono); }
.sp-color.is-disabled { opacity: 0.6; }
</style>
