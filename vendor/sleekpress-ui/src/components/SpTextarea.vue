<script setup>
const props = defineProps( {
	modelValue: { type: String, default: '' },
	rows: { type: [ Number, String ], default: 4 },
	placeholder: { type: String, default: '' },
	disabled: { type: Boolean, default: false },
	id: { type: String, default: '' },
} );
const emit = defineEmits( [ 'update:modelValue' ] );
</script>

<template>
	<textarea
		:id="props.id || undefined"
		class="sp-control"
		:rows="props.rows"
		:value="props.modelValue"
		:placeholder="props.placeholder"
		:disabled="props.disabled"
		@input="emit( 'update:modelValue', $event.target.value )"
	/>
</template>
