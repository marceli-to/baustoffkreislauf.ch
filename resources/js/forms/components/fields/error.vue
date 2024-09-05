<template>
  <div v-if="error" :class="classes" class="text-red-600 absolute -top-27 right-0 text-xxs">{{ error }}</div>
</template>
<script setup>

const props = defineProps({
  error: {
    type: String,
    default: ''
  },
  classes: {
    type: String,
    default: ''
  }
});
</script>