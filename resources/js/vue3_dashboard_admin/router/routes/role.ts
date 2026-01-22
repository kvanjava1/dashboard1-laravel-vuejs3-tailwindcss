import type { RouteRecordRaw } from 'vue-router'

// Import views
import RoleIndex from '../../views/admin/role/Index.vue'
import RoleAdd from '../../views/admin/role/RoleAdd.vue'
import RoleEdit from '../../views/admin/role/RoleEdit.vue'

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
    {
        path: '/role_management/edit/:id',
        name: 'role_management.edit',
        component: RoleEdit,
        props: true,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.edit', // Using user management permission for now
            title: 'Edit Role'
        }
    },
    // Add more role routes here as needed (delete, etc.)
]

export default extendRoutes