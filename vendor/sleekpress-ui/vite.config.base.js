import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';

/**
 * Base Vite config for a SleekPress plugin admin app.
 *
 * Usage in a plugin's vite.config.js:
 *
 *   import { defineConfig } from 'vite';
 *   import { sleekpressBase } from './vendor/sleekpress-ui/vite.config.base.js';
 *   export default defineConfig( sleekpressBase( {
 *     root: __dirname,
 *     entry: 'admin-app/main.js',
 *     outDir: 'assets/admin/dist',
 *     name: 'admin',
 *     globalName: 'SpcAdminApp',
 *   } ) );
 *
 * Produces a single self-executing `<name>.js` + `<name>.css` (no hashing, no
 * manifest, no module/chunk splitting; all deps bundled) so PHP can enqueue
 * them by a stable path with a plain <script> tag.
 */
export function sleekpressBase( opts = {} ) {
	const {
		root,
		entry = 'admin-app/main.js',
		outDir = 'assets/admin/dist',
		name = 'admin',
		globalName = 'SleekpressApp',
		alias = {},
		base = './',
	} = opts;

	return {
		root,
		base,
		plugins: [ vue() ],
		resolve: {
			alias: {
				'@sleekpress/ui': fileURLToPath( new URL( './src/index.js', import.meta.url ) ),
				...alias,
			},
		},
		build: {
			outDir,
			emptyOutDir: true,
			cssCodeSplit: false,
			rollupOptions: {
				input: entry,
				output: {
					format: 'iife',
					name: globalName,
					inlineDynamicImports: true,
					entryFileNames: `${ name }.js`,
					assetFileNames: ( assetInfo ) => {
						if ( assetInfo.name && assetInfo.name.endsWith( '.css' ) ) {
							return `${ name }.css`;
						}
						return `${ name }-[name][extname]`;
					},
				},
			},
		},
	};
}
