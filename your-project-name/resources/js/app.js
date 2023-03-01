/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;
// import VueRouter from 'vue-router';
// import routes from './router/index';
// Vue.use(VueRouter);
// // khai báo dùng router này
// const router = new VueRouter({
//     routes,
//     mode: 'history'
// });
// /**
//  * The following block of code may be used to automatically register your
//  * Vue components. It will recursively scan this directory for the Vue
//  * components and automatically register them with their "basename".
//  *
//  * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
//  */

// // const files = require.context('./', true, /\.vue$/i)
// // files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
import ExampleComponent from './components/ExamComponent.vue';
import ExamFileUpload from './components/ExamFileUpload.vue';
import VueAxios from 'vue-axios';
import axios from 'axios';
import App from './App.vue';
Vue.use(VueAxios, axios);
// khai báo các component

import VueRouter from 'vue-router'

Vue.use(VueRouter);



const routes = [
    {
        path: "/",
        name: "Home",
        component: ExampleComponent,
    },
    {
        path: "/upload",
        name: "ExamFileUpload",
        component: ExamFileUpload,
    },
];

const router = new VueRouter({
    mode: "history",
    routes
});



// /**
//  * Next, we will create a fresh Vue application instance and attach it to
//  * the page. Then, you may begin adding components to this application
//  * or customize the JavaScript scaffolding to fit your unique needs.
//  */

const app = new Vue({
    el: '#app',
    router: router,
    render: h => h(App),

});

// import { createApp } from 'vue'
// import App from './App.vue'
// // import router from './router'

// createApp(App).mount('#app')
