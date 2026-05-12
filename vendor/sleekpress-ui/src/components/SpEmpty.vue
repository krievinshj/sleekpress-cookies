<script setup>
defineProps( {
	title: { type: String, default: '' },
	description: { type: String, default: '' },
} );
</script>

<template>
	<div class="sp-empty">
		<div v-if="$slots.icon" class="sp-empty__icon"><slot name="icon" /></div>
		<h3 v-if="title">{{ title }}</h3>
		<p v-if="description" class="sp-empty__desc">{{ description }}</p>
		<div v-if="$slots.default" class="sp-empty__actions"><slot /></div>
	</div>
</template>

<style scoped>
.sp-empty { text-align: center; padding: var(--sp-space-7) var(--sp-space-5); color: var(--sp-text-muted); }
.sp-empty__icon { font-size: 2rem; margin-bottom: var(--sp-space-3); opacity: 0.5; }
.sp-empty__desc { margin-top: var(--sp-space-2); max-width: 32rem; margin-inline: auto; }
.sp-empty__actions { margin-top: var(--sp-space-4); display: flex; justify-content: center; gap: var(--sp-space-2); }
</style>
