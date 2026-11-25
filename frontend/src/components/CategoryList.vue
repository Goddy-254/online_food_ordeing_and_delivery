
<script setup>
defineProps({
    categories: {
        type: Array,
        required: true
    },

    selectedCategory: {
        type: [Number, String],
        default: null
    }
})

const emit = defineEmits(['select-category'])
</script>

<template>
    <section class="mb-8">

        <!-- Section heading -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            Explore Categories
        </h2>

        <!-- Category buttons -->
        <div class="flex flex-wrap gap-3">

            <!-- All categories -->
            <button
                @click="emit('select-category', null)"
                class="px-5 py-2 rounded-full font-medium transition duration-200"
                :class="selectedCategory === null
                    ? 'bg-green-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-green-100'"
            >
                All
            </button>

            <!-- Dynamic categories -->
            <button
                v-for="category in categories"
                :key="category.id"
                @click="emit('select-category', category.id)"
                class="px-5 py-2 rounded-full font-medium transition duration-200"
                :class="selectedCategory === category.id
                    ? 'bg-green-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-green-100'"
            >
                {{ category.name }}
            </button>

        </div>
    </section>
</template>
