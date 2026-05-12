<script setup>
import { watch, onBeforeUnmount } from 'vue';

const props = defineProps( {
	modelValue: { type: Boolean, default: false },
	title: { type: String, default: '' },
	size: { type: String, default: 'md' }, // sm | md | lg
	closeOnBackdrop: { type: Boolean, default: true },
} );
const emit = defineEmits( [ 'update:modelValue', 'close' ] );

function close() {
	emit( 'update:modelValue', false );
	emit( 'close' );
}
function onKey( e ) {
	if ( e.key === 'Escape' && props.modelValue ) close();
}
watch( () => props.modelValue, ( open ) => {
	if ( open ) document.addEventListener( 'keydown', onKey );
	else document.removeEventListener( 'keydown', onKey );
} );
onBeforeUnmount( () => document.removeEventListener( 'keydown', onKey ) );
</script>

<template>
	<Teleport to="body">
		<div v-if="props.modelValue" class="sp-app sp-modal-root">
			<div class="sp-modal__backdrop" @click="props.closeOnBackdrop && close()" />
			<div class="sp-modal" :class="`sp-modal--${ props.size }`" role="dialog" aria-modal="true">
				<header class="sp-modal__head">
					<h3>{{ props.title }}</h3>
					<button type="button" class="sp-modal__x" aria-label="Close" @click="close">&times;</button>
				</header>
				<div class="sp-modal__body"><slot /></div>
				<footer v-if="$slots.footer" class="sp-modal__foot"><slot name="footer" :close="close" /></footer>
			</div>
		</div>
	</Teleport>
</template>

<style scoped>
.sp-modal-root { position: fixed; inset: 0; z-index: var(--sp-z-modal); display: flex; align-items: center; justify-content: center; padding: var(--sp-space-5); }
.sp-modal__backdrop { position: absolute; inset: 0; background: rgba( 17, 24, 39, 0.45 ); }
.sp-modal {
	position: relative;
	background: var(--sp-surface);
	border-radius: var(--sp-radius-lg);
	box-shadow: var(--sp-shadow-lg);
	width: 32rem; max-width: 100%;
	max-height: 90vh;
	display: flex; flex-direction: column;
	overflow: hidden;
}
.sp-modal--sm { width: 24rem; }
.sp-modal--lg { width: 48rem; }
.sp-modal__head { display: flex; align-items: center; justify-content: space-between; padding: var(--sp-space-4) var(--sp-space-5); border-bottom: 1px solid var(--sp-border); }
.sp-modal__x { background: none; border: 0; cursor: pointer; font-size: 1.4rem; line-height: 1; color: var(--sp-text-muted); }
.sp-modal__x:hover { color: var(--sp-text); }
.sp-modal__body { padding: var(--sp-space-5); overflow-y: auto; }
.sp-modal__foot { padding: var(--sp-space-4) var(--sp-space-5); border-top: 1px solid var(--sp-border); background: var(--sp-surface-2); display: flex; justify-content: flex-end; gap: var(--sp-space-2); }
</style>
