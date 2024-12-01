<?php
/**
 * Define the internationalization functionality
 */

namespace HeroHub\CRM;

class HeroHub_CRM_i18n {
    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'herohub-crm',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
