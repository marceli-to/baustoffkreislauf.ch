import { ref, onMounted } from 'vue';
import axios from 'axios';

export function useI18n() {
  const translations = ref({});
  const hasTranslations = ref(false);
  const fallback_locale = 'de';
  const current_locale = ref();
  const routes = {
    get: '/api/translations',
  };

  const __ = (key) => {
    if (translations.value[key]) {
      return translations.value[key];
    }
    return key;
  };

  const _getLocale = () => {
    return current_locale.value;
  };

  const _setLocale = () => {
    current_locale.value = document.documentElement.lang || fallback_locale;
  };

  const _getTranslations = () => {
    const locale = _getLocale();
    if (locale && locale !== fallback_locale) {
      const storedTranslations = localStorage.getItem(`bks_${locale}`);
      const expirationTime = localStorage.getItem(`bks_${locale}_expiration`); 
      if (storedTranslations && expirationTime && new Date(expirationTime) > new Date()) {
        translations.value = JSON.parse(storedTranslations);
        hasTranslations.value = true;
      } else {
        axios.get(`${routes.get}/${locale}`).then(response => {
          translations.value = JSON.parse(response.data);
          hasTranslations.value = true;
          const expirationTime = new Date();
          expirationTime.setHours(expirationTime.getHours() + 2);
          localStorage.removeItem(`bks_${locale}`);
          localStorage.removeItem(`bks_${locale}_expiration`);
          localStorage.setItem(`bks_${locale}_expiration`, expirationTime.toISOString());
          localStorage.setItem(`bks_${locale}`, JSON.stringify(translations.value));
        });
      }
    }
  };

  onMounted(() => {
    _setLocale();
    _getTranslations();
  });

  return {
    __,
    translations,
    hasTranslations,
    fallback_locale,
    current_locale,
  };
}