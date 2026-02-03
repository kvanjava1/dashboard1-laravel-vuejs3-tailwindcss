import type { RouteRecordRaw } from 'vue-router'

// Import views
import Image from '../../views/admin/image/Index.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/image_management/index',
        name: 'image_management.index',
        component: Image,
        meta: {
            requiresAuth: true,
            title: 'Image Management'
        }
    }
]

export default extendRoutes