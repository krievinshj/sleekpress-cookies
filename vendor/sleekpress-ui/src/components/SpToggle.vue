<script setup>
const props = defineProps( {
	modelValue: { type: [ Boolean, Number ], default: false },
	disabled: { type: Boolean, default: false },
	label: { type: String, default: '' },
} );
const emit = defineEmits( [ 'update:modelValue' ] );

function toggle() {
	if ( props.disabled ) return;
	emit( 'update:modelValue', ! props.modelValue );
}
</script>

<template>
	<label class="sp-toggle" :class="{ 'is-on': !!props.modelValue, 'is-disabled': props.disabled }">
		<button type="button" class="sp-toggle__track" role="switch" :aria-checked="!!props.modelValue" :disabled="props.disabled" @click="toggle">
			<span class="sp-toggle__thumb" />
		</button>
		<span v-if="props.label || $slots.default" class="sp-toggle__label" @click="toggle"><slot>{{ props.label }}</slot></span>
	</label>
</template>

<style scoped>
.sp-toggle { display: inline-flex; align-items: center; gap: var(--sp-space-3); }
.sp-toggle__track {
	flex: none;
	width: 2.5rem; height: 1.4rem;
	border-radius: var(--sp-radius-pill);
	border: 0;
	padding: 0;
	background: var(--sp-border-strong);
	cursor: pointer;
	position: relative;
	transition: background-color 0.18s;
}
.sp-toggle.is-on .sp-toggle__track { background: var(--sp-primary); }
.sp-toggle.is-disabled .sp-toggle__track { cursor: not-allowed; opacity: 0.6; }
.sp-toggle__thumb {
	position: absolute; top: 0.15rem; left: 0.15rem;
	width: 1.1rem; height: 1.1rem;
	background: #fff;
	border-radius: 50%;
	box-shadow: var(--sp-shadow-sm);
	transition: transform 0.18s;
}
.sp-toggle.is-on .sp-toggle__thumb { transform: translateX( 1.1rem ); }
.sp-toggle__label { font-size: var(--sp-text-base); color: var(--sp-text); cursor: pointer; }
.sp-toggle.is-disabled .sp-toggle__label { cursor: not-allowed; }
.sp-toggle__track:focus-visible { outline: 2px solid var(--sp-primary); outline-offset: 2px; }
</style>
