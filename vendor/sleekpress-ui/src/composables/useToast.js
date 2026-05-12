import { reactive } from 'vue';

let seq = 0;
const state = reactive( { items: [] } );

function push( message, variant = 'info', timeout = 4000 ) {
	const id = ++seq;
	state.items.push( { id, message, variant } );
	if ( timeout ) {
		setTimeout( () => dismiss( id ), timeout );
	}
	return id;
}

function dismiss( id ) {
	const i = state.items.findIndex( ( t ) => t.id === id );
	if ( i !== -1 ) state.items.splice( i, 1 );
}

export const toast = {
	info: ( m, t ) => push( m, 'info', t ),
	success: ( m, t ) => push( m, 'success', t ),
	warning: ( m, t ) => push( m, 'warning', t ),
	error: ( m, t ) => push( m, 'danger', t ?? 7000 ),
	dismiss,
};

export function useToasts() {
	return { items: state.items, dismiss };
}
