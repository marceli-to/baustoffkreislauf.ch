<template>
  <div :class="['checkboxes flex gap-x-15', classes]">
    <input 
      :id="id" 
      :name="name" 
      :value="value" 
      :checked="checked" 
      :disabled="disabled" 
      :required="required" 
      type="checkbox" 
      @change="handleChange"
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
    type: String,
    required: true,
  },
  modelValue: {
    type: [String, Boolean],
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
});

const emit = defineEmits(['update:modelValue']);

const checked = computed(() => props.value === props.modelValue);

const handleChange = (event) => {
  emit('update:modelValue', event.target.checked);
};
</script>