<script setup>
import { ref } from 'vue';
import SpToastHost from './SpToastHost.vue';

const props = defineProps( {
	title: { type: String, default: '' },
	subtitle: { type: String, default: '' },
	version: { type: String, default: '' },
	nav: { type: Array, default: () => [] }, // [{ to, label, icon? (html string) }]
	dark: { type: Boolean, default: false },
} );

const mobileOpen = ref( false );
</script>

<template>
	<div class="sp-app sp-shell" :class="{ 'sp-dark': props.dark }">
		<aside class="sp-shell__sidebar" :class="{ 'is-open': mobileOpen }">
			<div class="sp-shell__brand">
				<span class="sp-shell__brand-name">{{ props.title }}</span>
				<span v-if="props.version" class="sp-shell__brand-ver">v{{ props.version }}</span>
			</div>
			<nav class="sp-shell__nav">
				<router-link
					v-for="item in props.nav"
					:key="item.to"
					:to="item.to"
					class="sp-shell__navitem"
					active-class="is-active"
					@click="mobileOpen = false"
				>
					<span v-if="item.icon" class="sp-shell__navicon" v-html="item.icon" />
					<span>{{ item.label }}</span>
				</router-link>
			</nav>
			<div v-if="$slots.sidebarFooter" class="sp-shell__sidebar-foot"><slot name="sidebarFooter" /></div>
		</aside>

		<div class="sp-shell__main">
			<header class="sp-shell__topbar">
				<button type="button" class="sp-shell__burger" aria-label="Menu" @click="mobileOpen = !mobileOpen">≡</button>
				<div class="sp-shell__topbar-title">
					<slot name="topbar">
						<strong>{{ props.subtitle || props.title }}</strong>
					</slot>
				</div>
				<div class="sp-shell__topbar-actions"><slot name="actions" /></div>
			</header>
			<main class="sp-shell__content">
				<div class="sp-shell__content-inner">
					<slot />
				</div>
			</main>
		</div>

		<SpToastHost />
	</div>
</template>

<style scoped>
.sp-shell { display: flex; min-height: calc( 100vh - 32px ); margin-left: -20px; }
.sp-shell__sidebar {
	width: var(--sp-sidebar-w);
	flex: none;
	background: var(--sp-surface);
	border-right: 1px solid var(--sp-border);
	display: flex; flex-direction: column;
}
.sp-shell__brand {
	padding: var(--sp-space-5);
	display: flex; align-items: baseline; gap: var(--sp-space-2);
	border-bottom: 1px solid var(--sp-border);
}
.sp-shell__brand-name { font-weight: 700; color: var(--sp-heading); font-size: var(--sp-text-md); }
.sp-shell__brand-ver { font-size: var(--sp-text-xs); color: var(--sp-text-muted); }
.sp-shell__nav { padding: var(--sp-space-3); display: flex; flex-direction: column; gap: 0.125rem; }
.sp-shell__navitem {
	display: flex; align-items: center; gap: var(--sp-space-3);
	padding: var(--sp-space-2) var(--sp-space-3);
	border-radius: var(--sp-radius);
	color: var(--sp-text-soft);
	font-weight: 500;
	text-decoration: none;
}
.sp-shell__navitem:hover { background: var(--sp-surface-2); color: var(--sp-text); text-decoration: none; }
.sp-shell__navitem.is-active { background: color-mix( in srgb, var(--sp-primary) 12%, transparent ); color: var(--sp-primary); font-weight: 600; }
.sp-shell__navicon { display: inline-flex; width: 1.1rem; height: 1.1rem; }
.sp-shell__navicon :deep(svg) { width: 100%; height: 100%; fill: currentColor; }
.sp-shell__sidebar-foot { margin-top: auto; padding: var(--sp-space-4); border-top: 1px solid var(--sp-border); font-size: var(--sp-text-xs); color: var(--sp-text-muted); }

.sp-shell__main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
.sp-shell__topbar {
	display: flex; align-items: center; gap: var(--sp-space-3);
	padding: var(--sp-space-3) var(--sp-space-5);
	background: var(--sp-surface);
	border-bottom: 1px solid var(--sp-border);
	min-height: 3.25rem;
}
.sp-shell__burger { display: none; background: none; border: 0; font-size: 1.4rem; cursor: pointer; color: var(--sp-text); }
.sp-shell__topbar-title { flex: 1 1 auto; color: var(--sp-text-soft); }
.sp-shell__topbar-actions { display: flex; gap: var(--sp-space-2); }
.sp-shell__content { padding: var(--sp-space-6) var(--sp-space-5); }
.sp-shell__content-inner { max-width: var(--sp-content-max); margin: 0 auto; }

@media ( max-width: 48rem ) {
	.sp-shell { margin-left: 0; }
	.sp-shell__sidebar { position: fixed; inset: 0 auto 0 0; z-index: 40; transform: translateX( -100% ); transition: transform 0.2s; box-shadow: var(--sp-shadow-lg); }
	.sp-shell__sidebar.is-open { transform: translateX( 0 ); }
	.sp-shell__burger { display: inline-block; }
}
</style>
