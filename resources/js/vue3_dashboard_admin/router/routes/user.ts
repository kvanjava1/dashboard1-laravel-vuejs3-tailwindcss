import type { RouteRecordRaw } from 'vue-router'

// Import views
import User from '../../views/admin/user/Index.vue'
import UserAdd from '../../views/admin/user/UserAdd.vue'
import UserEdit from '../../views/admin/user/UserEdit.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/user_management/index',
        name: 'user_management.index',
        component: User,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.view',
            title: 'User Management'
        }
    },
    {
        path: '/user_management/add',
        name: 'user_management.add',
        component: UserAdd,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.add',
            title: 'Add New User'
        }
    },
    {
        path: '/user_management/edit/:id',
        name: 'user_management.edit',
        component: UserEdit,
        meta: {
            requiresAuth: true,
            requiredPermission: 'user_management.edit',
            title: 'Edit User'
        }
    },
    // Add more dashboard routes here as needed
]

export default extendRoutes
