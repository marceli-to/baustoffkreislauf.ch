<template>
  <div class="relative">
    <input
      type="date"
      :value="modelValue"
      :min="min"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="$emit('update:error', '')"
      :placeholder="placeholder"
      :class="[
        { '!border-red-600': error },
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
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  min: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue', 'update:error']);
</script>
