import Splide from '@splidejs/splide';

// Use jQuery as $ within this file scope.
const $ = jQuery;

export default class HeroCarousel {
    cache() {
        this.carousels = $( '.hero__carousel' );
    }

    /**
     * Init sliders
     *
     * @return {void}
     */
    init() {
        if ( ! this.carousels ) {
            return;
        }

        const translations = window.s.gallery || {
            next: 'Next',
            previous: 'Previous',
        };

        Splide.defaults = {
            type: 'loop',
            arrows: false,
            pagination: false,
            autoplay: true,
            speed: 600,
            i18n: {
                prev: translations.previous,
                next: translations.next,
            },
            role: 'group',
        };

        new Splide( '.hero__carousel' ).mount();
    }

    /**
     * Run when the document is ready.
     *
     * @return {void}
     */
    docReady() {
        this.cache();
        this.init();
    }
}
