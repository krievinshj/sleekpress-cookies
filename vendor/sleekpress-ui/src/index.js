/**
 * @sleekpress/ui — shared Vue 3 admin UI kit.
 *
 * Import individual components, or call installSleekpressUI(app) to register
 * them all globally (Sp* prefix).
 */
import './styles/index.css';

import SpAppShell from './components/SpAppShell.vue';
import SpButton from './components/SpButton.vue';
import SpSpinner from './components/SpSpinner.vue';
import SpCard from './components/SpCard.vue';
import SpPageHeader from './components/SpPageHeader.vue';
import SpFormRow from './components/SpFormRow.vue';
import SpTextInput from './components/SpTextInput.vue';
import SpTextarea from './components/SpTextarea.vue';
import SpNumberInput from './components/SpNumberInput.vue';
import SpSelect from './components/SpSelect.vue';
import SpToggle from './components/SpToggle.vue';
import SpColorInput from './components/SpColorInput.vue';
import SpNotice from './components/SpNotice.vue';
import SpBadge from './components/SpBadge.vue';
import SpModal from './components/SpModal.vue';
import SpTable from './components/SpTable.vue';
import SpEmpty from './components/SpEmpty.vue';
import SpToastHost from './components/SpToastHost.vue';

export {
	SpAppShell, SpButton, SpSpinner, SpCard, SpPageHeader, SpFormRow,
	SpTextInput, SpTextarea, SpNumberInput, SpSelect, SpToggle, SpColorInput,
	SpNotice, SpBadge, SpModal, SpTable, SpEmpty, SpToastHost,
};

export { toast, useToasts } from './composables/useToast.js';
export { createApi, useApi, configureApi } from './composables/useApi.js';

const components = {
	SpAppShell, SpButton, SpSpinner, SpCard, SpPageHeader, SpFormRow,
	SpTextInput, SpTextarea, SpNumberInput, SpSelect, SpToggle, SpColorInput,
	SpNotice, SpBadge, SpModal, SpTable, SpEmpty, SpToastHost,
};

export function installSleekpressUI( app ) {
	for ( const [ name, comp ] of Object.entries( components ) ) {
		app.component( name, comp );
	}
}

export default { install: installSleekpressUI };
