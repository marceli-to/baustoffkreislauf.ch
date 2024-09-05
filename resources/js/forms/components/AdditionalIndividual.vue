<template>
  <div class="space-y-15 lg:space-y-20">
    <form-group v-if="hasName">
      <form-label id="name" :label="__('Name')" :required="requiresName" />
      <form-text-field 
        v-model="individual.name" 
        :error="errors.name"
        @update:error="errors.name = $event"
      />
    </form-group>
    <form-group v-if="hasFirstname">
      <form-label id="firstname" :label="__('Vorname')" :required="requiresFirstname" />
      <form-text-field 
        v-model="individual.firstname" 
        :error="errors.firstname"
        @update:error="errors.firstname = $event"
      />
    </form-group>
    <form-group v-if="hasMealOptions">
      <form-label id="meal_options" :label="__('Menüwunsch')" :required="requiresMealOptions" />
      <form-select-field 
        v-model="individual.meal_options" 
        :error="errors.meal_options"
        @update:error="errors.meal_options = $event"
        :options="mealOptions"
      />
    </form-group>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';
import { useI18n } from '@/composables/i18n';
import FormGroup from '@/forms/components/fields/group.vue';
import FormTextField from '@/forms/components/fields/text.vue';
import FormLabel from '@/forms/components/fields/label.vue';
import FormSelectField from '@/forms/components/fields/select.vue';

const { __ } = useI18n();

const props = defineProps({
  hasName: Boolean,
  requiresName: Boolean,
  hasFirstname: Boolean,
  requiresFirstname: Boolean,
  hasMealOptions: Boolean,
  requiresMealOptions: Boolean,
  mealOptions: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:individual']);


const individual = ref({
  name: null,
  firstname: null,
  meal_options: props.mealOptions[0].value,
});

// Watch for changes in the individual object and emit them to the parent
watch(individual, (newValue) => {
  emit('update:individual', newValue);
}, { deep: true });
</script>