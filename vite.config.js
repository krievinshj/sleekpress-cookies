import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import { sleekpressBase } from './vendor/sleekpress-ui/vite.config.base.js';

export default defineConfig(
	sleekpressBase( {
		root: fileURLToPath( new URL( '.', import.meta.url ) ),
		entry: 'admin-app/main.js',
		outDir: 'assets/admin/dist',
		name: 'admin',
		globalName: 'SpcAdminApp',
		alias: {
			'@': fileURLToPath( new URL( './admin-app', import.meta.url ) ),
		},
	} )
);
