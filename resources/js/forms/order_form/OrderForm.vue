<template>
  <template v-if="formSuccess">
    <success-alert>
      {{ __('Vielen Dank für Ihre Bestellung!') }}
    </success-alert>
  </template>
  <template v-if="formError">
    <error-alert>
      {{ __('Bitte überprüfen Sie die eingegebenen Daten.') }}
    </error-alert>
  </template>
  <form @submit.prevent="submitForm" v-if="isLoaded" class="space-y-15 lg:space-y-30 max-w-2xl">

    <div class="border border-lime bg-lime/10 p-10 lg:p-20">
      <h2>{{__('Artikel') }}: {{ publication.title }}</h2>
      <p>{{ publication.cost }}</p>
    </div>
    <form-group>
      <form-label id="quantity" :label="__('Anzahl')" :required="true" />
      <form-number-field 
        v-model.number="form.quantity"
        :error="__(errors.quantity)"
        @update:error="errors.quantity = $event"
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
    <form-group>
      <form-label id="company" :label="__('Firma')" />
      <form-text-field 
        v-model="form.company" 
        :error="__(errors.company)"
        @update:error="errors.company = $event"
      />
    </form-group>
    <form-group>
      <form-label id="invoice_address" :label="__('Rechnungsadresse')" :required="true" />
      <form-textarea-field 
        v-model="form.invoice_address" 
        :error="__(errors.invoice_address)"
        classes="min-h-125"
        @update:error="errors.invoice_address = $event"
      />
    </form-group>
    <form-group>
      <form-label id="delivery_address" :label="__('Lieferadresse (falls abweichend von Rechnungsadresse)')" />
      <form-textarea-field 
        v-model="form.delivery_address" 
        :error="__(errors.delivery_address)"
        classes="min-h-125"
        @update:error="errors.delivery_address = $event"
      />
    </form-group>
    <form-group>
      <form-label id="remarks" :label="__('Bemerkungen')" />
      <form-textarea-field 
        v-model="form.remarks" 
        :error="__(errors.remarks)"
        classes="min-h-125"
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
import FormNumberField from '@/forms/components/fields/number.vue';
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
const locale = ref(document.documentElement.lang);

const form = ref({
  publication_id: props.publicationId,
  quantity: 1,
  name: null,
  firstname: null,
  email: null,
  phone: null,
  company: null,
  invoice_address: null,
  delivery_address: null,
  remarks: null,
  locale: locale.value,
});

const errors = ref({
    quantity: '',
    name: '',
    firstname: '',
    email: '',
    phone: '',
    invoice_address: '',
  }
);

watch(() => form.value.quantity, (newVal) => {
  if (newVal !== null && newVal < 1) {
    form.value.quantity = 1;
  }
});

onMounted(async () => {
  try {
    const response = await axios.get(`/api/publication/${props.publicationId}`);
    publication.value = response.data.publication;
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
    const response = await axios.post('/api/publication/order', {
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
    invoice_address: null,
    delivery_address: null,
    remarks: null,
  };

  errors.value = {
    name: '',
    firstname: '',
    email: '',
    phone: '',
    location: '',
    invoice_address: '',
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