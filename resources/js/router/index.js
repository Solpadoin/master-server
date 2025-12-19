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
                path: 'games',
                name: 'admin.games',
                component: () => import('../views/admin/Games.vue'),
            },
            {
                path: 'instances',
                name: 'admin.instances',
                component: () => import('../views/admin/Instances.vue'),
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
