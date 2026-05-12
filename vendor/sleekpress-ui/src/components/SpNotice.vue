<script setup>
const props = defineProps( {
	variant: { type: String, default: 'info' }, // info | success | warning | danger
	title: { type: String, default: '' },
	dismissible: { type: Boolean, default: false },
} );
const emit = defineEmits( [ 'dismiss' ] );
</script>

<template>
	<div class="sp-notice" :class="`sp-notice--${ props.variant }`" role="status">
		<div class="sp-notice__body">
			<strong v-if="props.title" class="sp-notice__title">{{ props.title }}</strong>
			<div class="sp-notice__text"><slot /></div>
		</div>
		<button v-if="props.dismissible" type="button" class="sp-notice__x" aria-label="Dismiss" @click="emit( 'dismiss' )">&times;</button>
	</div>
</template>

<style scoped>
.sp-notice {
	display: flex;
	gap: var(--sp-space-3);
	padding: var(--sp-space-3) var(--sp-space-4);
	border-radius: var(--sp-radius);
	border: 1px solid var(--sp-border);
	background: var(--sp-surface);
	font-size: var(--sp-text-base);
}
.sp-notice__body { flex: 1 1 auto; min-width: 0; }
.sp-notice__title { display: block; margin-bottom: var(--sp-space-1); }
.sp-notice__x { background: none; border: 0; cursor: pointer; font-size: 1.1rem; line-height: 1; color: inherit; opacity: 0.6; }
.sp-notice__x:hover { opacity: 1; }
.sp-notice--info { border-color: color-mix( in srgb, var(--sp-info) 35%, var(--sp-border) ); background: color-mix( in srgb, var(--sp-info) 8%, var(--sp-surface) ); }
.sp-notice--success { border-color: color-mix( in srgb, var(--sp-success) 35%, var(--sp-border) ); background: color-mix( in srgb, var(--sp-success) 8%, var(--sp-surface) ); }
.sp-notice--warning { border-color: color-mix( in srgb, var(--sp-warning) 40%, var(--sp-border) ); background: color-mix( in srgb, var(--sp-warning) 10%, var(--sp-surface) ); }
.sp-notice--danger { border-color: color-mix( in srgb, var(--sp-danger) 40%, var(--sp-border) ); background: color-mix( in srgb, var(--sp-danger) 8%, var(--sp-surface) ); }
</style>
