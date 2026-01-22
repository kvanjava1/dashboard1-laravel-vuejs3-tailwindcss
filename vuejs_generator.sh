#!/bin/bash

# ============================================
# CREATE VUE 3 DASHBOARD - WITH BASIC CODE
# ============================================
BASE_DIR="resources/js/vue3_dashboard_admin"
CURRENT_DIR=$(pwd)

echo "🚀 Creating Vue 3 Dashboard with Initial Code..."
echo "📁 Base directory: $CURRENT_DIR/$BASE_DIR"
echo ""

# Create root directory
mkdir -p "$BASE_DIR"
cd "$BASE_DIR" || exit

# ============================================
# 1. app.ts - MAIN VUE APP ENTRY
# ============================================
cat > app.ts << 'EOL'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Import main CSS (Tailwind will be processed by Vite)
import '../css/app.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')
EOL

# ============================================
# 2. App.vue - ROOT COMPONENT
# ============================================
cat > App.vue << 'EOL'
<template>
  <router-view />
</template>

<script setup lang="ts">
// Root app component - just renders router views
</script>
EOL

# ============================================
# 3. ROUTER FILES
# ============================================
mkdir -p router/routes

# router/index.ts
cat > router/index.ts << 'EOL'
import { createRouter, createWebHistory } from 'vue-router'
import adminRoutes from './routes/admin'
import authRoutes from './routes/auth'

// Layout imports
import AdminLayout from '@/layouts/AdminLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

// Route wrapper function for layouts
const wrapLayout = (layout: any, children: any[]) => ({
  path: '',
  component: layout,
  children
})

const routes = [
  // Admin routes with AdminLayout
  {
    path: '/admin',
    ...wrapLayout(AdminLayout, adminRoutes)
  },
  
  // Auth routes with AuthLayout  
  {
    path: '/auth',
    ...wrapLayout(AuthLayout, authRoutes)
  },
  
  // Public route (no layout wrapper)
  {
    path: '/',
    name: 'landing',
    component: () => import('@/views/public/Landing.vue')
  },
  
  // 404 fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/admin/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Global guards can be added here
router.beforeEach((to, from, next) => {
  console.log(`Navigating to: ${to.path}`)
  next()
})

export default router
EOL

# router/routes/admin.ts
cat > router/routes/admin.ts << 'EOL'
// Admin routes - all use AdminLayout via wrapper in router/index.ts
export default [
  {
    path: 'dashboard',
    name: 'admin.dashboard',
    component: () => import('@/views/admin/Dashboard/Index.vue')
  },
  {
    path: 'users',
    name: 'admin.users',
    component: () => import('@/views/admin/Users/Index.vue')
  },
  // Add more admin routes here
]
EOL

# router/routes/auth.ts
cat > router/routes/auth.ts << 'EOL'
// Auth routes - all use AuthLayout
export default [
  {
    path: 'login',
    name: 'auth.login',
    component: () => import('@/views/auth/Login.vue')
  },
  {
    path: 'register',
    name: 'auth.register', 
    component: () => import('@/views/auth/Register.vue')
  }
]
EOL

# ============================================
# 4. LAYOUTS
# ============================================
mkdir -p layouts

# AdminLayout.vue
cat > layouts/AdminLayout.vue << 'EOL'
<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 p-6">
      <h1 class="text-xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>
      <nav class="space-y-2">
        <router-link 
          to="/admin/dashboard" 
          class="block p-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600"
          active-class="bg-blue-50 text-blue-600"
        >
          📊 Dashboard
        </router-link>
        <router-link 
          to="/admin/users" 
          class="block p-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600"
          active-class="bg-blue-50 text-blue-600"
        >
          👥 Users
        </router-link>
      </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="flex-1 p-8">
      <div class="max-w-7xl mx-auto">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
// Admin layout with sidebar
</script>
EOL

# AuthLayout.vue  
cat > layouts/AuthLayout.vue << 'EOL'
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900">
          <slot name="title">Authentication</slot>
        </h2>
      </div>
      <router-view />
    </div>
  </div>
</template>

<script setup lang="ts">
// Simple centered auth layout
</script>
EOL

# ============================================
# 5. VIEWS & DASHBOARD COMPONENTS
# ============================================

# Dashboard Index (as folder)
mkdir -p views/admin/Dashboard/components
cat > views/admin/Dashboard/Index.vue << 'EOL'
<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Overview</h1>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatsCards />
    </div>
    
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <ChartWidget />
      </div>
      <div>
        <ActivityFeed />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import StatsCards from './components/StatsCards.vue'
import ChartWidget from './components/ChartWidget.vue'
import ActivityFeed from './components/ActivityFeed.vue'
</script>
EOL

# Dashboard Components
cat > views/admin/Dashboard/components/StatsCards.vue << 'EOL'
<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 w-full">
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
      <p class="text-3xl font-bold mt-2">1,254</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      <h3 class="text-sm font-medium text-gray-500">Revenue</h3>
      <p class="text-3xl font-bold mt-2">$24,580</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      <h3 class="text-sm font-medium text-gray-500">Active Now</h3>
      <p class="text-3xl font-bold mt-2">42</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      <h3 class="text-sm font-medium text-gray-500">Conversion</h3>
      <p class="text-3xl font-bold mt-2">3.2%</p>
    </div>
  </div>
</template>

<script setup lang="ts">
// Stats cards component
</script>
EOL

cat > views/admin/Dashboard/components/ChartWidget.vue << 'EOL'
<template>
  <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Performance Chart</h3>
    <div class="h-64 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg">
      <p class="text-gray-500">Chart Component Area</p>
    </div>
  </div>
</template>

<script setup lang="ts">
// Chart widget placeholder
</script>
EOL

cat > views/admin/Dashboard/components/ActivityFeed.vue << 'EOL'
<template>
  <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
    <ul class="space-y-4">
      <li class="flex items-center">
        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
        <span class="text-gray-700">User login from new device</span>
      </li>
      <li class="flex items-center">
        <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
        <span class="text-gray-700">New order #ORD-7890</span>
      </li>
      <li class="flex items-center">
        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
        <span class="text-gray-700">System backup completed</span>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
// Activity feed component
</script>
EOL

# Other views
mkdir -p views/auth
cat > views/auth/Login.vue << 'EOL'
<template>
  <div>
    <h2 class="text-2xl font-bold text-center mb-8">Sign In to Dashboard</h2>
    <form class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" class="mt-1 block w-full border border-gray-300 rounded-md p-3">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" class="mt-1 block w-full border border-gray-300 rounded-md p-3">
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-md font-medium hover:bg-blue-700">
        Sign In
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
// Login component
</script>
EOL

cat > views/auth/Register.vue << 'EOL'
<template>
  <div>
    <h2 class="text-2xl font-bold text-center mb-8">Create Account</h2>
    <form class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700">Full Name</label>
        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-3">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" class="mt-1 block w-full border border-gray-300 rounded-md p-3">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" class="mt-1 block w-full border border-gray-300 rounded-md p-3">
      </div>
      <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-md font-medium hover:bg-green-700">
        Create Account
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
// Register component
</script>
EOL

mkdir -p views/public
cat > views/public/Landing.vue << 'EOL'
<template>
  <div class="text-center py-20">
    <h1 class="text-4xl font-bold text-gray-900 mb-4">Welcome to Admin Dashboard</h1>
    <p class="text-gray-600 mb-8">A modern Vue 3 dashboard built with Laravel & TypeScript</p>
    <router-link to="/auth/login" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium">
      Get Started
    </router-link>
  </div>
</template>

<script setup lang="ts">
// Landing page
</script>
EOL

mkdir -p views/admin/Users
cat > views/admin/Users/Index.vue << 'EOL'
<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">
        + Add User
      </button>
    </div>
    <div class="bg-white rounded-xl shadow border border-gray-100 p-6">
      <p class="text-gray-500">User table will be displayed here</p>
    </div>
  </div>
</template>

<script setup lang="ts">
// Users page
</script>
EOL

# ============================================
# 6. CORE FILES (Pinia, Types, Utils)
# ============================================
mkdir -p stores
cat > stores/index.ts << 'EOL'
import { defineStore } from 'pinia'

// Example store
export const useAppStore = defineStore('app', {
  state: () => ({
    sidebarOpen: true,
    theme: 'light'
  }),
  actions: {
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen
    }
  }
})
EOL

