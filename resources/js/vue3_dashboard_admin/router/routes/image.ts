import type { RouteRecordRaw } from "vue-router";

// Import views
import Image from "../../views/admin/image/Index.vue";
import ImageAdd from "../../views/admin/image/ImageAdd.vue";

const extendRoutes: RouteRecordRaw[] = [
  {
    path: "/image_management/index",
    name: "image_management.index",
    component: Image,
    meta: {
      requiresAuth: true,
      title: "Image Management",
    },
  },
  {
    path: "/image_management/create",
    name: "image_management.create",
    component: ImageAdd,
    meta: {
      requiresAuth: true,
      title: "Add Image",
    },
  },
  {
    path: "/image_management/edit/:id",
    name: "image_management.edit",
    component: () => import("../../views/admin/image/ImageEdit.vue"),
    meta: {
      requiresAuth: true,
      title: "Edit Image",
    },
  },
];

export default extendRoutes;
