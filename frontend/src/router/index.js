import { createRouter, createWebHistory } from 'vue-router';
import Notes from '../components/Notes.vue';
import Login from '../components/Login.vue';
import Register from '../components/Register.vue';
import store from '../store';

const routes = [
  { path: '/', redirect: '/login' }, 
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/notes', component: Notes },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters.isAuthenticated;
  if (to.path === '/notes' && !isAuthenticated) {
    next('/login');
  } else {
    next();
  }
})

export default router;
