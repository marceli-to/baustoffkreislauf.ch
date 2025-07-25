<template>
  <template v-if="isLoaded">
    <template v-if="formSuccess">
      <success-alert>
        Vielen Dank für Ihre Anmeldung!<br>
        Merci pour votre inscription !<br>
        Grazie per la tua iscrizione!
      </success-alert>
    </template>
    <template v-if="formError">
      <error-alert>
        Bitte überprüfen Sie die eingegebenen Daten.<br>
        Veuillez vérifier les données saisies.<br>
        Controlla i tuoi dati.
      </error-alert>
    </template>

    <form @submit.prevent="submitForm" class="space-y-15 lg:space-y-30 max-w-2xl">
      <form-group v-if="hasSalutation">
        <form-label id="salutation" label="Anrede / Titre / Appellativo" :required="requiresSalutation" />
        <form-select-field 
          v-model="form.salutation" 
          :error="__(errors.salutation)"
          @update:error="errors.salutation = $event"
          :options="salutations"
        />
      </form-group>
      <form-group v-if="hasCompany">
        <form-label id="company" label="Firma / Société / Azienda" :required="requiresCompany" />
        <form-text-field 
          v-model="form.company" 
          :error="__(errors.company)"
          @update:error="errors.company = $event"
        />
      </form-group>
      <form-group v-if="hasFirstname">
        <form-label id="firstname" label="Vorname / Prénom / Prenome" :required="requiresFirstname" />
        <form-text-field 
          v-model="form.firstname" 
          :error="__(errors.firstname)"
          @update:error="errors.firstname = $event"
        />
      </form-group>
      <form-group v-if="hasName">
        <form-label id="name" label="Name / Nom / Nome" :required="requiresName" />
        <form-text-field 
          v-model="form.name" 
          :error="__(errors.name)"
          @update:error="errors.name = $event"
        />
      </form-group>
      <form-group v-if="hasEmail">
        <form-label id="email" label="E-Mail / E-mail / Email" :required="requiresEmail" />
        <form-text-field 
          type="email"
          v-model="form.email" 
          :error="__(errors.email)"
          @update:error="errors.email = $event"
        />
      </form-group>
      <form-group v-if="hasPhone">
        <form-label id="phone" label="Telefon / Téléphone / Telefono" :required="requiresPhone" />
        <form-text-field 
          v-model="form.phone" 
          :error="__(errors.phone)"
          @update:error="errors.phone = $event"
        />
      </form-group>
      <form-group v-if="hasAddress">
        <form-label id="address" label="Strasse, Nr. / Rue, n° / Via, n." :required="requiresAddress" />
        <form-text-field 
          v-model="form.address" 
          :error="__(errors.address)"
          @update:error="errors.address = $event"
        />
      </form-group>
      <form-group v-if="hasLocation">
        <form-label id="zip" label="PLZ / NPA / CAP" :required="requiresLocation" />
        <form-text-field 
          v-model="form.zip" 
          :error="__(errors.zip)"
          @update:error="errors.location = $event"
        />
      </form-group>
      <form-group v-if="hasLocation">
        <form-label id="city" label="Ort / Localité / Località" :required="requiresLocation" />
        <form-text-field 
          v-model="form.city" 
          :error="__(errors.city)"
          @update:error="errors.location = $event"
        />
      </form-group>
      <form-group v-if="hasCostCenter">
        <form-label id="cost_center" label="Kostenstelle / Centre de coût / Centro di costo" :required="requiresCostCenter" />
        <form-textarea-field 
          v-model="form.cost_center" 
          :error="__(errors.cost_center)"
          @update:error="errors.cost_center = $event"
        />
      </form-group>

      <form-group v-if="hasRemarks">
        <form-label id="remarks" label="Bemerkungen / Remarques / Note" />
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
          :multiLanguage="true"
          v-model="form.toc"
          :error="errors.toc" 
          @update:error="errors.toc = $event"
        />
      </form-group>
      <form-group classes="!mt-30 lg:!mt-60 mx-auto flex justify-center">
        <form-button 
          type="submit" 
          label="Anmelden / S'inscrire / Registro"
          :disabled="isSubmitting"
          :submitting="isSubmitting"
        />
      </form-group>
    </form>
  </template>
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
  courseId: {
    type: String,
    required: true,
  },
});

const { __ } = useI18n();

const isLoaded = ref(false);
const isSubmitting = ref(false);
const formSuccess = ref(false);
const formError = ref(false);
const title = ref('');
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
const hasCostCenter = ref(false);
const requiresCostCenter = ref(false);
const hasRemarks = ref(false);

const locale = ref(document.documentElement.lang);

const salutations = ref([
  { label: 'Frau/Madame/Signora', value: 'Frau' },
  { label: 'Herr/Monsieur/Signore', value: 'Herr' },
  { label: 'Divers/Autre/Altro', value: 'Divers' },
]);

const form = ref({
  course_id: props.courseId,
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
  remarks: null,
  cost_center: null,
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
  }
);

onMounted(async () => {
  try {
    const response = await axios.get(`/api/course/${props.courseId}/${locale.value}`);
    isLoaded.value = true;
    title.value = response.data.title;
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
    hasRemarks.value = response.data.has_remarks;
    requiresAddress.value = response.data.requires_address;

    if (hasSalutation.value) {
      form.value.salutation = salutations.value[0].value;
    }

  } catch (error) {
    console.error(error);
  }
});

async function submitForm() {
  isSubmitting.value = true;
  formSuccess.value = false;
  formError.value = false;
  try {
    const response = await axios.post('/api/course/register', {
      ...form.value,
      locale: locale.value,
    });
    handleSuccess();
  } catch (error) {
    handleError(error);
  }
}

function handleSuccess() {
  form.value = {
    course_id: props.courseId,
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
  };

  if (hasSalutation.value) {
    form.value.salutation = salutations.value[0].value;
  }
  
  errors.value = {
    name: '',
    firstname: '',
    email: '',
    phone: '',
    company: '',
    location: '',
    address: '',
  };
  
  isSubmitting.value = false;
  formSuccess.value = true;
  window.scrollTo({
    top: 250,
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
    window.scrollTo({
      top: 250,
      behavior: 'smooth'
    });
  }
}
</script>