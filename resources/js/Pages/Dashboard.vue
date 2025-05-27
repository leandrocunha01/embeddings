<template>
    <div class="min-h-screen bg-base-200">
        <!-- Navbar -->
        <div class="navbar bg-base-100 shadow">
            <div class="flex-1">
                <a class="btn btn-ghost text-xl">My Dashboard</a>
            </div>
            <div class="flex-none">
                <button class="btn btn-outline">Logout</button>
            </div>
        </div>

        <!-- Sidebar + Content -->
        <div class="flex">
            <div class="w-64 bg-base-100 p-4 shadow-lg">
                <ul class="menu">
                    <li><a class="active">Home</a></li>
                    <li><a>Reports</a></li>
                    <li><a>Settings</a></li>
                </ul>
            </div>

            <div class="flex-1 p-6">
                <h2 class="text-2xl font-bold mb-4">Welcome back!</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stat bg-base-100 shadow">
                        <div class="stat-title">Users</div>
                        <div class="stat-value">1,234</div>
                    </div>
                    <div class="stat bg-base-100 shadow">
                        <div class="stat-title">Sales</div>
                        <div class="stat-value">$12,345</div>
                    </div>
                    <div class="stat bg-base-100 shadow">
                        <div class="stat-title">Active</div>
                        <div class="stat-value">456</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Nenhuma lógica por enquanto
</script>
