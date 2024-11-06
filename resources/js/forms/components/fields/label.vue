<template>
  <label :for="id" :class="classes" class="block mb-5">
    {{ label }} <span v-if="required">*</span>
  </label>
</template>
<script setup>
const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  classes: {
    type: String,
    default: ''
  }
});
</script>