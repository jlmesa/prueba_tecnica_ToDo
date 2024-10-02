<template>
  <div>
    <nav class="navbar">
      <div class="navbar-left">
        <h1 @click="fetchNotes()">ToDo Notes</h1>
      </div>
      <div class="navbar-right">
        <span>{{ userName }}</span>
        <button @click="logout">Cerrar Sesión</button>
      </div>
    </nav>

    <div class="content">
      <div class="sort-container">
        <label for="sortBy">Ordenar por:</label>
        <select id="sortBy" v-model="sortBy" @change="fetchNotes">
          <option value="created_at">Fecha de Creación</option>
          <option value="fecha_vencimiento">Fecha de Vencimiento</option>
        </select>
      </div>

      <div class="add-note-container">
        <button class="btn-add" @click="openModal">Agregar Nota</button>
      </div>
      <div class="notes-container">
        <div class="note-card" v-for="note in notes" :key="note.id">
          <div v-if="note.imagen">
            <img :src="note.imagen" alt="Imagen de la nota" class="note-image" />
          </div>
          <div v-else>
            <img src="/sin-imagen.jfif" alt="Sin imagen de la nota" class="note-no-image" />
          </div>
          <h3>{{ note.titulo }}</h3>
          <p>{{ note.descripcion }}</p>
          <p><strong>Etiquetas:</strong> {{ note.etiquetas }}</p>
          <p><strong>Fecha de creacion:</strong> {{ formatDate(note.created_at) }}</p>
          <p v-if="note.fecha_vencimiento"><strong>Fecha de vencimiento:</strong> {{ formatDate(note.fecha_vencimiento)
            }}</p>
          <p v-else><strong>Fecha de vencimiento:</strong> Sin fecha de vencimiento</p>
          <div class="note-actions">
            <button class="edit" @click="editNote(note)">Editar</button>
            <button class="delete" @click="deleteNote(note.id)">Eliminar</button>
          </div>
        </div>
      </div>
    </div>

    <modal v-if="isModalOpen" @close="closeModal">
      <template #header>
        <h2>{{ isEditing ? 'Editar Nota' : 'Nueva Nota' }}</h2>
      </template>
      <template #body>
        <form @submit.prevent="isEditing ? updateNote() : createNote()">
          <div>
            <label>Título:</label>
            <input type="text" v-model="note.titulo" />
            <p v-if="tituloError" class="error">{{ tituloError }}</p>
          </div>
          <div>
            <label>Descripción:</label>
            <textarea v-model="note.descripcion"></textarea>
            <p v-if="descripcionError" class="error">{{ descripcionError }}</p>
          </div>
          <div>
            <label>Etiquetas:</label>
            <input type="text" v-model="note.etiquetas" />
            <p v-if="etiquetasError" class="error">{{ etiquetasError }}</p>
          </div>
          <div>
            <label>Imagen:</label>
            <input type="file" @change="onFileChange" />
          </div>
          <div>
            <label>Fecha de Vencimiento:</label>
            <input type="date" v-model="note.fecha_vencimiento" />
            <p v-if="fechaError" class="error">{{ fechaError }}</p>
          </div>
          <div class="btn-modal">
            <button type="submit">{{ isEditing ? 'Actualizar' : 'Crear' }}</button>
          </div>
        </form>
      </template>
    </modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import Toastify from 'toastify-js';
import axios from '../axios';
import Modal from './Modal.vue';
import Swal from 'sweetalert2';
import { format, parseISO, isBefore } from 'date-fns';
import 'toastify-js/src/toastify.css';

const store = useStore();
const router = useRouter();
const userName = computed(() => store.getters.userName);
const notes = ref([]);
const isModalOpen = ref(false);
const isEditing = ref(false);
const note = ref({ titulo: '', descripcion: '', etiquetas: '', user_id: store.getters.userId, imagen: null, fecha_vencimiento: null, created_at: new Date().toISOString() });
const tituloError = ref('');
const descripcionError = ref('');
const etiquetasError = ref('');
const fechaError = ref('');
const sortBy = ref('created_at');

const validateForm = () => {
  let isValid = true;
  tituloError.value = '';
  descripcionError.value = '';
  etiquetasError.value = '';
  fechaError.value = '';

  if (!note.value.titulo) {
    tituloError.value = 'El título es obligatorio';
    isValid = false;
  }

  if (!note.value.descripcion) {
    descripcionError.value = 'La descripción es obligatoria';
    isValid = false;
  }

  if (!note.value.etiquetas) {
    etiquetasError.value = 'Las etiquetas son obligatorias';
    isValid = false;
  }

  const today = new Date();
  if (note.value.fecha_vencimiento) {
    const selectedDate = new Date(note.value.fecha_vencimiento);
    const creationDate = new Date(note.value.created_at);

    if (isBefore(selectedDate, today)) {
      fechaError.value = 'La fecha de vencimiento no puede ser anterior a la fecha actual';
      isValid = false;
    }

    if (isBefore(selectedDate, creationDate)) {
      fechaError.value = 'La fecha de vencimiento no puede ser anterior a la fecha de creación';
      isValid = false;
    }
  }

  return isValid;
};


