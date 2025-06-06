<template>
  <template v-if="formSuccess">
    <success-alert>
      {{ __('Vielen Dank für Ihre Anmeldung!') }}
    </success-alert>
  </template>
  <template v-if="formError">
    <error-alert>
      {{ __('Bitte überprüfen Sie die eingegebenen Daten.') }}
    </error-alert>
  </template>
  <form @submit.prevent="submitForm" v-if="isLoaded" class="space-y-15 lg:space-y-30 max-w-2xl">
    <form-group v-if="hasSalutation">
      <form-label id="salutation" :label="__('Anrede')" :required="requiresSalutation" />
      <form-select-field 
        v-model="form.salutation" 
        :error="__(errors.salutation)"
        @update:error="errors.salutation = $event"
        :options="salutations"
      />
    </form-group>
    <form-group v-if="hasCompany">
      <form-label id="company" :label="__('Firma')" :required="requiresCompany" />
      <form-text-field 
        v-model="form.company" 
        :error="__(errors.company)"
        @update:error="errors.company = $event"
      />
    </form-group>
    <form-group v-if="hasFirstname">
      <form-label id="firstname" :label="__('Vorname')" :required="requiresFirstname" />
      <form-text-field 
        v-model="form.firstname" 
        :error="__(errors.firstname)"
        @update:error="errors.firstname = $event"
      />
    </form-group>
    <form-group v-if="hasName">
      <form-label id="name" :label="__('Name')" :required="requiresName" />
      <form-text-field 
        v-model="form.name" 
        :error="__(errors.name)"
        @update:error="errors.name = $event"
      />
    </form-group>
    <form-group v-if="hasEmail">
      <form-label id="email" :label="__('E-Mail')" :required="requiresEmail" />
      <form-text-field 
        type="email"
        v-model="form.email" 
        :error="__(errors.email)"
        @update:error="errors.email = $event"
      />
    </form-group>
    <form-group v-if="hasPhone">
      <form-label id="phone" :label="__('Telefon')" :required="requiresPhone" />
      <form-text-field 
        v-model="form.phone" 
        :error="__(errors.phone)"
        @update:error="errors.phone = $event"
      />
    </form-group>
    <form-group v-if="hasAddress">
      <form-label id="address" :label="__('Strasse, Nr.')" :required="requiresAddress" />
      <form-text-field 
        v-model="form.address" 
        :error="__(errors.address)"
        @update:error="errors.address = $event"
      />
    </form-group>
    <form-group v-if="hasLocation">
      <form-label id="zip" :label="__('PLZ')" :required="requiresLocation" />
      <form-text-field 
        v-model="form.zip" 
        :error="__(errors.zip)"
        @update:error="errors.location = $event"
      />
    </form-group>
    <form-group v-if="hasLocation">
      <form-label id="city" :label="__('Ort')" :required="requiresLocation" />
      <form-text-field 
        v-model="form.city" 
        :error="__(errors.city)"
        @update:error="errors.location = $event"
      />
    </form-group>
    <form-group v-if="hasCostCenter">
      <form-label id="cost_center" :label="__('Kostenstelle')" :required="requiresCostCenter" />
      <form-textarea-field 
        v-model="form.cost_center" 
        :error="__(errors.cost_center)"
        @update:error="errors.cost_center = $event"
      />
    </form-group>
    <form-group v-if="hasParty">
      <form-label id="party" :label="__('Partei/Verband/Organisation')" :required="requiresParty" />
      <form-textarea-field 
        v-model="form.party" 
        :error="__(errors.party)"
        @update:error="errors.party = $event"
      />
    </form-group>
    <form-group v-if="hasAffiliation">
      <form-label id="affiliation" :label="__('Zugehörigkeit')" :required="requiresAffiliation" />
      <form-select-field 
        v-model="form.affiliation" 
        :error="__(errors.affiliation)"
        @update:error="errors.affiliation = $event"
        :options="affiliations"
      />
    </form-group>
    <form-group v-if="hasLanguage">
      <form-label id="language" :label="__('Sprache')" :required="requiresLanguage" />
      <form-select-field 
        v-model="form.language" 
        :error="__(errors.language)"
        @update:error="errors.language = $event"
        :options="languages"
      />
    </form-group>
    <template v-if="hasMealOptions">
      <form-group>
        <label>{{ __('Essen/Apéro') }}</label>
        <div class="flex gap-x-20 lg:gap-x-30 items-center mt-3 lg:mt-10 relative">
          <form-radio-field 
            v-model="form.wants_meal_options" 
            :id="'wants_meal_options_yes'"
            :name="'wants_meal_options'"
            :error="__(errors.wants_meal_options)"
            @update:error="errors.wants_meal_options = $event"
            @update:modelValue="form.wants_meal_options = $event"
            :value="'true'"
            :label="__('Ja')"
          />
          <form-radio-field 
            v-model="form.wants_meal_options" 
            :id="'wants_meal_options_no'"
            :name="'wants_meal_options'"
            :error="__(errors.wants_meal_options)"
            @update:error="errors.wants_meal_options = $event"
            @update:modelValue="form.wants_meal_options = $event"
            :value="'false'"
            :label="__('Nein')"
          />
          <Error :error="__(errors.wants_meal_options)" />
        </div>
      </form-group>
      <form-group v-if="form.wants_meal_options == 'true'">
        <form-label id="meal_options" :label="__('Menüwunsch')" :required="requiresMealOptions" />
        <form-select-field 
          v-model="form.meal_options" 
          :error="__(errors.meal_options)"
          @update:error="errors.meal_options = $event"
          :options="mealOptions"
          />
      </form-group>
    </template>

    <template v-if="hasButtonAdditionalIndividuals">
      <div>
        <h3 class="mt-15 xl:mt-30 mb:5 xl:mb-10">{{ __('Weitere Person') }}</h3>
        <form-group v-for="(individual, index) in additionalIndividuals" :key="index">
          <AdditionalIndividual
            :hasSalutation="hasFieldAdditionalIndividualSalutation"
            :requiresSalutation="true"
            :hasEmail="hasFieldAdditionalIndividualEmail"
            :requiresEmail="requiresEmail"
            :hasName="hasFieldAdditionalIndividualName"
            :requiresName="requiresName"
            :hasFirstname="hasFieldAdditionalIndividualFirstname"
            :requiresFirstname="requiresFirstname"
            :hasMealOptions="hasMealOptions"
            :requiresMealOptions="requiresMealOptions"
            :hasCostCenter="hasFieldAdditionalIndividualCostCenter"
            :requiresCostCenter="requiresCostCenter"
            :salutations="salutations"
            :mealOptions="mealOptions"
            :errors="errors.additional_individuals ? errors.additional_individuals[index] : {}"
            @update:individual="updateAdditionalIndividual(index, $event)"
          />
          <div class="flex justify-end">
            <a 
              href="javascript:;" 
              @click.prevent="removeAdditionalIndividual(index)" 
              class="mt-5 mb-10 xl:mt-10 xl:mb-20 inline-block text-xxs xl:text-xs">
              {{ __('Person Entfernen') }}
            </a>
          </div>
        </form-group>
        <form-group>
          <a 
            href="javascript:;"
            @click.prevent="addAdditionalIndividual" 
            class="inline-block"
          >
            {{ __('Weitere Person hinzufügen') }}
          </a>
        </form-group>
      </div>
    </template>

    <form-group v-if="hasRemarks">
      <form-label id="remarks" :label="__('Bemerkungen')" />
      <form-textarea-field 
        v-model="form.remarks" 
        :error="__(errors.remarks)"
        @update:error="errors.remarks = $event"
      />
    </form-group>
    
    <form-group>
      <form-toc 
        id="toc" 
        label="Datenschutzerklärung" 
        v-model="form.toc"
        :withoutCharge="withoutCharge"
        :error="errors.toc" 
        @update:error="errors.toc = $event"
      />
    </form-group>
    <form-group classes="!mt-30 lg:!mt-60 mx-auto flex justify-center">
      <form-button 
        type="submit" 
        :label="__('Anmelden')"
        :disabled="isSubmitting"
        :submitting="isSubmitting"
      />
    </form-group>
  </form>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useI18n } from '@/composables/i18n';
