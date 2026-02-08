import type { RouteRecordRaw } from 'vue-router'

// Import views
import Gallery from '../../views/admin/gallery/Index.vue'
import CreateGallery from '../../views/admin/gallery/GalleryAdd.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/gallery_management/index',
        name: 'gallery_management.index',
        component: Gallery,
        meta: {
            requiresAuth: true,
            title: 'Gallery Management'
        }
    },
    {
        path: '/gallery_management/create',
        name: 'gallery_management.create',
        component: CreateGallery,
        meta: {
            requiresAuth: true,
            title: 'Create Gallery'
        }
    },
    {
        path: '/gallery_management/edit/:id',
        name: 'gallery_management.edit',
        component: () => import('../../views/admin/gallery/GalleryEdit.vue'),
        meta: {
            requiresAuth: true,
            title: 'Edit Gallery'
        }
    }
]

export default extendRoutes