// Disable most embed block variations.
wp.domReady( () => {
    const allowedEmbedVariations = [
        'spotify',
    ];

    wp.blocks.getBlockVariations( 'core/embed' ).forEach( ( variation ) => {
        if ( allowedEmbedVariations.indexOf( variation.name ) === -1 ) {
            wp.blocks.unregisterBlockVariation( 'core/embed', variation.name );
        }
    } );
} );
