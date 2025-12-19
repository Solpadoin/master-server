import { createRouter, createWebHistory } from 'vue-router';
import { auth } from '../services/api';

const routes = [
    {
        path: '/',
        redirect: '/admin',
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/admin',
        component: () => import('../views/admin/Dashboard.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'admin.home',
                component: () => import('../views/admin/Home.vue'),
            },
            {
                path: 'instances',
                name: 'admin.instances',
                component: () => import('../views/admin/Instances.vue'),
            },
            {
                path: 'instances/create',
                name: 'admin.instances.create',
                component: () => import('../views/admin/CreateInstance.vue'),
            },
            {
                path: 'instances/:id',
                name: 'admin.instances.show',
                component: () => import('../views/admin/InstanceDashboard.vue'),
            },
            {
                path: 'monitoring',
                name: 'admin.monitoring',
                component: () => import('../views/admin/Monitoring.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const isAuthenticated = auth.isAuthenticated();

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && isAuthenticated) {
        next({ name: 'admin.home' });
    } else {
        next();
    }
});

export default router;
