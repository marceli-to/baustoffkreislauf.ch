<template>
  <select :value="modelValue" @input="updateValue($event.target.value)">
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ __(option.label) }}
    </option>
  </select>
  <div v-if="error" class="text-red-600 absolute bottom-5 right-0 text-xxs">{{ error }}</div>
</template>

<script setup>
import { useI18n } from '@/composables/i18n';

const { __ } = useI18n();

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