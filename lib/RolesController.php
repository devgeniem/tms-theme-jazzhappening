<?php
namespace TMS\Theme\Jazzhappening;

use Geniem\Role;
use TMS\Theme\Base\Interfaces\Controller;

/**
 * RolesController
 */
class RolesController implements Controller {

    /**
     * Artist / artist-cpt.
     *
     * @var string[]
     */
    private $artists_all_capabilities = [
        'delete_artist',
        'delete_artists',
        'delete_others_artists',
        'delete_private_artists',
        'delete_published_artists',
        'edit_artist',
        'edit_artists',
        'edit_others_artists',
        'edit_private_artists',
        'edit_published_artists',
        'publish_artists',
        'read',
        'read_artist',
        'read_private_artists',
    ];

    /**
     * Festival / festival-cpt.
     *
     * @var string[]
     */
    private $festivals_all_capabilities = [
        'delete_festival',
        'delete_festivals',
        'delete_others_festivals',
        'delete_private_festivals',
        'delete_published_festivals',
        'edit_festival',
        'edit_festivals',
        'edit_others_festivals',
        'edit_private_festivals',
        'edit_published_festivals',
        'publish_festivals',
        'read',
        'read_festival',
        'read_private_festivals',
    ];

    /**
     * Artist Category taxonomy
     *
     * @var string[]
     */
    private $taxonomy_artist_category_all_capabilities = [
        'manage_artist_categories',
        'edit_artist_categories',
        'delete_artist_categories',
        'assign_artist_categories',
    ];

    /**
     * Festival Category taxonomy
     *
     * @var string[]
     */
    private $taxonomy_festival_category_all_capabilities = [
        'manage_festival_categories',
        'edit_festival_categories',
        'delete_festival_categories',
        'assign_festival_categories',
    ];

    /**
     * Hooks
     */
    public function hooks(): void {
        \add_filter( 'tms/roles/super_administrator', [ $this, 'modify_super_administrator_caps' ] );
        \add_filter( 'tms/roles/admin', [ $this, 'modify_admin_caps' ] );
        \add_filter( 'tms/roles/editor', [ $this, 'modify_editor_caps' ] );
        \add_filter( 'tms/roles/author', [ $this, 'modify_author_caps' ] );
        \add_filter( 'tms/roles/contributor', [ $this, 'modify_contributor_caps' ] );
    }

    /**
     * Modify Super Administrator caps
     *
     * @param Role $role Instance of \Geniem\Role.
     */
    public function modify_super_administrator_caps( Role $role ) {
        $role->add_caps( $this->artists_all_capabilities );
        $role->add_caps( $this->festivals_all_capabilities );
        $role->add_caps( $this->taxonomy_artist_category_all_capabilities );
        $role->add_caps( $this->taxonomy_festival_category_all_capabilities );

        return $role;
    }

    /**
     * Modify Administrator caps
     *
     * @param Role $role Instance of \Geniem\Role.
     */
    public function modify_admin_caps( Role $role ) {
        $role->add_caps( $this->artists_all_capabilities );
        $role->add_caps( $this->festivals_all_capabilities );
        $role->add_caps( $this->taxonomy_artist_category_all_capabilities );
        $role->add_caps( $this->taxonomy_festival_category_all_capabilities );

        return $role;
    }

    /**
     * Modify Editor caps
     *
     * @param Role $role Instance of \Geniem\Role.
     */
    public function modify_editor_caps( Role $role ) {
        $role->add_caps( $this->artists_all_capabilities );
        $role->add_caps( $this->festivals_all_capabilities );
        $role->add_caps( $this->taxonomy_artist_category_all_capabilities );
        $role->add_caps( $this->taxonomy_festival_category_all_capabilities );

        return $role;
    }

    /**
     * Modify Author caps
     *
     * @param Role $role Instance of \Geniem\Role.
     */
    public function modify_author_caps( Role $role ) {
        $role->add_caps( $this->artists_all_capabilities );
        $role->add_caps( $this->festivals_all_capabilities );

        $role->add_caps( [
            'assign_festival_categories',
            'assign_artist_categories',
        ] );

        return $role;
    }

    /**
     * Modify Contributor caps
     *
     * @param Role $role Instance of \Geniem\Role.
     */
    public function modify_contributor_caps( Role $role ) {
        $role->add_caps( [
            'delete_artist',
            'delete_artists',
            'delete_private_artists',
            'delete_published_artists',
            'edit_artist',
            'edit_artists',
            'edit_private_artists',
            'edit_published_artists',
            'read',
            'read_artist',
            'read_private_artists',
        ] );

        $role->add_caps( [
            'delete_festival',
            'delete_festivals',
            'delete_private_festivals',
            'delete_published_festivals',
            'edit_festival',
            'edit_festivals',
            'edit_private_festivals',
            'edit_published_festivals',
            'read',
            'read_festival',
            'read_private_festivals',
        ] );

        $role->add_caps( [
            'assign_festival_categories',
            'assign_artist_categories',
        ] );

        return $role;
    }
}
