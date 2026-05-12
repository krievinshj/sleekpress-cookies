# @sleekpress/ui

Shared Vue 3 admin UI kit for SleekPress plugins — design tokens, components, an
app shell, and a tiny PHP loader. The goal is a consistent, modern, performant
admin across every SleekPress plugin.

Stack: **Vue 3** (`<script setup>` SFCs) · **vue-router** (hash mode) · **Pinia**
optional · **Vite** for the build · plain **scoped CSS + design tokens** (no CSS
framework). Everything is namespaced under a `.sp-app` root so it neither leaks
into wp-admin nor inherits from it.

## How it's distributed

This package lives in its own repo. Each plugin **vendors a copy** of it under
`vendor/sleekpress-ui/` and aliases `@sleekpress/ui` to `vendor/sleekpress-ui/src`
in its Vite config. There's no npm/Composer registry — pull updates with
`bin/sync.sh` (or `git subtree`).

```
vendor/sleekpress-ui/
  src/
    index.js              # exports + installSleekpressUI(app)
    styles/tokens.css      # ← the brand file: edit colours/spacing/radius here
    styles/base.css        # resets + a few layout utilities (.sp-stack, .sp-row…)
    components/             # SpAppShell SpButton SpCard SpFormRow SpTextInput
                            #   SpTextarea SpNumberInput SpSelect SpToggle
                            #   SpColorInput SpNotice SpBadge SpModal SpTable
                            #   SpEmpty SpSpinner SpToastHost
    composables/            # useApi (REST client), useToast
  vite.config.base.js       # importable base Vite config (IIFE bundle, fixed names)
  php/class-sleekpress-ui.php  # SleekPress_UI::enqueue_app() + ::render_root()
  bin/sync.sh               # copy this package into another plugin's vendor/
```

## Adding it to a new plugin

1. **Vendor the package**

   ```bash
   # from the sleekpress-ui repo:
   ./bin/sync.sh /path/to/your-plugin
   # → copies into /path/to/your-plugin/vendor/sleekpress-ui
   ```

2. **Create the admin app** in the plugin, e.g. `admin-app/`:

   ```
   admin-app/
     main.js        # createApp, router, pinia, installSleekpressUI, mount('#your-root')
     App.vue        # <SpAppShell :title :version :nav> <router-view/> </SpAppShell>
     router.js
     store.js       # Pinia store calling useApi()
     pages/*.vue
   ```

   `main.js` skeleton:

   ```js
   import { createApp } from 'vue';
   import { createRouter, createWebHashHistory } from 'vue-router';
   import { createPinia } from 'pinia';
   import SleekpressUI, { configureApi } from '@sleekpress/ui';
   import App from './App.vue';
   import { routes } from './router.js';

   const cfg = window.YourPluginAdmin || {};
   configureApi( { restBase: cfg.restBase, nonce: cfg.nonce } );

   createApp( App )
     .use( createPinia() )
     .use( createRouter( { history: createWebHashHistory(), routes } ) )
     .use( SleekpressUI )
     .mount( '#your-root' );
   ```

3. **Add the build** — `package.json` in the plugin root:

   ```json
   {
     "private": true,
     "scripts": { "dev": "vite", "build": "vite build" },
     "devDependencies": {
       "vite": "^5", "@vitejs/plugin-vue": "^5",
       "vue": "^3.4", "vue-router": "^4.3", "pinia": "^2.1"
     }
   }
   ```

   `vite.config.js`:

   ```js
   import { defineConfig } from 'vite';
   import { sleekpressBase } from './vendor/sleekpress-ui/vite.config.base.js';
   export default defineConfig( sleekpressBase( {
     root: import.meta.dirname,
     entry: 'admin-app/main.js',
     outDir: 'assets/admin/dist',
     name: 'admin',          // → assets/admin/dist/admin.js + admin.css
     globalName: 'YourPluginAdminApp',
   } ) );
   ```

   Then `npm install && npm run build`. Commit the generated `assets/admin/dist/`.

4. **Wire PHP** — require the loader and, on your admin page hook:

   ```php
   require_once PLUGIN_DIR . 'vendor/sleekpress-ui/php/class-sleekpress-ui.php';

   $built = SleekPress_UI::enqueue_app( array(
     'handle'         => 'yourplugin-admin',
     'dist_dir'       => PLUGIN_DIR . 'assets/admin/dist',
     'dist_url'       => PLUGIN_URL . 'assets/admin/dist',
     'version'        => YOURPLUGIN_VERSION,
     'config_var'     => 'YourPluginAdmin',
     'rest_namespace' => 'yourplugin/v1',
     // Optional but recommended: route the app's data calls through
     // admin-ajax.php instead of /wp-json/. admin-ajax is under /wp-admin/,
     // so the auth cookie is always sent — REST cookie auth can be flaky on
     // some hosts. Register a matching wp_ajax_{action} handler that proxies
     // to your REST routes (see SPC_Ajax in sleekpress-cookies for a copy-paste
     // example using rest_do_request()).
     'ajax_action'    => 'yourplugin_api',
     'config'         => array( /* anything your app needs at boot */ ),
   ) );
   // in the page render callback:
   SleekPress_UI::render_root( 'your-root', $built );
   ```

   `enqueue_app()` injects `window.YourPluginAdmin = { restBase, nonce, adminUrl, pluginUrl, ... }` — plus `ajaxUrl`, `ajaxAction`, `ajaxNonce` when `ajax_action` is set. `createApi()` automatically uses the admin-ajax transport if those are present, else falls back to a plain REST fetch.

## Re-skinning

Override the tokens in `src/styles/tokens.css` (or, per-plugin, ship a small CSS
file after the bundle that overrides `--sp-*` on `.sp-app`). The most useful:
`--sp-primary`, `--sp-radius`, the `--sp-space-*` scale, `--sp-font`.

## Updating the package across plugins

Edit here, bump the version in `package.json`, tag the repo, then run
`./bin/sync.sh <plugin-path>` for each plugin and rebuild it. Treat the vendored
copy as read-only inside plugins.
