import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        name: 'Login',
        components: {
            nav: () => import('../components/Nav.vue'),
            main: () => import('../components/LoginRegister/LoginRegisterPage.vue'),
            footer: () => import('../components/Footer.vue')
        }
    },
    {
        path: '/forgetPassword',
        name: 'ForgetPassword',
        components: {
            nav: () => import('../components/Nav.vue'),
            main: () => import('../components/ResetPW/ResetPW.vue'),
            footer: () => import('../components/Footer.vue')
        }
    },
    {
        path: '/fileList/:folderName',
        name: 'FileList',
        components: {
            nav: () => import('../components/Nav.vue'),
            main: () => import('../components/File/FileList.vue'),
            footer: () => import('../components/Footer.vue')
        }
    },
    {
        path: '/shareList',
        name: 'ShareList',
        components: {
            nav: () => import('../components/Nav.vue'),
            main: () => import('../components/File/ShareList.vue'),
            footer: () => import('../components/Footer.vue')
        }
    },
    {
        path: '/share/:link',
        name: 'DownloadShareFile',
        components: {
            nav: () => import('../components/Nav.vue'),
            main: () => import('../components/File/DownloadShareFile.vue'),
            footer: () => import('../components/Footer.vue')
        }
    },
    {
        path: '/setting/account',
        name: 'Account',
        components: {
            nav: () => import('../components/Nav.vue'),
            main: () => import('../components/Setting/Account.vue'),
            footer: () => import('../components/Footer.vue')
        }
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }

]


const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router