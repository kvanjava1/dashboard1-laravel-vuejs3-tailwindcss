import type { RouteRecordRaw } from 'vue-router'

// Import views
import Dashboard from '../../views/admin/dashboard/Index.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/dashboard/index',
        name: 'dashboard.index',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            requiredPermission: 'dashboard.view',
            title: 'Dashboard'
        }
    },
    // Add more dashboard routes here as needed
]

export default extendRoutes
