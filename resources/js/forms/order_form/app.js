import { createApp } from 'vue';
import OrderForm from './OrderForm.vue';
const app = createApp({});

app.component('order-form', OrderForm);

if (document.getElementById('order-form')) {
  app.mount('#order-form');
}
