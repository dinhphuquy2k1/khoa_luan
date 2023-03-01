import { createWebHistory, createRouter } from 'vue-router';
import home from './components/ExamSelection.vue';
import ExamFileUpload from './components/ExamFileUpload.vue';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: home
    },
    {
        path: '/upload',
        name: 'ExamFileUpload',
        component: ExamFileUpload
    },

];

const router = createRouter({
    history: createWebHistory(),
    routes
});



export default router;
