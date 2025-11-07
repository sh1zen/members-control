<?php
/**
 * @author    sh1zen
 * @copyright Copyright (C) 2025.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace WPMembership\core;

/**
 * Creates the menu page for the plugin.
 *
 * Provides the functionality necessary for rendering the page corresponding
 * to the menu with which this page is associated.
 */
class PagesHandler
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_pages'));
        add_action('admin_enqueue_scripts', array($this, 'register_assets'), 20, 0);
    }

    public function add_plugin_pages(): void
    {
        add_menu_page(
            'Members Control',
            'Members Control',
            'customize',
            'members-control',
            array($this, 'render_main'),
            'dashicons-groups'
        );

        /**
         * Modules - sub pages
         */
        foreach (wps('wpmc')->moduleHandler->get_modules(array('scopes' => 'admin-page')) as $module) {

            add_submenu_page('members-control', 'WPMC' . $module['name'], $module['name'], 'customize', $module['slug'], array($this, 'render_module'));
        }

        /**
         * Plugin core settings
         */
        add_submenu_page('members-control', __('WPMC Settings', 'members-control'), __('Settings', 'members-control'), 'manage_options', 'wpmc-settings', array($this, 'render_core_settings'));
    }

    private function enqueue_scripts(): void
    {
        wp_enqueue_style('wpmc_css');
        wp_enqueue_script('vendor-wps-js');
    }

    public function render_core_settings(): void
    {
        $this->enqueue_scripts();

        wps('wpmc')->settings->render_core_settings();
    }

    public function render_module(): void
    {
        $module_slug = sanitize_text_field($_GET['page']);

        $object = wps('wpmc')->moduleHandler->get_module_instance($module_slug);

        if (is_null($object)) {
            return;
        }

        $this->enqueue_scripts();

        $object->render_admin_page();
    }

    public function register_assets(): void
    {
        $assets_url = PluginInit::getInstance()->plugin_base_url;

        $min = wps_core()->online ? '.min' : '';

        wp_register_style("wpmc_css", "{$assets_url}assets/style{$min}.css", ['vendor-wps-css']);

        wps_localize([
            'saved'   => __('Settings Saved', 'members-control'),
            'error'   => __('Request fail', 'members-control'),
            'success' => __('Request succeed', 'members-control'),
        ]);
    }

    /**
     * This function renders the contents of the page associated with the menu
     * that invokes the render method. In the context of this plugin, this is the
     * menu class.
     */
    public function render_main(): void
    {
        $this->enqueue_scripts();
        ?>
        <section class="wps-wrap-flex wps-wrap wps-home">
            <section class="wps">
                <block class="wps">
                    <block class="wps-header">
                        <h1>Members Control Dashboard</h1>
                    </block>
                    <h2><?php _e('Members by role:', 'members-control'); ?></h2>
                    <ul class="wps">
                        <?php
                        foreach (count_users()['avail_roles'] ?? [] as $role => $count) {
                            echo "<li class='wps'><strong>" . ucwords($role) . "</strong>: $count</li>";
                        }
                        ?>
                    </ul>
                    <h2><?php _e('Members by levels:', 'members-control'); ?></h2>
                    <ul class="wps">
                        <?php
                        foreach (wpmc_get_levels() as $level) {
                            echo "<li class='wps'><strong>" . ucwords($level->title) . "</strong>: " . $level->count() . "</li>";
                        }
                        ?>
                    </ul>
                    <h2><?php _e('Members Stats:', 'members-control'); ?></h2>
                    <block class="wps">
                        <?php echo wpmc_stats_count_members(). " / ". wpmc_stats_count_possible_members(['author']) . ' active members.'; ?>
                    </block>
                </block>
            </section>
            <aside class="wps">
                <section class="wps-box">
                    <div class="wps-donation-wrap">
                        <div class="wps-donation-title"><?php _e('Support this project, buy me a coffee.', 'members-control'); ?></div>
                        <br>
                        <a href="https://www.paypal.com/donate?business=dev.sh1zen%40outlook.it&item_name=Thank+you+in+advanced+for+the+kind+donations.+You+will+sustain+me+developing+MembersControl.&currency_code=EUR"
                           target="_blank">
                            <img src="https://www.paypalobjects.com/en_US/IT/i/btn/btn_donateCC_LG.gif"
                                 title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button"/>
                        </a>
                        <div class="wps-donation-hr"></div>
                        <div class="dn-btc">
                            <div class="wps-donation-name">BTC:</div>
                            <p class="wps-donation-value">3QE5CyfTxb5kufKxWtx4QEw4qwQyr9J5eo</p>
                        </div>
                    </div>
                </section>
                <section class="wps-box">
                    <h3><?php _e('Want to support in other ways?', 'members-control'); ?></h3>
                    <ul class="wps">
                        <li>
                            <a href="https://translate.wordpress.org/projects/wp-plugins/members-control/"><?php _e('Help me translating', 'members-control'); ?></a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/members-control/reviews/?filter=5"><?php _e('Leave a review', 'members-control'); ?></a>
                        </li>
                    </ul>
                    <h3>Members Control:</h3>
                    <ul class="wps">
                        <li>
                            <a href="https://github.com/sh1zen/members-control/"><?php _e('Source code', 'members-control'); ?></a>
                        </li>
                        <li>
                            <a href="https://sh1zen.github.io/"><?php _e('About me', 'members-control'); ?></a>
                        </li>
                    </ul>
                </section>
            </aside>
        </section>
        <?php
    }
}