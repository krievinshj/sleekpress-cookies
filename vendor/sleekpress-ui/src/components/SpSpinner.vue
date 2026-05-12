<script setup>
const props = defineProps( {
	size: { type: [ Number, String ], default: 18 },
} );
</script>

<template>
	<span class="sp-spinner" :style="{ width: props.size + 'px', height: props.size + 'px' }" aria-hidden="true" />
</template>

<style scoped>
.sp-spinner {
	display: inline-block;
	border: 2px solid currentColor;
	border-right-color: transparent;
	border-radius: 50%;
	opacity: 0.7;
	animation: sp-spin 0.6s linear infinite;
	flex: none;
}
</style>
