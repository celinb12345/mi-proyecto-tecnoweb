import axios from 'axios';

// Configurar URL base global
axios.defaults.baseURL = import.meta.env.VITE_APP_URL;

// Enviar cookies de sesión correctamente
//axios.defaults.withCredentials = true;

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
