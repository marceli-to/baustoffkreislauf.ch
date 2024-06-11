import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const SwiperUi = (function() {

  let swiperInstance;
  
  const selectors = {
    body: 'body',
    swiper: '.js-swiper'
  };

  const swiperOptions = {
    modules: [Navigation, Pagination],
    direction: 'horizontal',
    slidesPerView: "1",
    centeredSlides: true,
    spaceBetween: "16",
    pagination: {
      el: ".swiper-pagination",
      clickable: true
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  };

  const initialize = function() {
    swiperInstance = new Swiper(
      selectors.swiper, 
      swiperOptions
    );
  };

  return {
    init: initialize,
  };
})();

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  const swiperWrapper = document.querySelector('.js-swiper');
  if (swiperWrapper) {
    SwiperUi.init();
  }
});
