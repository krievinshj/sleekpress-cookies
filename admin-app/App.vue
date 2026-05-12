<script setup>
import { onMounted } from 'vue';
import { SpAppShell, SpSpinner, SpButton } from '@sleekpress/ui';
import { nav } from './router.js';
import { useStore } from './store.js';

const cfg = window.SPCAdmin || {};
const apiBase = cfg.restBase || '(not set)';
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

		<div v-if="store.state.loadFailed" class="spc-loadfail">
			<strong>Couldn't load the admin data.</strong>
			<code v-if="store.state.loadError">{{ store.state.loadError }}</code>
			<span class="sp-muted sp-small">Endpoint: <code>{{ apiBase }}/settings</code></span>
			<SpButton variant="default" size="sm" @click="store.load">Retry</SpButton>
		</div>
		<router-view v-else-if="store.state.loaded" />
		<div v-else class="spc-loading">
			<SpSpinner :size="22" /> <span class="sp-muted">Loading…</span>
		</div>
	</SpAppShell>
</template>

<style scoped>
.spc-loading { display: flex; align-items: center; gap: var(--sp-space-3); padding: var(--sp-space-7); justify-content: center; }
.spc-loadfail { display: flex; flex-direction: column; align-items: flex-start; gap: var(--sp-space-3); padding: var(--sp-space-6); }
.spc-loadfail code { word-break: break-all; }
</style>
