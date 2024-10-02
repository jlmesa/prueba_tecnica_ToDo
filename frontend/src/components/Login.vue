<template>
  <div>
    <h2>INICIAR SESION</h2>
    <form @submit.prevent="login">
      <div>
        <label for="email">Correo Electrónico:</label>
        <input type="email" v-model="email" />
        <p v-if="emailError" class="error">{{ emailError }}</p>
      </div>
      <div>
        <label for="password">Contraseña:</label>
        <input type="password" v-model="password" />
        <p v-if="passwordError" class="error">{{ passwordError }}</p>
        <p v-if="password && password.length < 6" class="error">La contraseña debe tener al menos 6 caracteres.</p>
      </div>
      <div class="btn">
        <button type="submit">Iniciar Sesión</button>
      </div>
      <div class="redirect">
        <router-link to="/register" class="redirect-link">
          ¿No tienes una cuenta? Regístrate aquí
        </router-link>
      </div>
    </form>
    <p v-if="error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
const router = useRouter();
const store = useStore();
const email = ref('');
const password = ref('');
const error = ref('');
const emailError = ref('');
const passwordError = ref('');

const validate = () => {
  emailError.value = email.value ? '' : 'Este campo es obligatorio';
  passwordError.value = password.value ? '' : 'Este campo es obligatorio';
  return !emailError.value && !passwordError.value && password.value.length >= 6;
};

const login = async () => {
  if (!validate()) return;

  Swal.fire({
    title: 'Iniciando Sesión...',
    text: 'Por favor, espera un momento.',
    icon: 'info',
    showConfirmButton: false,
    allowOutsideClick: false,
    willOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    const response = await store.dispatch('login', { email: email.value, password: password.value });
    const token = response.data.token;
    localStorage.setItem('token', token);

    Swal.fire({
      title: 'Inicio de Sesión Exitoso!',
      text: 'Redirigiendo...',
      icon: 'success',
      showConfirmButton: false,
      timer: 3000
    });
    
    setTimeout(() => {
      router.push('/notes');
    }, 3000);
  } catch (err) {
    Swal.fire({
      title: 'Error!',
      text: err.response ? (err.response.data.error || 'Error desconocido') : 'Error de conexión',
      icon: 'error',
      confirmButtonText: 'Intentar de nuevo',
      buttonsStyling: false
    });
  }
};
</script>