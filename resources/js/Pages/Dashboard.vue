<template>
    <div class="p-4">
        <form @submit.prevent="search" class="mb-4 flex gap-2">
            <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar produto..."
                class="border rounded p-2 w-full"
            />
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">
                Buscar
            </button>
        </form>

        <ul class="space-y-2">
            <li
                v-for="product in products"
                :key="product.id"
                class="border p-2 rounded"
            >
                <h2 class="font-bold">{{ product.payload.name }}</h2>
                <p>{{ product.payload.description }}</p>
                <p>{{ product.score }}</p>
            </li>
        </ul>
    </div>
</template>

<script setup>
import {reactive, ref} from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const { props } = usePage()
const products = ref(props.products || [])

const filters = ref({
    search: props.filters?.search || ''
})

function search() {
    router.get('/dashboard', filters.value)
}
</script>