import FormGroup from '@/forms/components/fields/group.vue';
import FormTextField from '@/forms/components/fields/text.vue';
import FormTextareaField from '@/forms/components/fields/textarea.vue';
import FormLabel from '@/forms/components/fields/label.vue';
import FormButton from '@/forms/components/fields/button.vue';
import FormSelectField from '@/forms/components/fields/select.vue';
import FormRadioField from '@/forms/components/fields/radio.vue';
import FormToc from '@/forms/components/fields/toc.vue';
import Error from '@/forms/components/fields/error.vue';
import AdditionalIndividual from '@/forms/components/AdditionalIndividual.vue';
import SuccessAlert from '@/forms/components/alerts/success.vue';
import ErrorAlert from '@/forms/components/alerts/error.vue';

const props = defineProps({
  eventId: {
    type: String,
    required: true,
  },
});

const { __ } = useI18n();

const isLoaded = ref(false);
const isSubmitting = ref(false);
const formSuccess = ref(false);
const formError = ref(false);
const withoutCharge = ref(false);
const hasSalutation = ref(false);
const requiresSalutation = ref(false);
const hasName = ref(false);
const requiresName = ref(false);
const hasFirstname = ref(false);
const requiresFirstname = ref(false);
const hasEmail = ref(false);
const requiresEmail = ref(false);
const hasPhone = ref(false);
const requiresPhone = ref(false);
const hasCompany = ref(false);
const requiresCompany = ref(false);
const hasLocation = ref(false);
const requiresLocation = ref(false);
const hasAddress = ref(false);
const requiresAddress = ref(false);
const hasMealOptions = ref(false);
const wantsMealOptions = ref(false);
const hasCostCenter = ref(false);
const requiresCostCenter = ref(false);
const hasParty = ref(false);
const requiresParty = ref(false);
const hasAffiliation = ref(false);
const requiresAffiliation = ref(false);
const hasLanguage = ref(false);
const requiresLanguage = ref(false);
const hasRemarks = ref(false);
const requiresMealOptions = ref(false);
const mealOptions = ref([]);
const hasButtonAdditionalIndividuals = ref(false);
const additionalIndividuals = ref([]);
const hasFieldAdditionalIndividualSalutation = ref(false);
const hasFieldAdditionalIndividualEmail = ref(false);
const hasFieldAdditionalIndividualName = ref(false);
const hasFieldAdditionalIndividualFirstname = ref(false);
const hasFieldAdditionalIndividualCostCenter = ref(false);

