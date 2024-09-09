<template>
  <div :class="['flex gap-x-15 checkboxes', { 'has-error': error }]">
    <input 
      :id="id" 
      :name="name" 
      :value="value" 
      :checked="checked" 
      :disabled="disabled" 
      :required="required" 
      type="radio" 
      @change="handleChange"
      @update:error="error = $event"
    />
    <label :for="id">{{ label }}</label>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number, Boolean],
    required: true,
  },
  modelValue: {
    type: [String, Number, Boolean],
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    required: true,
  },
  classes: {
    type: [String, Array, Object],
    default: '',
  },
  error: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'update:error']);

const checked = computed(() => props.value === props.modelValue);

const handleChange = (event) => {
  emit('update:modelValue', event.target.value);
  emit('update:error', null);
};

</script>