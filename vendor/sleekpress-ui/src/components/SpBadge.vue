<script setup>
const props = defineProps( {
	variant: { type: String, default: 'neutral' }, // neutral | primary | success | warning | danger
} );
</script>

<template>
	<span class="sp-badge" :class="`sp-badge--${ props.variant }`"><slot /></span>
</template>

<style scoped>
.sp-badge {
	display: inline-flex;
	align-items: center;
	font-size: var(--sp-text-xs);
	font-weight: 600;
	line-height: 1;
	padding: 0.25rem 0.5rem;
	border-radius: var(--sp-radius-pill);
	white-space: nowrap;
}
.sp-badge--neutral { background: var(--sp-surface-2); color: var(--sp-text-soft); border: 1px solid var(--sp-border); }
.sp-badge--primary { background: color-mix( in srgb, var(--sp-primary) 12%, transparent ); color: var(--sp-primary); }
.sp-badge--success { background: color-mix( in srgb, var(--sp-success) 14%, transparent ); color: var(--sp-success); }
.sp-badge--warning { background: color-mix( in srgb, var(--sp-warning) 16%, transparent ); color: var(--sp-warning); }
.sp-badge--danger { background: color-mix( in srgb, var(--sp-danger) 14%, transparent ); color: var(--sp-danger); }
</style>
