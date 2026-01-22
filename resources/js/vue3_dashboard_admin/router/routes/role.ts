import type { RouteRecordRaw } from 'vue-router'

// Import views
import RoleIndex from '../../views/admin/role/Index.vue'
import RoleAdd from '../../views/admin/role/RoleAdd.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/role_management/index',
        name: 'role_management.index',
        component: RoleIndex,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.view', // Using user management permission for now
            title: 'Role Management'
        }
    },
    {
        path: '/role_management/add',
        name: 'role_management.add',
        component: RoleAdd,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.add', // Using user management permission for now
            title: 'Add New Role'
        }
    },
    // Add more role routes here as needed (edit, delete, etc.)
]

export default extendRoutes