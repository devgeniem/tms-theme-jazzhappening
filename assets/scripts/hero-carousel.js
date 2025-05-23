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

        Splide.defaults = {
            type: 'fade',
            rewind: true,
            arrows: false,
            pagination: false,
            autoplay: true,
            speed: 600,
            pauseOnHover: false,
            role: 'group',
        };

        for ( let i = 0; i < this.carousels.length; i++ ) {
            new Splide( this.carousels[ i ] ).mount();
        }
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
