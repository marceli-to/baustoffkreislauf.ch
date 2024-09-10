<template>
  <div class="relative">
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="$emit('update:error', '')"
      :class="[
        { '!border-raspberry': error },
      ]">
    </textarea>
    <Error :error="error" />
  </div>
</template>

<script setup>
import Error from './error.vue';
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue', 'update:error']);
</script>
