import type { RouteRecordRaw } from 'vue-router'

// Import views
import Login from '../../views/admin/auth/Login.vue'
import NoPermissions from '../../views/admin/auth/NoPermissions.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    {
        path: '/no-permissions',
        name: 'no-permissions',
        component: NoPermissions,
        meta: {
            title: 'Access Denied'
        }
    },
    // Add more auth routes here (register, forgot-password, etc.)
]

export default extendRoutes
