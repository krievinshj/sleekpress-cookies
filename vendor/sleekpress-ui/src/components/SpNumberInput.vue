<script setup>
const props = defineProps( {
	modelValue: { type: [ Number, String ], default: 0 },
	min: { type: [ Number, String ], default: null },
	max: { type: [ Number, String ], default: null },
	step: { type: [ Number, String ], default: 1 },
	disabled: { type: Boolean, default: false },
	suffix: { type: String, default: '' },
	id: { type: String, default: '' },
} );
const emit = defineEmits( [ 'update:modelValue' ] );

function onInput( e ) {
	const v = e.target.value;
	emit( 'update:modelValue', v === '' ? '' : Number( v ) );
}
</script>

<template>
	<span class="sp-num">
		<input
			:id="props.id || undefined"
			type="number"
			class="sp-control sp-control--inline sp-num__input"
			:value="props.modelValue"
			:min="props.min ?? undefined"
			:max="props.max ?? undefined"
			:step="props.step"
			:disabled="props.disabled"
			@input="onInput"
		/>
		<span v-if="props.suffix" class="sp-num__suffix">{{ props.suffix }}</span>
	</span>
</template>

<style scoped>
.sp-num { display: inline-flex; align-items: center; gap: var(--sp-space-2); }
.sp-num__input { width: 7rem; }
.sp-num__suffix { color: var(--sp-text-muted); font-size: var(--sp-text-sm); }
</style>
