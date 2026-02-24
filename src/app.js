import '@/bootstrap';
import '@/styles/app.css';
import {createApp} from 'vue';
import {createPinia} from 'pinia';
import {useAuthStore} from '@/stores/auth';

import router from './routes/index.js';
import App from './app.vue';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

const authStore = useAuthStore(pinia);
await authStore.verify();

app.mount('#app');
