<template>
  <div class="relative !mt-20 lg:!mt-30">
    <div :class="['flex checkboxes', { 'has-error': error, 'items-center': withoutCharge }]">
      <div class="flex items-start mr-15 mt-2 lg:mt-4">
        <input id="toc" name="toc" type="checkbox" :checked="modelValue" @change="updateValue">
      </div>
      <template v-if="withoutCharge">
        <label for="toc" class="hyphens-auto" v-if="locale === 'de'">
          Mit der Anmeldung bestätigen Sie die <a href="/datenschutz" target="_blank">Datenschutzbestimmungen</a>
        </label>
        <label for="toc" class="hyphens-auto" v-else-if="locale === 'fr'">
          En vous inscrivant, vous confirmez la <a href="/fr/protection-des-donnees" target="_blank">politique de confidentialité</a>
        </label>
        <label for="toc" class="hyphens-auto" v-else-if="locale === 'it'">
          Con l'iscrizione, confermi la <a href="/it/protezione-dati" target="_blank">politica sulla privacy</a>
        </label>
      </template>
      <template v-else>
        <label for="toc" class="hyphens-auto" v-if="locale === 'de'">
          Mit der Anmeldung bestätigen Sie die entsprechenden <a href="/annullationsbedingungen" target="_blank">Teilnahme- und Annullationsbedingungen</a> sowie <a href="/datenschutz" target="_blank">Datenschutzbestimmungen</a>
        </label>
        <label for="toc" class="hyphens-auto" v-else-if="locale === 'fr'">
          En vous inscrivant, vous confirmez les <a href="/annullationsbedingungen" target="_blank">conditions de participation et d'annulation correspondantes</a> ainsi que la <a href="/fr/protection-des-donnees" target="_blank">politique de confidentialité</a>
        </label>
        <label for="toc" class="hyphens-auto" v-else-if="locale === 'it'">
          Con l'iscrizione, confermi le <a href="/annullationsbedingungen" target="_blank">condizioni di partecipazione e di annullamento corrispondenti</a> e la <a href="/it/protezione-dati" target="_blank">politica sulla privacy</a>
        </label>
      </template>
    </div>
  </div>
</template>
<script setup>
const locale = document.documentElement.lang;

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  error: {
    type: String,
    default: null
  },
  required: {
    type: Boolean,
    default: false
  },
  modelValue: {
    type: Boolean,
    default: false
  },
  withoutCharge: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'update:error']);
const updateValue = (event) => {
  emit('update:modelValue', event.target.checked);
  emit('update:error', null);
}
</script>