mkdir -p types
cat > types/index.ts << 'EOL'
// Global TypeScript types

export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user' | 'editor'
}

export interface DashboardStats {
  users: number
  revenue: number
  active: number
  conversion: number
}
EOL

mkdir -p composables
cat > composables/useApi.ts << 'EOL'
// API composable example
import { ref } from 'vue'

export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  const fetchData = async (url: string) => {
    loading.value = true
    try {
      const response = await fetch(url)
      return await response.json()
    } catch (err) {
      error.value = err
    } finally {
      loading.value = false
    }
  }

  return { loading, error, fetchData }
}
EOL

mkdir -p utils
cat > utils/api.ts << 'EOL'
// Axios instance configuration
import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(config => {
  // Add auth token here
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api
EOL

mkdir -p components/ui
cat > components/ui/BaseCard.vue << 'EOL'
<template>
  <div class="bg-white rounded-xl shadow border border-gray-100 p-6">
    <slot />
  </div>
</template>

<script setup lang="ts">
// Base card component
</script>
EOL

# ============================================
# 7. LARAVEL BLADE ENTRY POINT
# ============================================
cd "$CURRENT_DIR" || exit
mkdir -p resources/views

cat > resources/views/app.blade.php << 'EOL'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vue 3 Admin Dashboard</title>
    
    <!-- Vite will inject CSS/JS here -->
    @vite(['resources/js/vue3_dashboard_admin/app.ts', 'resources/css/app.css'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
EOL

# ============================================
# COMPLETION
# ============================================
cd "$BASE_DIR" || exit

echo "✅ Dashboard structure created with initial code!"
echo ""
echo "📁 Total files created:"
find . -type f \( -name "*.ts" -o -name "*.vue" \) | wc -l
echo ""
echo "🚀 NEXT STEPS:"
echo "1. Install dependencies:"
echo "   npm install vue@next vue-router@4 pinia axios"
echo "   npm install -D typescript @vue/tsconfig @types/node"
echo ""
echo "2. Update Laravel's vite.config.js:"
echo "   - Add vue() plugin"
echo "   - Set alias: '@': '/resources/js/vue3_dashboard_admin'"
echo ""
echo "3. Add to package.json scripts:"
echo "   \"dev\": \"vite\""
echo "   \"build\": \"vite build\""
echo ""
echo "4. Create resources/css/app.css with Tailwind directives"
echo ""
echo "5. Run: npm run dev"
echo ""
echo "6. Create Laravel route in routes/web.php:"
echo "   Route::get('/{any}', function () {"
echo "       return view('app');"
echo "   })->where('any', '.*');"
echo ""
echo "📌 Your dashboard will be available at:"
echo "   - /admin/dashboard"
echo "   - /auth/login"
echo "   - / (landing page)"