const fetchNotes = async () => {
  try {
    const response = await axios.get('/notes', {
      params: {
        sort_by: sortBy.value
      }
    });

    notes.value = response.data.map(note => {
      if (note.imagen) {
        note.imagen = import.meta.env.VITE_BASE_URL + note.imagen;
        console.log("URL:", note.imagen)
      }
      return note;
    });
  } catch (error) {
    console.error('Error fetching notes:', error);
  }
};

const formatDate = (dateString) => {
  return format(parseISO(dateString), 'dd MMMM yyyy');
};

const openModal = () => {
  note.value = { titulo: '', descripcion: '', etiquetas: '', user_id: store.getters.userId, imagen: null, fecha_vencimiento: null };
  isEditing.value = false;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    note.value.imagen = file;
  }
};

const createNote = async () => {
  if (!validateForm()) return;
  try {
    const formData = new FormData();
    formData.append('titulo', note.value.titulo);
    formData.append('descripcion', note.value.descripcion);
    formData.append('etiquetas', note.value.etiquetas);
    formData.append('user_id', note.value.user_id);

    if (note.value.imagen) {
      formData.append('imagen', note.value.imagen);
    }

    if (note.value.fecha_vencimiento) {
      formData.append('fecha_vencimiento', note.value.fecha_vencimiento);
    }

    const response = await axios.post('/notes', formData);
    notes.value.push(response.data);

    Toastify({
      text: "Nota creada con éxito",
      duration: 3000,
      gravity: "top",
      position: 'center',
      backgroundColor: "#4caf50",
      stopOnFocus: true
    }).showToast();

    closeModal();
  } catch (error) {
    console.error('Error creating note:', error);
    Toastify({
      text: "Error al crear la nota",
      duration: 3000,
      gravity: "top",
      position: 'center',
      backgroundColor: "#af2222",
      stopOnFocus: true
    }).showToast();
  }
};

const editNote = (selectedNote) => {
  note.value = { ...selectedNote };
  isEditing.value = true;
  isModalOpen.value = true;
};

const updateNote = async () => {
  if (!validateForm()) return;
  try {
    await axios.put(`/notes/${note.value.id}`, note.value);
    const index = notes.value.findIndex(n => n.id === note.value.id);
    notes.value[index] = { ...note.value };

    Toastify({
      text: "Nota actualizada con éxito",
      duration: 3000,
      gravity: "top",
      position: 'center',
      backgroundColor: "#4caf50",
      stopOnFocus: true
    }).showToast();

    closeModal();
  } catch (error) {
    if (error.response) {
      console.error('Error updating note:', error.response.data);
    } else {
      console.error('Error updating note:', error);
    }
    Toastify({
      text: "Error al actualizar la nota",
      duration: 3000,
      gravity: "top",
      position: 'center',
      backgroundColor: "#af2222",
      stopOnFocus: true
    }).showToast();
  }
};

const deleteNote = async (noteId) => {
  Swal.fire({
    title: 'Eliminar Nota',
    text: "¿Realmente desea eliminar la nota?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: '¡Sí, eliminar!',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      axios.delete(`/notes/${noteId}`)
        .then(() => {
          notes.value = notes.value.filter(note => note.id !== noteId);
          Toastify({
            text: "Nota eliminada con éxito",
            duration: 3000,
            gravity: "top",
            position: 'center',
            backgroundColor: "#4caf50",
            stopOnFocus: true
          }).showToast();
        })
        .catch(error => {
          console.error('Error deleting note:', error);
          Toastify({
            text: "Error al eliminar la nota",
            duration: 3000,
            gravity: "top",
            position: 'center',
            backgroundColor: "#af2222",
            stopOnFocus: true
          }).showToast();
        });
    }
  });
};

const logout = () => {
  Swal.fire({
    title: 'Cerrar Sesión',
    text: "¿Realmente desea cerrar sesión?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: '¡Sí, cerrar sesión!',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      store.dispatch('logout');
      localStorage.removeItem('token');
      router.push('/login');
    }
  });
};

onMounted(fetchNotes);
import './Notes.css';
</script>