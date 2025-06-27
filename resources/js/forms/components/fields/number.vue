<template>
  <div class="relative">
    <input
      type="number"
      min="1"
      step="1"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="$emit('update:error', '')"
      :placeholder="placeholder"
      :class="[
        { '!border-raspberry': error },
      ]"
    >
    <Error :error="error" />
  </div>
</template>

<script setup>
import Error from './error.vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
});

defineEmits(['update:modelValue', 'update:error']);
</script>
