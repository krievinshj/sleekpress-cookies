<script setup>
const props = defineProps( {
	title: { type: String, default: '' },
	subtitle: { type: String, default: '' },
} );
</script>

<template>
	<div class="sp-pagehead">
		<div class="sp-pagehead__titles">
			<h1>{{ props.title }}</h1>
			<p v-if="props.subtitle" class="sp-pagehead__sub">{{ props.subtitle }}</p>
			<slot name="meta" />
		</div>
		<div v-if="$slots.actions" class="sp-pagehead__actions"><slot name="actions" /></div>
	</div>
</template>

<style scoped>
.sp-pagehead {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: var(--sp-space-4);
	flex-wrap: wrap;
	margin-bottom: var(--sp-space-5);
}
.sp-pagehead__sub { margin-top: var(--sp-space-2); color: var(--sp-text-muted); max-width: 46rem; }
.sp-pagehead__actions { display: flex; gap: var(--sp-space-2); flex: none; }
</style>
