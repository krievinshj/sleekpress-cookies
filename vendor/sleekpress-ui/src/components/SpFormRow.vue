<script setup>
const props = defineProps( {
	label: { type: String, default: '' },
	description: { type: String, default: '' },
	hint: { type: String, default: '' }, // shown under the control
	stacked: { type: Boolean, default: false }, // label above control instead of beside
	for: { type: String, default: '' },
} );
</script>

<template>
	<div class="sp-formrow" :class="{ 'sp-formrow--stacked': props.stacked }">
		<div class="sp-formrow__label">
			<label v-if="props.label" :for="props.for">{{ props.label }}</label>
			<p v-if="props.description" class="sp-formrow__desc">{{ props.description }}</p>
		</div>
		<div class="sp-formrow__control">
			<slot />
			<p v-if="props.hint" class="sp-formrow__hint"><slot name="hint">{{ props.hint }}</slot></p>
			<p v-else-if="$slots.hint" class="sp-formrow__hint"><slot name="hint" /></p>
		</div>
	</div>
</template>

<style scoped>
.sp-formrow {
	display: grid;
	grid-template-columns: 16rem 1fr;
	gap: var(--sp-space-4);
	padding: var(--sp-space-4) 0;
	border-bottom: 1px solid var(--sp-border);
}
.sp-formrow:last-child { border-bottom: 0; }
.sp-formrow--stacked { grid-template-columns: 1fr; gap: var(--sp-space-2); }
.sp-formrow__label label { font-weight: 600; color: var(--sp-heading); display: block; }
.sp-formrow__desc { margin-top: var(--sp-space-1); color: var(--sp-text-muted); font-size: var(--sp-text-sm); }
.sp-formrow__control { min-width: 0; }
.sp-formrow__hint { margin-top: var(--sp-space-2); color: var(--sp-text-muted); font-size: var(--sp-text-sm); }
.sp-formrow__hint :deep(code) { font-size: 0.85em; }
@media ( max-width: 48rem ) {
	.sp-formrow { grid-template-columns: 1fr; gap: var(--sp-space-2); }
}
</style>
