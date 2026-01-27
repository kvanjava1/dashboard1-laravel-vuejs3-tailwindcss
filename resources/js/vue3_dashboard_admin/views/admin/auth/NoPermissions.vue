<template>
    <div class="min-h-screen bg-background-lighter flex items-center justify-center p-4">
        <!-- Background decorations -->
        <div class="absolute inset-0 opacity-5 pointer-events-none">
            <div class="absolute top-20 right-0 w-96 h-96 bg-danger rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-warning rounded-full blur-3xl"></div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 w-full max-w-md text-center">
            <!-- Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center bg-gradient-to-r from-danger to-danger-dark rounded-2xl p-4 mb-6">
                    <span class="material-symbols-outlined text-white text-4xl">lock</span>
                </div>
                <h1 class="text-4xl font-bold text-slate-800 tracking-tight mb-2">Access Denied</h1>
                <p class="text-lg text-slate-600">You don't have permission to access this page</p>
            </div>

            <!-- Access Details -->
            <div class="bg-white p-6 rounded-2xl border border-border-light shadow-soft mb-6">
                <div class="text-left">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-danger text-sm">error</span>
                        <span class="text-sm font-semibold text-slate-700">Access Restricted</span>
                    </div>
                    <p class="text-sm text-slate-500">
                        You don't have sufficient permissions to access this page.
                        Please contact your administrator if you believe this is an error.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button
                    @click="handleLogout"
                    class="w-full px-6 py-3 bg-gradient-to-r from-danger to-danger-dark text-white font-semibold rounded-xl hover:shadow-hard hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined text-sm">logout</span>
                    Logout
                </button>
                
                <button
                    @click="goBack"
                    class="w-full px-6 py-3 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-xl hover:shadow-hard hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Go Back
                </button>

                <button
                    @click="goHome"
                    class="w-full px-6 py-3 border border-border-light text-slate-700 font-semibold rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined text-sm">home</span>
                    Go to Dashboard
                </button>
            </div>

            <!-- Contact Info -->
            <div class="mt-8 pt-6 border-t border-border-light">
                <p class="text-sm text-slate-500">
                    Need help?
                    <a href="mailto:admin@example.com" class="text-primary hover:text-primary-dark font-medium">
                        Contact Administrator
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Component logic remains simple - no sensitive permission info displayed

const goBack = () => {
    // Try to go back in history, fallback to home if not possible
    if (window.history.length > 1) {
        router.go(-1)
    } else {
        goHome()
    }
}

const goHome = () => {
    router.push({ name: 'dashboard.index' })
}

const handleLogout = async () => {
    try {
        await authStore.logout()
        router.push({ name: 'login' })
    } catch (error) {
        console.error('Logout failed:', error)
        // Still redirect to login even if logout fails
        router.push({ name: 'login' })
    }
}
</script>