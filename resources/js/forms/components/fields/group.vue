<template>
  <div :class="classes">
    <slot></slot>
  </div>
</template>

<script setup>
const props = defineProps({
  classes: {
    type: String,
    default: ''
  }
});
</script>