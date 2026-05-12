<script setup>
const props = defineProps( {
	modelValue: { type: [ String, Number ], default: '' },
	type: { type: String, default: 'text' },
	placeholder: { type: String, default: '' },
	disabled: { type: Boolean, default: false },
	size: { type: String, default: 'md' },
	inline: { type: Boolean, default: false },
	invalid: { type: Boolean, default: false },
	id: { type: String, default: '' },
	autocomplete: { type: String, default: 'off' },
} );
const emit = defineEmits( [ 'update:modelValue' ] );
</script>

<template>
	<input
		:id="props.id || undefined"
		:type="props.type"
		class="sp-control"
		:class="{ 'sp-control--sm': props.size === 'sm', 'sp-control--inline': props.inline, 'sp-control--invalid': props.invalid }"
		:value="props.modelValue"
		:placeholder="props.placeholder"
		:disabled="props.disabled"
		:autocomplete="props.autocomplete"
		@input="emit( 'update:modelValue', $event.target.value )"
	/>
</template>
