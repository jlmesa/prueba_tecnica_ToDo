<template>
  <div>
    <h2>CREAR CUENTA</h2>
    <form @submit.prevent="register">
      <div>
        <label for="name">Nombre:</label>
        <input type="text" v-model="name" />
        <p v-if="nameError" class="error">{{ nameError }}</p>
      </div>
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
        <button type="submit">Registrarse</button>
      </div>
      <div class="redirect">
        <router-link to="/login" class="redirect-link">
          ¿Ya tienes una cuenta? Inicia sesión aquí
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


const store = useStore();
const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const error = ref('');
const nameError = ref('');
const emailError = ref('');
const passwordError = ref('');

const validate = () => {
  nameError.value = name.value ? '' : 'Este campo es obligatorio';
  emailError.value = email.value ? '' : 'Este campo es obligatorio';
  passwordError.value = password.value ? '' : 'Este campo es obligatorio';
  return !nameError.value && !emailError.value && !passwordError.value && password.value.length >= 6;
};

const register = async () => {
  if (!validate()) return;

  Swal.fire({
    title: 'Registrando...',
    text: 'Por favor, espera un momento.',
    icon: 'info',
    showConfirmButton: false,
    allowOutsideClick: false,
    willOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    await store.dispatch('register', { name: name.value, email: email.value, password: password.value });
   
    Swal.fire({
      title: 'Registro Exitoso!',
      text: 'Tu cuenta ha sido creada exitosamente.',
      icon: 'success',
      confirmButtonText: 'Ir a Iniciar Sesión'
    }).then(() => {
      router.push('/login');
    });

  } catch (err) {
    Swal.fire({
      title: 'Error!',
      text: err.response ? (err.response.data.error || 'Error desconocido') : 'Error de conexión',
      icon: 'error',
      confirmButtonText: 'Intentar de nuevo'
    });
  }
};
</script>
