<?php

/**
 * Plugin management. Handles activation, deactivation, etc.
 *
 * @since 0.7.0
 */

namespace cconover\FeaturedImageCaption;

class Manage {
    /**
     * Class constructor.
     *
     * @since 0.7.0
     */
    public function __construct() {
        // Plugin upgrades
        $this->upgrade();
    }

    /**
     * Plugin deactivation.
     *
     * @since 0.7.0
     */
    public function deactivate()
    {
        // Remove the plugin options from the database
        $result = delete_option(CCFIC_KEY.'_options');

        return $result;
    }

    /**
     * Plugin upgrade.
     *
     * @since 0.7.0
     */
    private function upgrade()
    {
        // Get the plugin options
        $options = get_option(CCFIC_KEY.'_options');

        // If the option does not exist, return
        if ( ! $options ) {
            return;
        }

        // If the options are stored as an array
        if ( is_array( $options ) ) {
            // If the database still has the legacy version entry
            if (! empty($options['dbversion'])) {
                // Set new version entry
                $options['version'] = $options['dbversion'];

                // Remove old entry
                unset($options['dbversion']);
            }

            /*
            If no version number is specified, it was likely caused by a bug
            introduced in 0.7.0, so we'll set the version to 0.7.0 to be able
            to correct the issue.
            */
            if( empty( $options['version'] ) ) {
                $options['version'] = '0.7.0';
            }

            $version = $options['version'];
        } else {
            $version = $options->version;
        }

        /*
        Check whether the database-stored plugin version number is less than
        the current plugin version number, or whether there is no plugin version
        saved in the database.
        */
        if (! empty($version) && version_compare($version, CCFIC_VERSION, '<')) {
            /* === UPGRADE ACTIONS === (oldest to latest) */

            /*
            Version 0.5.0
            */
            if (version_compare($version, '0.5.0', '<')) {
                /*
                Add an option to automatically append caption to the featured
                image. Since this is an upgrade, we assume the user is already
                using the plugin's theme function(s), so we'll set this to false
                to avoid breakage.
                */
                $options['auto_append'] = false;

                // Wrap the caption HTML in a container <div>
                $options['container'] = true;
            }

            /*
            Version 0.7.0
            */
            if (version_compare($version, '0.7.0', '<')) {
                // Convert the stored plugin options from an array to an object
                if ( is_array( $options ) ) {
                    $options_obj = new \stdClass();
                    foreach ( $options as $key => $value ) {
                        $options_obj->$key = $value;
                    }

                    $options = $options_obj;
                }
            }

            /*
            Version 0.7.2
            Fixes options broken by version 0.7.0
            */
            if (version_compare($version, '0.7.2', '<')) {
                // If the options are still stored as an array, convert to an object
                if ( is_array( $options ) ) {
                    $options_obj = new \stdClass();
                    foreach ( $options as $key => $value ) {
                        $options_obj->$key = $value;
                    }

                    $options = $options_obj;
                }

                // Add the version number
                $options->version = $version;
            }


            /* === END UPGRADE ACTIONS === */

            /* LAST STEPS ALWAYS!!! Update the plugin version saved in the database */
            // Set the value of the plugin version
            $options->version = CCFIC_VERSION;

            // Save to the database
            $result = update_option(CCFIC_KEY.'_options', $options);

            return $result;
        }
    }
}