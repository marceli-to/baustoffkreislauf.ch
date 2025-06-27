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

    <div class="border border-lime bg-lime/10 p-10 lg:p-20">
      <h2>{{ publication.title }}</h2>
      <p>{{ publication.cost }}</p>
    </div>

    <form-group>
      <form-label id="company" :label="__('Firma')" :required="true" />
      <form-text-field 
        v-model="form.company" 
        :error="__(errors.company)"
        @update:error="errors.company = $event"
      />
    </form-group>
    <form-group>
      <form-label id="firstname" :label="__('Vorname')" :required="true" />
      <form-text-field 
        v-model="form.firstname" 
        :error="__(errors.firstname)"
        @update:error="errors.firstname = $event"
      />
    </form-group>
    <form-group>
      <form-label id="name" :label="__('Name')" :required="true" />
      <form-text-field 
        v-model="form.name" 
        :error="__(errors.name)"
        @update:error="errors.name = $event"
      />
    </form-group>
    <form-group>
      <form-label id="email" :label="__('E-Mail')" :required="true" />
      <form-text-field 
        type="email"
        v-model="form.email" 
        :error="__(errors.email)"
        @update:error="errors.email = $event"
      />
    </form-group>
    <form-group>
      <form-label id="phone" :label="__('Telefon')" :required="true" />
      <form-text-field 
        v-model="form.phone" 
        :error="__(errors.phone)"
        @update:error="errors.phone = $event"
      />
    </form-group>
    <form-group >
      <form-label id="address" :label="__('Strasse, Nr.')" :required="true" />
      <form-text-field 
        v-model="form.address" 
        :error="__(errors.address)"
        @update:error="errors.address = $event"
      />
    </form-group>
    <form-group>
      <form-label id="zip" :label="__('PLZ')" :required="true" />
      <form-text-field 
        v-model="form.zip" 
        :error="__(errors.zip)"
        @update:error="errors.location = $event"
      />
    </form-group>
    <form-group>
      <form-label id="city" :label="__('Ort')" :required="true" />
      <form-text-field 
        v-model="form.city" 
        :error="__(errors.city)"
        @update:error="errors.location = $event"
      />
    </form-group>

    <form-group>
      <form-label id="remarks" :label="__('Bemerkungen')" />
      <form-textarea-field 
        v-model="form.remarks" 
        :error="__(errors.remarks)"
        @update:error="errors.remarks = $event"
      />
    </form-group>
    

    <form-group classes="!mt-30 lg:!mt-60 mx-auto flex justify-center">
      <form-button 
        type="submit" 
        :label="__('Bestellen')"
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
  publicationId: {
    type: String,
    required: true,
  },
});

const { __ } = useI18n();

const isLoaded = ref(false);
const isSubmitting = ref(false);
const formSuccess = ref(false);
const formError = ref(false);

const publication = ref(null);

const form = ref({
  publication_id: props.publicationId,
  company: null,
  name: null,
  firstname: null,
  email: null,
  phone: null,
  location: null,
  zip: null,
  city: null,
  address: null,
  remarks: null,
});

const errors = ref({
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
    const response = await axios.get(`/api/publication/${props.publicationId}`);
    publication.value = response.data.publication;
    console.log(publication.value);
    isLoaded.value = true;
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
    });
    handleSuccess();
  } catch (error) {
    handleError(error);
  }
}


function handleSuccess() {
  form.value = {
    publication_id: props.eventId,
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
  };

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