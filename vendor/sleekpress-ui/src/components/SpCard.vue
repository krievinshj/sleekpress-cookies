<script setup>
const props = defineProps( {
	title: { type: String, default: '' },
	description: { type: String, default: '' },
	padded: { type: Boolean, default: true },
} );
</script>

<template>
	<section class="sp-card">
		<header v-if="props.title || $slots.header || $slots.actions" class="sp-card__head">
			<div class="sp-card__titles">
				<slot name="header">
					<h3 v-if="props.title">{{ props.title }}</h3>
					<p v-if="props.description" class="sp-card__desc">{{ props.description }}</p>
				</slot>
			</div>
			<div v-if="$slots.actions" class="sp-card__actions"><slot name="actions" /></div>
		</header>
		<div class="sp-card__body" :class="{ 'sp-card__body--flush': !props.padded }">
			<slot />
		</div>
		<footer v-if="$slots.footer" class="sp-card__foot"><slot name="footer" /></footer>
	</section>
</template>

<style scoped>
.sp-card {
	background: var(--sp-surface);
	border: 1px solid var(--sp-border);
	border-radius: var(--sp-radius-lg);
	box-shadow: var(--sp-shadow-sm);
	overflow: hidden;
}
.sp-card__head {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: var(--sp-space-3);
	padding: var(--sp-space-4) var(--sp-space-5);
	border-bottom: 1px solid var(--sp-border);
}
.sp-card__desc { margin-top: var(--sp-space-1); color: var(--sp-text-muted); font-size: var(--sp-text-sm); }
.sp-card__actions { flex: none; }
.sp-card__body { padding: var(--sp-space-5); }
.sp-card__body--flush { padding: 0; }
.sp-card__foot {
	padding: var(--sp-space-4) var(--sp-space-5);
	border-top: 1px solid var(--sp-border);
	background: var(--sp-surface-2);
}
</style>
