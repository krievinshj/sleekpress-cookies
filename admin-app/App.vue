<script setup>
import { onMounted } from 'vue';
import { SpAppShell, SpSpinner } from '@sleekpress/ui';
import { nav } from './router.js';
import { useStore } from './store.js';

const cfg = window.SPCAdmin || {};
const store = useStore();

onMounted( store.load );
</script>

<template>
	<SpAppShell
		:title="cfg.pluginName || 'SleekPress Cookies'"
		:version="store.state.version"
		:nav="nav"
	>
		<template #actions>
			<a v-if="cfg.docsUrl" :href="cfg.docsUrl" target="_blank" rel="noopener" class="sp-small sp-muted">Docs ↗</a>
		</template>

		<div v-if="!store.state.loaded && store.state.loading" class="spc-loading">
			<SpSpinner :size="22" /> <span class="sp-muted">Loading…</span>
		</div>
		<router-view v-else-if="store.state.loaded" />
		<div v-else class="spc-loading sp-muted">Couldn't load the admin data. Reload the page to try again.</div>
	</SpAppShell>
</template>

<style scoped>
.spc-loading { display: flex; align-items: center; gap: var(--sp-space-3); padding: var(--sp-space-7); justify-content: center; }
</style>
