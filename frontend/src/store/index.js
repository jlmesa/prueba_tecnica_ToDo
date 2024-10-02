import { createStore } from 'vuex';
import axios from '../axios';

const store = createStore({
    state: {
        user: null,
        token: null,
    },
    mutations: {
        SET_USER(state, user) {
            state.user = user;
        },
        SET_TOKEN(state, token) {
            state.token = token;
        },
    },
    actions: {
        async register({ commit }, userData) {
            const response = await axios.post('/register', userData);
            commit('SET_USER', response.data);
        },
        async login({ commit }, credentials) {
            const response = await axios.post('login', credentials);
            commit('SET_USER', response.data.user);
            commit('SET_TOKEN', response.data.token);
            return response;
        },
        logout({ commit }) {
            commit('SET_USER', null);
            commit('SET_TOKEN', null);
        },
    },
    getters: {
        isAuthenticated(state) {
            return !!state.token;
        },
        userName(state) {
            return state.user ? state.user.name : '';
        },
    },
});

export default store;
