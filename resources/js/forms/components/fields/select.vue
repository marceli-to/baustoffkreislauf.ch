<template>
  <select :value="modelValue" @input="updateValue($event.target.value)">
    <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
  </select>
  <div v-if="error" class="text-red-600 absolute bottom-5 right-0 text-xxs">{{ error }}</div>
</template>

<script setup>

const props = defineProps({
  options: {
    type: [Array, Object],
    required: true,
  },
  modelValue: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (value) => {
  emit('update:modelValue', value);
};
</script>