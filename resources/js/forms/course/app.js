import { createApp } from 'vue';
import CourseRegistration from './CourseRegistration.vue';
const app = createApp({});

app.component('course-registration', CourseRegistration);

if (document.getElementById('course-form')) {
  app.mount('#course-form');
}
