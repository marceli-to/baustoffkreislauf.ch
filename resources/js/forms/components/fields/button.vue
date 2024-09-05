<template>
  <button :type="type" :class="classes" :disabled="disabled">{{ label }}</button>
</template>
<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'submit'
  },
  label: {
    type: String,
    required: true
  },
  classes: {
    type: String,
    default: 'border-none rounded-md bg-black hover:bg-turquoise font-acumin-medium font-medium tracking-wide text-white hover:text-black py-15 px-20 leading-none flex items-center transition-all'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});
</script>