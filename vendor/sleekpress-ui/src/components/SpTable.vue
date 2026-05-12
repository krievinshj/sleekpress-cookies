<script setup>
/**
 * Thin styled table. Pass `columns` as [{ key, label, width, align }] and use
 * the default slot for <tr> rows (with #row scoped to each item if `rows` given),
 * or just use the default slot freely.
 */
const props = defineProps( {
	columns: { type: Array, default: () => [] },
	rows: { type: Array, default: null },
	dense: { type: Boolean, default: false },
} );
</script>

<template>
	<div class="sp-table-wrap">
		<table class="sp-table" :class="{ 'sp-table--dense': props.dense }">
			<thead v-if="props.columns.length">
				<tr>
					<th v-for="c in props.columns" :key="c.key" :style="{ width: c.width, textAlign: c.align }">{{ c.label }}</th>
				</tr>
			</thead>
			<tbody>
				<template v-if="props.rows">
					<slot v-for="( item, i ) in props.rows" :key="i" name="row" :item="item" :index="i" />
				</template>
				<slot v-else />
			</tbody>
		</table>
	</div>
</template>

<style scoped>
.sp-table-wrap { overflow-x: auto; border: 1px solid var(--sp-border); border-radius: var(--sp-radius-lg); }
.sp-table { width: 100%; border-collapse: collapse; font-size: var(--sp-text-base); background: var(--sp-surface); }
.sp-table :deep(th), .sp-table :deep(td) { padding: var(--sp-space-3) var(--sp-space-4); text-align: left; vertical-align: top; }
.sp-table--dense :deep(th), .sp-table--dense :deep(td) { padding: var(--sp-space-2) var(--sp-space-3); }
.sp-table :deep(thead th) {
	background: var(--sp-surface-2);
	color: var(--sp-text-soft);
	font-weight: 600;
	font-size: var(--sp-text-sm);
	border-bottom: 1px solid var(--sp-border);
	position: sticky; top: 0;
}
.sp-table :deep(tbody tr) { border-top: 1px solid var(--sp-border); }
.sp-table :deep(tbody tr:first-child) { border-top: 0; }
.sp-table :deep(tbody tr:hover) { background: var(--sp-surface-2); }
</style>
