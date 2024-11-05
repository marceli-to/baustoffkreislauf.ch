<template>
  <div v-if="error" :class="classes" class="text-raspberry absolute -top-27 right-0 text-xxxs">{{ error }}</div>
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