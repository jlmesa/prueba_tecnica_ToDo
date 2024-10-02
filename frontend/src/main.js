import { createApp } from 'vue'
import App from './App.vue'
import store from './store';
import axios from './axios';
import router from './router';
import './estilos.css';

const app = createApp(App);
app.config.globalProperties.$axios = axios;
app.use(store);
app.use(router);
app.mount('#app');
