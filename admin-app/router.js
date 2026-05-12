import Scanner from './pages/Scanner.vue';
import Cookies from './pages/Cookies.vue';
import Banner from './pages/Banner.vue';
import Consent from './pages/Consent.vue';
import Settings from './pages/Settings.vue';

export const routes = [
	{ path: '/', redirect: '/scanner' },
	{ path: '/scanner', name: 'scanner', component: Scanner, meta: { label: 'Cookie scanner' } },
	{ path: '/cookies', name: 'cookies', component: Cookies, meta: { label: 'Cookie list' } },
	{ path: '/banner', name: 'banner', component: Banner, meta: { label: 'Banner & design' } },
	{ path: '/consent', name: 'consent', component: Consent, meta: { label: 'Consent Mode' } },
	{ path: '/settings', name: 'settings', component: Settings, meta: { label: 'Settings' } },
	{ path: '/:pathMatch(.*)*', redirect: '/scanner' },
];

export const nav = [
	{ to: '/scanner', label: 'Cookie scanner', icon: '<svg viewBox="0 0 20 20"><path d="M8 2a6 6 0 1 0 3.7 10.7l4.3 4.3 1.4-1.4-4.3-4.3A6 6 0 0 0 8 2zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8z"/></svg>' },
	{ to: '/cookies', label: 'Cookie list', icon: '<svg viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 5h14v2H3V9zm0 5h14v2H3v-2z"/></svg>' },
	{ to: '/banner', label: 'Banner & design', icon: '<svg viewBox="0 0 20 20"><path d="M3 3h14a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H7l-4 4V4a1 1 0 0 1 1-1zm2 3v2h10V6H5zm0 4v2h7v-2H5z"/></svg>' },
	{ to: '/consent', label: 'Consent Mode', icon: '<svg viewBox="0 0 20 20"><path d="M10 1l7 3v5c0 4.6-3 8.5-7 10C6 17.5 3 13.6 3 9V4l7-3zm-1 11.4 5-5L13.6 6 9 10.6 6.4 8 5 9.4l4 4z"/></svg>' },
	{ to: '/settings', label: 'Settings', icon: '<svg viewBox="0 0 20 20"><path d="M11.5 1l.6 2.4 2-.7 1.7 1.7-1.1 1.8 2.4.6v2.4l-2.4.6 1.1 1.8-1.7 1.7-2-.7-.6 2.4H8.5l-.6-2.4-2 .7-1.7-1.7 1.1-1.8L2.9 11V8.6l2.4-.6L4.2 6.2 5.9 4.5l2 .7L8.5 1h3zM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>' },
];
