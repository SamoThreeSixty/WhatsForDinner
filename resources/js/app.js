import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';

import router from './routes/index.js'
import App from './vue/app.vue';

const app = createApp(App);
const pinia = createPinia();

app.use(router);
app.use(pinia);
app.mount('#app');