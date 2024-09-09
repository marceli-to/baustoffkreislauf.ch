<template>
  <div class="space-y-15 lg:space-y-20">
    <form-group v-if="hasSalutation">
      <form-label id="salutation" :label="__('Anrede')" :required="requiresSalutation" />
      <form-select-field 
        v-model="individual.salutation" 
        :error="__(errors.salutation)"
        @update:error="errors.salutation = $event"
        :options="salutations"
      />
    </form-group>
    <form-group v-if="hasFirstname">
      <form-label id="firstname" :label="__('Vorname')" :required="requiresFirstname" />
      <form-text-field 
        v-model="individual.firstname" 
        :error="__(errors.firstname)"
        @update:error="errors.firstname = $event"
      />
    </form-group>
    <form-group v-if="hasName">
      <form-label id="name" :label="__('Name')" :required="requiresName" />
      <form-text-field 
        v-model="individual.name" 
        :error="__(errors.name)"
        @update:error="errors.name = $event"
      />
    </form-group>
    <form-group v-if="hasEmail">
      <form-label id="email" :label="__('E-Mail')" :required="requiresEmail" />
      <form-text-field 
        v-model="individual.email" 
        :error="__(errors.email)"
        @update:error="errors.email = $event"
      />
    </form-group>
    <template v-if="hasMealOptions">
      <form-group>
        <form-checkbox-field 
          v-model="localWantsMealOptions" 
          :id="'local_wants_meal_options'"
          :name="'local_wants_meal_options'"
          :value="'local_wants_meal_options'"
          :label="__('Essen/Apéro')"
        />
      </form-group>
      <form-group v-if="localWantsMealOptions">
        <form-label id="meal_options" :label="__('Menüwunsch')" :required="requiresMealOptions" />
        <form-select-field 
          v-model="individual.meal_options" 
          :error="__(errors.meal_options)"
          @update:error="errors.meal_options = $event"
          :options="mealOptions"
        />
      </form-group>
    </template>

  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useI18n } from '@/composables/i18n';
import FormGroup from '@/forms/components/fields/group.vue';
import FormTextField from '@/forms/components/fields/text.vue';
import FormLabel from '@/forms/components/fields/label.vue';
import FormSelectField from '@/forms/components/fields/select.vue';
import FormCheckboxField from '@/forms/components/fields/checkbox.vue';

const { __ } = useI18n();

const props = defineProps({
  hasName: Boolean,
  requiresName: Boolean,
  hasFirstname: Boolean,
  requiresFirstname: Boolean,
  hasMealOptions: Boolean,
  requiresMealOptions: Boolean,
  hasSalutation: Boolean,
  requiresSalutation: Boolean,
  hasEmail: Boolean,
  requiresEmail: Boolean,
  salutations: Array,
  mealOptions: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:individual']);
const localWantsMealOptions = ref(false);
const individual = ref({
  salutation: props.salutations[0] ? props.salutations[0].value : null,
  email: null,
  name: null,
  firstname: null,
  meal_options: null,
  wants_meal_options: false,
});

// Watch for changes in the individual object and emit them to the parent
watch(individual, (newValue) => {
  emit('update:individual', newValue);
}, { deep: true });

// Watch for changes in localWantsMealOptions
watch(localWantsMealOptions, (newValue) => {
  emit('update:individual', {
    ...individual.value,
    meal_options: props.mealOptions[0] ? props.mealOptions[0].value : null,
    wants_meal_options: newValue
  });
});
</script>