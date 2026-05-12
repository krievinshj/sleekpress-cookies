<script setup>
/**
 * options: array of { value, label } or array of strings.
 */
const props = defineProps( {
	modelValue: { type: [ String, Number, Boolean ], default: '' },
	options: { type: Array, default: () => [] },
	disabled: { type: Boolean, default: false },
	inline: { type: Boolean, default: true },
	size: { type: String, default: 'md' },
	id: { type: String, default: '' },
} );
const emit = defineEmits( [ 'update:modelValue' ] );

function normalize( o ) {
	if ( o && typeof o === 'object' ) return { value: o.value, label: o.label ?? String( o.value ) };
	return { value: o, label: String( o ) };
}
</script>

<template>
	<select
		:id="props.id || undefined"
		class="sp-control"
		:class="{ 'sp-control--inline': props.inline, 'sp-control--sm': props.size === 'sm' }"
		:value="props.modelValue"
		:disabled="props.disabled"
		@change="emit( 'update:modelValue', $event.target.value )"
	>
		<option v-for="o in props.options.map( normalize )" :key="String( o.value )" :value="o.value">{{ o.label }}</option>
	</select>
</template>
