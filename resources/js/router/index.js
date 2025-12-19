import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../views/Home.vue'),
    },
    {
        path: '/admin',
        name: 'admin',
        component: () => import('../views/admin/Dashboard.vue'),
        children: [
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

export default router;