const locale = ref(document.documentElement.lang);

const salutations = ref([
  { label: 'Frau', value: 'Frau' },
  { label: 'Herr', value: 'Herr' },
  { label: 'Divers', value: 'Divers' },
]);

const languages = ref([
  { label: 'Deutsch', value: 'Deutsch' },
  { label: 'Französisch', value: 'Französisch' },
]);

const affiliations = ref([
  { label: 'Mitglied Baustoff Kreislauf Schweiz', value: 'Mitglied Baustoff Kreislauf Schweiz' },
  { label: 'Liste OdA Abfall- und Rohstoffwirtschaft / Behörde', value: 'Liste OdA Abfall- und Rohstoffwirtschaft / Behörde' },
  { label: 'Andere', value: 'Andere' }
]);

const form = ref({
  event_id: props.eventId,
  salutation: null,
  name: null  ,
  firstname: null,
  email: null,
  phone: null,
  company: null,
  location: null,
  zip: null,
  city: null,
  address: null,
  remarks: null,
  cost_center: null,
  party: null,
  affiliation: null,
  language: null,
  wants_meal_options: null,
  meal_options: null,
  additional_individuals: [],
});

const errors = ref({
    salutation: '',
    name: '',
    firstname: '',
    email: '',
    phone: '',
    company: '',
    location: '',
    address: '',
    meal_options: '',
    additional_individuals: [],
  }
);

