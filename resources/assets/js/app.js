/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');
window.axios = require('axios');

import VueRouter from 'vue-router';
import routes from './routes';
import iView from 'iview';
import 'iview/dist/styles/iview.css';


import flvjs from 'flv.js';

import 'vue-github-buttons/dist/vue-github-buttons.css';
import VueGitHubButtons from 'vue-github-buttons';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(VueGitHubButtons, {useCache: true});
Vue.use(VueRouter);
Vue.use(iView);
Vue.prototype.$flvjs = flvjs;

const router = new VueRouter({
    routes
});

const app = new Vue({
    router,
    el: '#app',
});

