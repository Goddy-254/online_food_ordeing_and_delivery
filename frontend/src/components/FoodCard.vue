
<script setup>
defineProps({
    food: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <div
        class="bg-white rounded-2xl shadow-sm hover:shadow-lg
        transition duration-300 overflow-hidden border border-gray-100"
    >

        <!-- Food image -->
        <div class="h-52 overflow-hidden bg-gray-100">
            <img
                :src="food.image || '/images/food-placeholder.png'"
                :alt="food.name"
                class="w-full h-full object-cover hover:scale-105 transition duration-300"
            />
        </div>

        <!-- Food information -->
        <div class="p-5">

            <h3 class="text-lg font-bold text-gray-800">
                {{ food.name }}
            </h3>

            <p class="text-sm text-gray-500 mt-2">
                Delicious and freshly prepared.
            </p>

            <div class="flex items-center justify-between mt-5">

                <span class="text-lg font-bold text-green-600">
                    KSh {{ Number(food.price).toLocaleString() }}
                </span>

                <button
                    class="bg-green-600 hover:bg-green-700
                    text-white px-4 py-2 rounded-lg
                    text-sm font-semibold transition"
                >
                    Add to Cart
                </button>

            </div>
        </div>
    </div>
</template>
