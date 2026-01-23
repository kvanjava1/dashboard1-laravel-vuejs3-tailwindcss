<template>
    <div class="min-h-screen bg-gradient-to-b from-background-lighter to-blue-50 flex items-center justify-center p-4 relative overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute inset-0 opacity-5 pointer-events-none">
            <div class="absolute top-20 right-0 w-96 h-96 bg-primary rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary rounded-full blur-3xl"></div>
        </div>

        <!-- Login Container -->
        <div class="relative z-10 w-full max-w-md">
            <!-- Logo / Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-3 mb-4">
                    <span class="material-symbols-outlined text-white text-3xl">dashboard</span>
                </div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Admin Panel</h1>
                <p class="text-slate-600 text-sm mt-2">v2.0 PRO</p>
            </div>

            <!-- Login Form Card -->
            <ContentBox>
                <div class="p-2">
                    <!-- Error Message -->
                    <div v-if="authStore.error" class="mb-4 p-3 bg-danger/10 border border-danger rounded-lg">
                        <p class="text-danger text-sm font-medium">{{ authStore.error }}</p>
                    </div>

                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
                        <p class="text-slate-500 text-sm mt-2">Sign in to your account to continue</p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="handleLogin" class="space-y-4">
                        <!-- Email Input -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Email Address
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="you@example.com"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                            />
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Password
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                placeholder="••••••••"
                                class="w-full px-4 py-2.5 rounded-lg border border-border-light bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                            />
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.rememberMe"
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-border-light text-primary focus:ring-primary"
                                />
                                <span class="text-slate-600">Remember me</span>
                            </label>
                            <a href="#" class="text-primary hover:text-primary-dark font-medium">
                                Forgot password?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            variant="primary"
                            full-width
                            class="mt-6"
                            :loading="authStore.isLoading"
                        >
                            <template v-if="authStore.isLoading">Signing In...</template>
                            <template v-else>Sign In</template>
                        </Button>
                    </form>

                    <!-- Divider -->
                    <div class="flex items-center gap-3 my-6">
                        <div class="flex-1 h-px bg-border-light"></div>
                        <span class="text-slate-400 text-sm">or</span>
                        <div class="flex-1 h-px bg-border-light"></div>
                    </div>

                    <!-- Social Buttons -->
                    <div class="grid grid-cols-2 gap-3">
                        <Button
                            variant="outline"
                            full-width
                            left-icon="account_circle"
                        >
                            Google
                        </Button>
                        <Button
                            variant="outline"
                            full-width
                            left-icon="code"
                        >
                            GitHub
                        </Button>
                    </div>

                    <!-- Sign Up Link -->
                    <p class="text-center text-slate-600 text-sm mt-6">
                        Don't have an account?
                        <a href="#" class="text-primary hover:text-primary-dark font-semibold">
                            Sign up here
                        </a>
                    </p>
                </div>
            </ContentBox>

            <!-- Footer Info -->
            <p class="text-center text-slate-600 text-xs mt-6">
                © 2024 Dashboard. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ContentBox from '../../../components/ui/ContentBox.vue'
import Button from '../../../components/ui/Button.vue'

interface LoginForm {
    email: string
    password: string
    rememberMe: boolean
}

const router = useRouter()
const authStore = useAuthStore()

const form = ref<LoginForm>({
    email: '',
    password: '',
    rememberMe: false
})

const handleLogin = async () => {
    try {
        // Clear previous error
        authStore.clearError()

        // Call auth store login
        await authStore.login(form.value.email, form.value.password)

        // Redirect to dashboard on success
        router.push({ name: 'dashboard.index' })
    } catch (error) {
        // Error is already set in authStore.error
        console.error('Login failed:', error)
    }
}
</script>
