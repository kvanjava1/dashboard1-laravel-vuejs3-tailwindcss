import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { apiRoutes } from '@/config/apiRoutes';

interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  status?: string;
  profile_image?: string;
  role: string;
  permissions: string[];
}

interface AuthState {
  token: string | null;
  user: User | null;
  isLoading: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', () => {
  // State
  const token = ref<string | null>(localStorage.getItem('auth_token'));
  const user = ref<User | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const currentUser = computed(() => user.value);
  const userRole = computed(() => user.value?.role ?? null);

  // Check permission
  const hasPermission = (permission: string): boolean => {
    if (!user.value) return false;
    return user.value.permissions.includes(permission);
  };

  // Check role
  const hasRole = (role: string): boolean => {
    return userRole.value === role;
  };

  // Check multiple permissions (any)
  const hasAnyPermission = (permissions: string[]): boolean => {
    if (!user.value) return false;
    return permissions.some(p => user.value!.permissions.includes(p));
  };

  // Check all permissions
  const hasAllPermissions = (permissions: string[]): boolean => {
    if (!user.value) return false;
    return permissions.every(p => user.value!.permissions.includes(p));
  };

  // Actions
  const login = async (email: string, password: string): Promise<void> => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await fetch(apiRoutes.auth.login, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Login failed');
      }

      const data = await response.json();

      // Store token
      token.value = data.token;
      user.value = data.user;

      // Persist token
      localStorage.setItem('auth_token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'An error occurred';
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async (): Promise<void> => {
    isLoading.value = true;
    error.value = null;

    try {
      if (token.value) {
        await fetch(apiRoutes.auth.logout, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token.value}`,
          },
        });
      }
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      // Clear state regardless of API call
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      isLoading.value = false;
    }
  };

  const fetchUser = async (): Promise<void> => {
    if (!token.value) return;

    isLoading.value = true;
    error.value = null;

    try {
      const response = await fetch(apiRoutes.auth.me, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token.value}`,
        },
      });

      if (!response.ok) {
        throw new Error('Failed to fetch user');
      }

      const data = await response.json();
      user.value = data.user;
      localStorage.setItem('user', JSON.stringify(data.user));

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch user';
      // Token might be invalid, clear auth
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const restoreSession = async (): Promise<void> => {
    // Try to restore from localStorage
    const savedToken = localStorage.getItem('auth_token');
    const savedUser = localStorage.getItem('user');

    if (savedToken && savedUser) {
      token.value = savedToken;
      user.value = JSON.parse(savedUser);

      // Try to fetch fresh user data from API
      try {
        await fetchUser();
      } catch (err) {
        // If fetch fails, clear everything
        token.value = null;
        user.value = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
      }
    }
  };

  const clearError = (): void => {
    error.value = null;
  };

  return {
    // State
    token,
    user,
    isLoading,
    error,
    // Getters
    isAuthenticated,
    currentUser,
    userRole,
    // Methods
    login,
    logout,
    fetchUser,
    restoreSession,
    hasPermission,
    hasRole,
    hasAnyPermission,
    hasAllPermissions,
    clearError,
  };
});
