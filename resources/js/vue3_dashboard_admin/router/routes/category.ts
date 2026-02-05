import type { RouteRecordRaw } from 'vue-router'

import CategoryIndex from '../../views/admin/category/Index.vue'
import CategoryAdd from '../../views/admin/category/CategoryAdd.vue'
import CategoryEdit from '../../views/admin/category/CategoryEdit.vue'

const extendRoutes: RouteRecordRaw[] = [
    {
        path: '/category_management/index',
        name: 'category_management.index',
        component: CategoryIndex,
        meta: {
            title: 'Category Management'
        }
    },
    {
        path: '/category_management/add',
        name: 'category_management.add',
        component: CategoryAdd,
        meta: {
            title: 'Add New Category'
        }
    },
    {
        path: '/category_management/edit/:id',
        name: 'category_management.edit',
        component: CategoryEdit,
        props: true,
        meta: {
            title: 'Edit Category'
        }
    }
]

export default extendRoutes
