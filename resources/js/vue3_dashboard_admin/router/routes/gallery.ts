import type { RouteRecordRaw } from 'vue-router'

// Import views
import Gallery from '../../views/admin/gallery/Index.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/gallery_management/index',
        name: 'gallery_management.index',
        component: Gallery,
        meta: {
            requiresAuth: true,
            title: 'Gallery Management'
        }
    }
]

export default extendRoutes