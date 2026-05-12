import { createApp } from 'vue';
import { createRouter, createWebHashHistory } from 'vue-router';
import SleekpressUI, { configureApi } from '@sleekpress/ui';
import App from './App.vue';
import { routes } from './router.js';

const cfg = window.SPCAdmin || {};

configureApi( { restBase: cfg.restBase, nonce: cfg.nonce } );

const router = createRouter( {
	history: createWebHashHistory(),
	routes,
} );

const mountId = cfg.mountId || 'spc-admin-app';
const el = document.getElementById( mountId );
if ( el ) {
	createApp( App ).use( router ).use( SleekpressUI ).mount( el );
}
