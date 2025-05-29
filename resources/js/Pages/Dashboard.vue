<template>
    <div class="container">
        <form @submit.prevent="search" class="form">
            <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar produto..."
                class="input"
            />
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
import {reactive, ref} from 'vue'
import {router, usePage} from '@inertiajs/vue3'

const {props} = usePage()
const products = ref(props.products || [])

const filters = ref({
    search: props.filters?.search || ''
})

function search() {
    router.get('/dashboard', filters.value)
}
</script>

<style>
.container {
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
}

.form {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}

.button {
    padding: 10px 20px;
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    color: white;
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
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    transition: box-shadow 0.3s;
}

.product-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.product-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}

.product-description {
    margin-bottom: 10px;
    color: #555;
}

.product-score {
    font-size: 12px;
    color: #999;
}
</style>
