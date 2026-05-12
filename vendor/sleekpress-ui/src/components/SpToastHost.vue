<script setup>
import { useToasts } from '../composables/useToast.js';
const { items, dismiss } = useToasts();
</script>

<template>
	<Teleport to="body">
		<div class="sp-app sp-toasts" aria-live="polite">
			<TransitionGroup name="sp-toast">
				<div v-for="t in items" :key="t.id" class="sp-toast" :class="`sp-toast--${ t.variant }`">
					<span class="sp-toast__msg">{{ t.message }}</span>
					<button type="button" class="sp-toast__x" aria-label="Dismiss" @click="dismiss( t.id )">&times;</button>
				</div>
			</TransitionGroup>
		</div>
	</Teleport>
</template>

<style scoped>
.sp-toasts {
	position: fixed; right: var(--sp-space-5); bottom: var(--sp-space-5);
	z-index: var(--sp-z-toast);
	display: flex; flex-direction: column; gap: var(--sp-space-2);
	max-width: 24rem;
}
.sp-toast {
	display: flex; align-items: center; gap: var(--sp-space-3);
	padding: var(--sp-space-3) var(--sp-space-4);
	border-radius: var(--sp-radius);
	background: var(--sp-surface);
	border: 1px solid var(--sp-border);
	box-shadow: var(--sp-shadow);
	font-size: var(--sp-text-base);
	color: var(--sp-text);
}
.sp-toast__msg { flex: 1 1 auto; }
.sp-toast__x { background: none; border: 0; cursor: pointer; font-size: 1.1rem; line-height: 1; color: var(--sp-text-muted); }
.sp-toast--success { border-left: 3px solid var(--sp-success); }
.sp-toast--danger { border-left: 3px solid var(--sp-danger); }
.sp-toast--warning { border-left: 3px solid var(--sp-warning); }
.sp-toast--info { border-left: 3px solid var(--sp-info); }
.sp-toast-enter-from, .sp-toast-leave-to { opacity: 0; transform: translateY( 0.5rem ); }
.sp-toast-enter-active, .sp-toast-leave-active { transition: opacity 0.2s, transform 0.2s; }
</style>
