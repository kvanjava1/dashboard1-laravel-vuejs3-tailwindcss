<template>
    <div class="flex h-screen w-full">
        <!-- Sidebar with 3-level menu (Dark) -->
        <aside :class="[
            'mobile-sidebar',
            'lg:flex',
            'flex-col',
            'h-full',
            'border-r',
            'border-border-dark',
            'bg-gradient-to-b',
            'from-background-dark',
            'to-surface-darker',
            'flex-shrink-0',
            'p-6',
            { 'active': sidebarOpen, 'hidden': !sidebarOpen }
        ]">
            <!-- Mobile Sidebar Header with Close Button -->
            <div class="flex items-center justify-between mb-6 lg:hidden">
                <div class="flex items-center gap-3">
                    <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border border-border-dark shadow-lg"
                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDUcrGy7TVAfNeO5QOxOvDWxmgg_Jm3DZxYCU7LJmoGUMGdzwFBLmf9-t2I2m9HWR9oagWB_pMXt1rWyOsseSLHafol9rb16gmxXUgjp2dMCOpBbp-1n9w_WOzmR6KuWmALqH9_T4NaO2gf5M_0_TpJw6P9c2x67lmHtyxyvNKjQaKyQOPFRFOnbxHig8f3xsJWlE-ITSi_wsa-bNLrWLDPVlznpesAA1HRAk8y_vv6lPq9DoWdkDFuokP0sbKWJ7oUxVQ5-U6CF2c");'>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-white text-lg font-bold leading-tight">Admin Panel</h1>
                        <p class="text-accent text-xs font-medium tracking-wider">v2.0 PRO</p>
                    </div>
                </div>
                <button class="text-white hover:text-primary-light p-2" @click="toggleSidebar">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Desktop Sidebar Header -->
            <div class="hidden lg:flex items-center gap-3 mb-10 px-2">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border border-border-dark shadow-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDUcrGy7TVAfNeO5QOxOvDWxmgg_Jm3DZxYCU7LJmoGUMGdzwFBLmf9-t2I2m9HWR9oagWB_pMXt1rWyOsseSLHafol9rb16gmxXUgjp2dMCOpBbp-1n9w_WOzmR6KuWmALqH9_T4NaO2gf5M_0_TpJw6P9c2x67lmHtyxyvNKjQaKyQOPFRFOnbxHig8f3xsJWlE-ITSi_wsa-bNLrWLDPVlznpesAA1HRAk8y_vv6lPq9DoWdkDFuokP0sbKWJ7oUxVQ5-U6CF2c");'>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-white text-lg font-bold leading-tight">Admin Panel</h1>
                    <p class="text-accent text-xs font-medium tracking-wider">v2.0 PRO</p>
                </div>
            </div>

            <!-- Navigation menu -->
            <nav class="flex flex-col gap-1 flex-1 overflow-y-auto">
                <!-- Level 1 - Dashboard -->
                <router-link v-if="authStore.hasPermission('dashboard.view')" to="/dashboard/index"
                    class="flex items-center gap-4 px-4 py-3 rounded-full text-slate-400 hover:bg-surface-dark hover:text-white transition-all duration-200 group active:bg-surface-darker active:text-primary-light active:scale-95"
                    @click="closeMobileSidebar"
                    active-class="bg-primary/10 text-primary-light border border-primary/20">
                    <span
                        class="material-symbols-outlined group-hover:text-primary-light transition-colors">dashboard</span>
                    <p class="text-sm font-bold">Dashboard</p>
                </router-link>

                <!-- Users Menu -->
                <router-link v-if="authStore.hasPermission('user_management.view')" to="/user_management/index"
                    class="flex items-center justify-between px-4 py-3 rounded-full text-slate-400 hover:bg-surface-dark hover:text-white transition-all duration-200 group active:bg-surface-darker active:text-primary-light active:scale-95 cursor-pointer">
                    <div class="flex items-center gap-4">
                        <span
                            class="material-symbols-outlined group-hover:text-primary-light transition-colors">group</span>
                        <p class="text-sm font-medium">User Managements</p>
                    </div>
                </router-link>

                <!-- Roles Menu -->
                <router-link v-if="authStore.hasPermission('role_management.view')" to="/role_management/index"
                    class="flex items-center justify-between px-4 py-3 rounded-full text-slate-400 hover:bg-surface-dark hover:text-white transition-all duration-200 group active:bg-surface-darker active:text-primary-light active:scale-95 cursor-pointer">
                    <div class="flex items-center gap-4">
                        <span
                            class="material-symbols-outlined group-hover:text-primary-light transition-colors">manage_accounts</span>
                        <p class="text-sm font-medium">Role Management</p>
                    </div>
                </router-link>

                <!-- Analytics Menu -->
                <div class="menu-item">
                    <div class="flex items-center justify-between px-4 py-3 rounded-full text-slate-400 hover:bg-surface-dark hover:text-white transition-all duration-200 group active:bg-surface-darker active:text-primary-light active:scale-95 cursor-pointer"
                        @click="toggleMenu('analytics-menu')">
                        <div class="flex items-center gap-4">
                            <span
                                class="material-symbols-outlined group-hover:text-primary-light transition-colors">analytics</span>
                            <p class="text-sm font-medium">Analytics</p>
                        </div>
                        <span
                            :class="['material-symbols-outlined', 'menu-arrow', 'text-sm', { 'rotated': openMenus['analytics-menu'] }]">chevron_right</span>
                    </div>

                    <div :id="'analytics-menu'" :class="['submenu', { 'open': openMenus['analytics-menu'] }]">
                        <router-link to="/admin/analytics"
                            class="flex items-center gap-4 px-4 py-3 rounded-full text-slate-400 hover:bg-surface-dark hover:text-white transition-all duration-200 group active:bg-surface-darker active:text-primary-light active:scale-95 menu-indent-1"
                            @click="closeMobileSidebar">
                            <span
                                class="material-symbols-outlined group-hover:text-primary-light transition-colors text-sm">trending_up</span>
                            <p class="text-sm font-medium">Overview</p>
                        </router-link>
                    </div>
                </div>
            </nav>

            <div class="mt-auto pt-6 border-t border-border-dark">
                <button @click="handleLogout"
                    class="flex items-center gap-4 px-4 py-3 rounded-full text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200 cursor-pointer active:scale-95 w-full text-left">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="text-sm font-medium">Logout</p>
                </button>
            </div>
        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div :class="['sidebar-overlay', 'lg:hidden', { 'active': sidebarOpen }]" @click="toggleSidebar"></div>

        <!-- Main Content Area (Light Theme) -->
        <main
            class="flex-1 flex flex-col h-full relative overflow-hidden bg-gradient-to-br from-background-lighter via-white to-blue-50">
            <!-- Header with Glass Effect -->
            <header
                class="h-20 flex items-center justify-between px-6 lg:px-8 border-b border-border-light glass bg-white/90 backdrop-blur-xl sticky top-0 z-30 shadow-soft">
                <div class="flex items-center gap-6 flex-1 max-w-2xl">
                    <div class="lg:hidden text-slate-700 cursor-pointer hover:text-primary transition-colors"
                        @click="toggleSidebar">
                        <span class="material-symbols-outlined text-2xl">menu</span>
                    </div>
                    <div class="relative w-full max-w-md hidden md:block">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-[20px]">search</span>
                        </div>
                        <input v-model="searchQuery" @input="handleSearch"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white border border-border-light rounded-full text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/50 transition-all shadow-inner-light"
                            placeholder="Search for data, users, docs..." type="text" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button
                        class="relative p-2 text-slate-600 hover:text-primary hover:bg-slate-50 rounded-full transition-colors duration-200 hover:scale-105">
                        <span class="material-symbols-outlined">notifications</span>
                        <span
                            class="absolute top-1.5 right-2 size-2.5 bg-danger rounded-full border-2 border-white animate-pulse"></span>
                    </button>

                    <!-- Profile with Dropdown -->
                    <div class="relative">
                        <div class="flex items-center gap-3 pl-6 border-l border-border-light cursor-pointer group"
                            @click="toggleProfileDropdown">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-800">{{ user.name }}</p>
                                <p class="text-xs text-slate-500">{{ user.role }}</p>
                            </div>
                            <div class="relative">
                                <div class="size-10 rounded-full bg-cover bg-center border-2 border-white shadow-md group-hover:border-primary transition-all duration-300"
                                    :style="`background-image: url('${user.avatar}')`">
                                </div>
                                <div
                                    class="absolute -bottom-1 -right-1 size-3.5 bg-success rounded-full border-2 border-white">
                                </div>
                            </div>
                            <span
                                :class="['material-symbols-outlined', 'text-slate-400', 'text-sm', 'group-hover:text-primary', 'transition-colors']">expand_more</span>
                        </div>

                        <!-- Profile Dropdown Menu -->
                        <div
                            :class="['profile-dropdown', 'absolute', 'right-0', 'mt-3', 'w-64', 'bg-white', 'rounded-xl', 'border', 'border-border-light', 'shadow-hard', 'z-50', { 'show': showProfileDropdown }]">
                            <div
                                class="p-4 border-b border-border-light bg-gradient-to-r from-primary/5 to-secondary/5">
                                <div class="flex items-center gap-3">
                                    <div class="size-12 rounded-full bg-cover bg-center border-2 border-white shadow-md"
                                        :style="`background-image: url('${user.avatar}')`">
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ user.name }}</p>
                                        <p class="text-xs text-slate-500">{{ user.email }}</p>
                                        <p
                                            class="text-xs text-primary font-semibold mt-1 px-2 py-0.5 bg-primary/10 rounded-full inline-block">
                                            {{ user.role }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="py-2">
                                <router-link to="/admin/profile"
                                    class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:text-primary hover:bg-slate-50 transition-colors">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                    <span class="text-sm font-medium">My Profile</span>
                                </router-link>
                                <router-link to="/admin/settings"
                                    class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:text-primary hover:bg-slate-50 transition-colors">
                                    <span class="material-symbols-outlined text-sm">settings</span>
                                    <span class="text-sm font-medium">Account Settings</span>
                                </router-link>
                            </div>
                            <div class="p-3 border-t border-border-light bg-slate-50/50">
                                <button @click="handleLogout"
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-white text-danger hover:bg-danger/10 hover:text-danger-dark transition-all cursor-pointer border border-border-light shadow-sm w-full">
                                    <span class="material-symbols-outlined text-sm">logout</span>
                                    <span class="text-sm font-medium">Log Out</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 space-y-6 lg:space-y-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Reactive state
const sidebarOpen = ref(false)
const showProfileDropdown = ref(false)
const searchQuery = ref('')
const openMenus = reactive<Record<string, boolean>>({
    'users-menu': true,
    'analytics-menu': false,
    'settings-menu': false
})

// User data (replace with actual auth data)
const user = reactive({
    name: 'Admin User',
    email: 'admin@example.com',
    role: 'Super Admin',
    avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAmhtkm9iCmViNWJB0Yzmd6NVIcNUgWLAscqdvfSH8O7OUDOxIT2LBZVMg3BKk04dWHNu8nHsjmouKQzSJydBfxPsvY_ZpRNhufIiOkkqbsbWI7EP2XbQzai7KRe9oZNRLpyI7SbU6yzS5M4CfnWWc9A0jjlOEIGGHD2CPlC4gSh2dHZp4_lxzXEfbL7nizekYJoLTYTuf5Zl6LtHVa9Sjrp4k-SRziH9pJTj6jbqF3yiKpfnCp56pBOytvhWxqN-vFwkYJ9jIcTuE'
})

// Methods
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
    if (sidebarOpen.value) {
        document.body.classList.add('sidebar-open')
    } else {
        document.body.classList.remove('sidebar-open')
    }
}

const closeMobileSidebar = () => {
    if (window.innerWidth < 1024) {
        sidebarOpen.value = false
        document.body.classList.remove('sidebar-open')
    }
}

const toggleMenu = (menuId: string) => {
    openMenus[menuId] = !openMenus[menuId]
}

const toggleProfileDropdown = () => {
    showProfileDropdown.value = !showProfileDropdown.value
}

const handleSearch = () => {
    // Implement search functionality
    console.log('Searching for:', searchQuery.value)
}

const handleLogout = async () => {
    // Implement logout logic
    try {
        // await logout API call
        router.push({ name: 'login' })
    } catch (error) {
        console.error('Logout failed:', error)
    }
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const dropdown = document.getElementById('profile-dropdown')
    const profileArea = document.querySelector('.relative')

    if (dropdown && profileArea && !profileArea.contains(event.target as Node)) {
        showProfileDropdown.value = false
    }
}

// Close sidebar on escape key
const handleEscapeKey = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        sidebarOpen.value = false
        showProfileDropdown.value = false
        document.body.classList.remove('sidebar-open')
    }
}

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleEscapeKey)
})
</script>