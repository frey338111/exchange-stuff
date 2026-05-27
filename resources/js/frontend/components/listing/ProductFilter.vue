<template>
    <aside class="space-y-6">
        <nav v-if="showChildCategoryFilter" class="rounded-md border border-gray-200 bg-white p-4 shadow-sm" aria-label="Child category filters">
            <h2 class="text-sm font-semibold text-gray-950">Category</h2>
            <div class="mt-3 space-y-1">
                <a :href="filterUrl({ categoryId: '', page: 1 })" :class="filterLinkClass(! selectedCategoryId)" @click.prevent="emitFilter({ categoryId: '', page: 1 })">
                    <span>All</span>
                    <span>{{ totalCount }}</span>
                </a>
                <a
                    v-for="childCategory in childCategoryFilters"
                    :key="childCategory.id"
                    :href="filterUrl({ categoryId: childCategory.id, page: 1 })"
                    :class="filterLinkClass(String(selectedCategoryId) === String(childCategory.id))"
                    @click.prevent="emitFilter({ categoryId: childCategory.id, page: 1 })"
                >
                    <span>{{ childCategory.title }}</span>
                    <span>{{ childCategory.count }}</span>
                </a>
            </div>
        </nav>

        <nav class="rounded-md border border-gray-200 bg-white p-4 shadow-sm" aria-label="Condition filters">
            <h2 class="text-sm font-semibold text-gray-950">Condition</h2>
            <div class="mt-3 space-y-1">
                <a :href="filterUrl({ conditionId: '', page: 1 })" :class="filterLinkClass(! selectedConditionId)" @click.prevent="emitFilter({ conditionId: '', page: 1 })">
                    <span>All</span>
                    <span>{{ categoryFilteredCount }}</span>
                </a>
                <a
                    v-for="condition in conditionFilters"
                    :key="condition.id"
                    :href="filterUrl({ conditionId: condition.id, page: 1 })"
                    :class="filterLinkClass(String(selectedConditionId) === String(condition.id))"
                    @click.prevent="emitFilter({ conditionId: condition.id, page: 1 })"
                >
                    <span>{{ condition.title }}</span>
                    <span>{{ condition.count }}</span>
                </a>
            </div>
        </nav>

        <nav class="rounded-md border border-gray-200 bg-white p-4 shadow-sm" aria-label="Date added filters">
            <h2 class="text-sm font-semibold text-gray-950">Added in</h2>
            <div class="mt-3 space-y-1">
                <a :href="filterUrl({ dateAdded: '', page: 1 })" :class="filterLinkClass(! selectedDateAdded)" @click.prevent="emitFilter({ dateAdded: '', page: 1 })">
                    <span>All</span>
                    <span>{{ conditionFilteredCount }}</span>
                </a>
                <a
                    v-for="dateFilter in dateFilters"
                    :key="dateFilter.id"
                    :href="filterUrl({ dateAdded: dateFilter.id, page: 1 })"
                    :class="filterLinkClass(selectedDateAdded === dateFilter.id)"
                    @click.prevent="emitFilter({ dateAdded: dateFilter.id, page: 1 })"
                >
                    <span>{{ dateFilter.title }}</span>
                    <span>{{ dateFilter.count }}</span>
                </a>
            </div>
        </nav>
    </aside>
</template>

<script setup>
defineProps({
    totalCount: {
        type: Number,
        required: true,
    },
    showChildCategoryFilter: {
        type: Boolean,
        required: true,
    },
    selectedCategoryId: {
        type: [String, Number],
        default: '',
    },
    selectedConditionId: {
        type: [String, Number],
        default: '',
    },
    selectedDateAdded: {
        type: String,
        default: '',
    },
    childCategoryFilters: {
        type: Array,
        required: true,
    },
    categoryFilteredCount: {
        type: Number,
        required: true,
    },
    conditionFilteredCount: {
        type: Number,
        required: true,
    },
    conditionFilters: {
        type: Array,
        required: true,
    },
    dateFilters: {
        type: Array,
        required: true,
    },
    filterUrl: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(['set-filter']);

function emitFilter(filters) {
    emit('set-filter', filters);
}

function filterLinkClass(active) {
    return [
        'flex items-center justify-between rounded-md px-2 py-1.5 text-sm',
        active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100',
    ];
}
</script>
