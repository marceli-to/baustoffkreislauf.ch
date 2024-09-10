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
    <form-group v-if="hasCostCenter">
      <form-label id="cost_center" :label="__('Kostenstelle')" :required="requiresCostCenter" />
      <form-textarea-field 
        v-model="individual.cost_center" 
        :error="__(errors.cost_center)"
        @update:error="errors.cost_center = $event"
      />
    </form-group>
    <template v-if="hasMealOptions">
      <form-group>
        <label>{{ __('Essen/Apéro') }}</label>
        <div class="flex gap-x-20 lg:gap-x-30 items-center mt-3 lg:mt-10 relative">
          <form-radio-field 
            v-model="individual.wants_meal_options" 
            :id="'individual_wants_meal_options_yes'"
            :name="'individual_wants_meal_options'"
            :error="__(errors.wants_meal_options)"
            @update:error="errors.wants_meal_options = $event"
            @update:modelValue="individual.wants_meal_options = $event"
            :value="'true'"
            :label="__('Ja')"
          />
          <form-radio-field 
            v-model="individual.wants_meal_options" 
            :id="'individual_wants_meal_options_no'"
            :name="'individual_wants_meal_options'"
            :error="__(errors.wants_meal_options)"
            @update:error="errors.wants_meal_options = $event"
            @update:modelValue="individual.wants_meal_options = $event"
            :value="'false'"
            :label="__('Nein')"
          />
          <Error :error="__(errors.wants_meal_options)" />
        </div>
      </form-group>
      <form-group v-if="individual.wants_meal_options == 'true'">
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
import FormTextareaField from '@/forms/components/fields/textarea.vue';
import FormLabel from '@/forms/components/fields/label.vue';
import FormSelectField from '@/forms/components/fields/select.vue';
import FormRadioField from '@/forms/components/fields/radio.vue';
import Error from '@/forms/components/fields/error.vue';

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
  hasCostCenter: Boolean,
  requiresCostCenter: Boolean,
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
const individual = ref({
  salutation: props.salutations[0] ? props.salutations[0].value : null,
  email: null,
  name: null,
  firstname: null,
  wants_meal_options: null,
  meal_options: null,
  cost_center: null,
});

// Watch for changes in the individual object and emit them to the parent
watch(individual, (newValue) => {
  emit('update:individual', newValue);
}, { deep: true });

// Watch for changes in wants_meal_options
watch(() => individual.value.wants_meal_options, (newValue) => {
  if (newValue === 'true') {
    individual.value.meal_options = props.mealOptions[0]?.value ?? null;
  } else {
    individual.value.meal_options = null;
  }
});
</script>