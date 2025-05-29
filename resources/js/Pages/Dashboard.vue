<template>
    <div class="container">
        <form @submit.prevent="search" class="form">
            <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar produto..."
                class="input"
            />

            <select v-model="filters.category" class="select">
                <option value="">Todas as categorias</option>
                <option
                    v-for="category in categories"
                    :key="category.id"
                    :value="category.id"
                >
                    {{ category.name }}
                </option>
            </select>

            <button type="submit" class="button">
                Buscar
            </button>
        </form>

        <ul class="product-list">
            <li v-for="product in products" :key="product.id" class="product-card">
                <h2 class="product-title">{{ product.payload.name }}</h2>
                <p class="product-description">{{ product.payload.description }}</p>
                <p class="product-score">Score: {{ product.score }}</p>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const { props } = usePage()
const products = ref(props.products || [])

// Categorias com id e name
const categories = ref([
    { id: 1, name: 'Carros' },
    { id: 2, name: 'Frutas' },
    { id: 3, name: 'Celulares' },
    { id: 4, name: 'Físicos famosos' }
])

const filters = ref({
    search: props.filters?.search || '',
    category: props.filters?.category || ''
})

function search() {
    router.get('/dashboard', filters.value)
}
</script>

<style>
.container {
    padding: 40px;
    max-width: 800px;
    margin: 0 auto;
}

.form {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.input, .select {
    flex: 1;
    padding: 16px;
    border: 1px solid #ccc;
    border-radius: 12px;
    font-size: 18px;
}

.button {
    padding: 16px 32px;
    background-color: #007bff;
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
}

.button:hover {
    background-color: #0056b3;
}

.product-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.product-card {
    border: 1px solid #ddd;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 20px;
    transition: box-shadow 0.3s;
}

.product-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.product-description {
    margin-bottom: 15px;
    color: #555;
    font-size: 16px;
}

.product-score {
    font-size: 14px;
    color: #999;
}
</style>