onMounted(async () => {
  try {
    const response = await axios.get(`/api/event/${props.eventId}`);
    isLoaded.value = true;
    withoutCharge.value = response.data.without_charge;
    hasSalutation.value = response.data.has_salutation;
    requiresSalutation.value = response.data.requires_salutation;
    hasName.value = response.data.has_name;
    requiresName.value = response.data.requires_name;
    hasFirstname.value = response.data.has_firstname;
    requiresFirstname.value = response.data.requires_firstname;
    hasEmail.value = response.data.has_email;
    requiresEmail.value = response.data.requires_email;
    hasPhone.value = response.data.has_phone;
    requiresPhone.value = response.data.requires_phone;
    hasCompany.value = response.data.has_company;
    requiresCompany.value = response.data.requires_company;
    hasLocation.value = response.data.has_location;
    requiresLocation.value = response.data.requires_location;
    hasAddress.value = response.data.has_address;
    hasCostCenter.value = response.data.has_cost_center;
    requiresCostCenter.value = response.data.requires_cost_center;
    hasParty.value = response.data.has_party;
    requiresParty.value = response.data.requires_party;
    hasAffiliation.value = response.data.has_affiliation;
    requiresAffiliation.value = response.data.requires_affiliation;
    hasLanguage.value = response.data.has_language;
    requiresLanguage.value = response.data.requires_language;
    hasRemarks.value = response.data.has_remarks;
    requiresAddress.value = response.data.requires_address;
    hasMealOptions.value = response.data.has_meal_options;

    if (hasMealOptions.value) {   
      
      if (response.data.meal_options) {
       Object.entries(response.data.meal_options).forEach(([key, value]) => {
         if (value) {
          // the key used in value needs to be translated
           mealOptions.value.push({ label: __(key), value: key });
         }
       });
       form.value.meal_options = mealOptions.value[0].value;
      }
    }

    if (hasSalutation.value) {
      form.value.salutation = salutations.value[0].value;
    }
    if (hasLanguage.value) {
      form.value.language = languages.value[0].value;
    }
    if (hasAffiliation.value) {
      form.value.affiliation = affiliations.value[0].value;
    }
    hasButtonAdditionalIndividuals.value = response.data.has_button_additional_individuals;
    hasFieldAdditionalIndividualSalutation.value = response.data.has_field_additional_individual_salutation;
    hasFieldAdditionalIndividualEmail.value = response.data.has_field_additional_individual_email;
    hasFieldAdditionalIndividualName.value = response.data.has_field_additional_individual_name;
    hasFieldAdditionalIndividualFirstname.value = response.data.has_field_additional_individual_firstname;
    hasFieldAdditionalIndividualCostCenter.value = response.data.has_field_additional_individual_cost_center;

  } catch (error) {
    console.error(error);
  }
});

async function submitForm() {
  isSubmitting.value = true;
  formSuccess.value = false;
  formError.value = false;
  try {
    const response = await axios.post('/api/event/register', {
      ...form.value,
      locale: locale.value,
    });
    handleSuccess();
  } catch (error) {
    handleError(error);
  }
}

function updateAdditionalIndividual(index, updatedIndividual) {
  additionalIndividuals.value[index] = updatedIndividual;
  form.value.additional_individuals = additionalIndividuals.value;
}

watch(additionalIndividuals, (newValue) => {
  form.value.additional_individuals = newValue;
}, { deep: true });

function addAdditionalIndividual() {
  additionalIndividuals.value.push({
    salutation: hasSalutation.value ? salutations.value[0].value : null,
    email: null,
    name: null,
    firstname: null,
    wants_meal_options: null,
    meal_options: null,
  });
  form.value.additional_individuals = additionalIndividuals.value;
}

function removeAdditionalIndividual(index) {
  additionalIndividuals.value.splice(index, 1);
  form.value.additional_individuals = additionalIndividuals.value;
}

function handleSuccess() {
  form.value = {
    event_id: props.eventId,
    salutation: null,
    name: null,
    firstname: null,
    email: null,
    phone: null,
    company: null,
    location: null,
    zip: null,
    city: null,
    address: null,
    meal_options: null,
    party: null,
    affiliation: null,
    cost_center: null,
    language: null,
    additional_individuals: [],
  };

  if (hasSalutation.value) {
    form.value.salutation = salutations.value[0].value;
  }

  if (hasLanguage.value) {
    form.value.language = languages.value[0].value;
  }

  if (hasAffiliation.value) {
    form.value.affiliation = affiliations.value[0].value;
  }

  // reset additional_individuals
  additionalIndividuals.value = [];
  form.value.additional_individuals = additionalIndividuals.value;
  
  errors.value = {
    name: '',
    firstname: '',
    email: '',
    phone: '',
    company: '',
    location: '',
    address: '',
    meal_options: '',
    additional_individuals: [],
  };
  
  isSubmitting.value = false;
  formSuccess.value = true;
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

function handleError(error) {
  isSubmitting.value = false;
  formError.value = true;

  if (error.response && error.response.data && typeof error.response.data.errors === 'object') {
    Object.keys(error.response.data.errors).forEach(key => {
      errors.value[key] = error.response.data.errors[key];
    });

    // handle additional_individuals errors
    if (error.response.data.errors.additional_individuals) {
      error.response.data.errors.additional_individuals.forEach(individualError => {
        Object.keys(individualError).forEach(key => {
          errors.value.additional_individuals[key] = individualError[key];
        });
      });
    }

    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }
}
</script>