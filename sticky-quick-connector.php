<?php

/**
 * Plugin Name: Sticky Quick Connector (DSG Theme)
 * Description: A fixed contact button for expandable link or contact options with flexible setting options based on ACF.
 * Version: 1.0.13
 * Author: Daniel Sänger (webmaster@daniel-saenger.de)
 * License: MIT
 * Text Domain: stickyquickconnector
 */

namespace StickyQuickConnector;

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

// Initialize GitHub Updater
require_once __DIR__ . '/includes/class-github-updater.php';
if (is_admin()) {
    $updater = new GitHubUpdater(__FILE__);
    $updater->initialize();
}

if (!defined('ABSPATH')) {
    exit;
}

class StickyQuickConnector
{
    private $import_export;
    private $acf_fields;
    public $page_id;


    public function __construct($page_id = null)
    {
        $this->page_id = $page_id ? $page_id : get_the_ID();

        register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);
        add_action('admin_init', [$this, 'checkDependencies']);
        add_filter('acf/load_field/key=field_679cda770fc4e', array(__CLASS__, 'load_special_pages_acf_choices'));
        add_filter('acf/load_field/key=field_679cd988ec1ab', array(__CLASS__, 'load_special_pages_acf_choices'));

        if ($this->isAcfActive()) {
            add_action('init', [$this, 'registerAssets']);
            add_action('wp_footer', [$this, 'renderContactButton']);
            add_action('wp_head', [$this, 'addCustomCSS']);
            add_filter('acf/validate_value/key=field_67912b13c5343', [$this, 'validateUrlField'], 10, 4);

            // Initialize ACF Fields
            require_once __DIR__ . '/includes/class-acf-fields.php';
            $this->acf_fields = new ACFFields();

            // Initialize Import/Export functionality
            require_once __DIR__ . '/includes/class-import-export.php';
            $this->import_export = new ImportExport();
        }
    }

    public static function uninstall()
    {
        // This method is required for the uninstall hook, but the actual cleanup
        // is handled in uninstall.php
    }

    private function isAcfActive()
    {
        return class_exists('ACF') && class_exists('ACFE');
    }

    public function checkDependencies()
    {
        if (!$this->isAcfActive()) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>' .
                    __('Sticky Quick Connector benötigt das Plugin "Advanced Custom Fields" (ACF). Bitte installieren und aktivieren Sie ACF.', 'sticky-quick-connector') .
                    '</p></div>';
            });
        }
    }

    /**
     * Register custom CSS and JS for the plugin.
     */
    public function registerAssets()
    {
        wp_register_style('custom-connector-styles', plugins_url('assets/css/styles.css', __FILE__), [], '1.0.0');
        wp_register_script('custom-connector-scripts', plugins_url('assets/js/scripts.js', __FILE__), [], '1.0.0', true);


        // Add defer attribute to the custom JS for better performance
        add_filter('script_loader_tag', function ($tag, $handle) {
            if ($handle === 'custom-connector-scripts') {
                return str_replace(' src', ' defer="defer" src', $tag);
            }
            return $tag;
        }, 10, 2);
    }


    public static function load_special_pages_acf_choices($field)
    {
        // Basic choices (already in field definition)
        $choices = $field['choices'];

        // Add general single and archive options
        $choices['single'] = 'Alle Detailseiten';
        $choices['archive'] = 'Alle Archivseiten';

        // Post Types
        $post_types = get_post_types(array(
            'public' => true,
            '_builtin' => false
        ), 'objects');

        // Add built-in post type 'post'
        $post_types['post'] = get_post_type_object('post');

        // Add single and archive options for each post type
        foreach ($post_types as $post_type) {
            // Add single page option
            $choices['single_' . $post_type->name] = sprintf(
                'Detailseite-%s',
                $post_type->labels->singular_name
            );

            // Add archive option if available
            if ($post_type->has_archive || $post_type->name === 'post') {
                $choices['archive_' . $post_type->name] = sprintf(
                    'Archiv-%s',
                    $post_type->labels->name
                );
            }
        }

        // Taxonomies
        $taxonomies = get_taxonomies(array(
            'public' => true
        ), 'objects');

        foreach ($taxonomies as $taxonomy) {
            $choices['taxonomy_' . $taxonomy->name] = sprintf(
                'Archiv-%s',
                $taxonomy->labels->name
            );
        }

        $field['choices'] = $choices;
        return $field;
    }

    private function get_current_special_page_type()
    {
        $types = array();

        if (is_home() || is_front_page()) {
            $types[] = 'blog';
        }

        if (is_search()) {
            $types[] = 'search';
        }

        if (is_author()) {
            $types[] = 'author';
        }

        if (is_404()) {
            $types[] = '404';
        }

        // Single post type pages
        if (is_single()) {
            // General single page type
            $types[] = 'single';

            // Specific post type
            $post_type = get_post_type();
            if ($post_type) {
                $types[] = 'single_' . $post_type;
            }
        }

        // Archive Checks
        if (is_archive()) {
            // General archive type
            $types[] = 'archive';

            // Post Type Archive
            $post_type = get_post_type();
            if ($post_type) {
                $types[] = 'archive_' . $post_type;
            }

            // Taxonomy Archive
            $queried_object = get_queried_object();
            if ($queried_object instanceof \WP_Term) {
                $types[] = 'taxonomy_' . $queried_object->taxonomy;
            }
        }

        return $types;
    }


    /**
     * Calculate fluid size values for CSS clamp
     * 
     * @param int $minSize Minimum size in pixels
     * @param int $maxSize Maximum size in pixels
     * @param int $minVw Minimum viewport width in pixels
     * @param int $maxVw Maximum viewport width in pixels
     * @return array Array containing min, max, and preferred values for clamp
     */
    private function calculateFluidSize($minSize, $maxSize, $minVw = 420, $maxVw = 1600)
    {
        // Calculate the slope
        $slope = ($maxSize - $minSize) / ($maxVw - $minVw);

        // Convert to vw units
        $vw_value = round($slope * 100, 3);

        // Calculate the y-intercept
        $intercept = round($minSize - ($slope * $minVw), 2);

        return [
            'min' => $minSize . 'px',
            'preferred' => $intercept . 'px + ' . $vw_value . 'vw',
            'max' => $maxSize . 'px'
        ];
    }

    /**
     * Render the main contact button and options.
     */
    public function renderContactButton()
    {
        // Ensure the button is active.
        $button_active = get_field('sqc_activate_button', 'option');
        if (!$button_active) {
            return;
        }

        $current_post_id = $this->page_id;
        $exclude_pages = get_field('sqc_hide_on_posts', 'option');
        $include_pages = get_field('sqc_show_on_posts', 'option');


        if (is_singular()) {

            if (empty($exclude_pages) || (is_array($exclude_pages) && !in_array($current_post_id, $exclude_pages))) {
                return;
            }

            if (empty($include_pages) || (is_array($include_pages) && !in_array($current_post_id, $include_pages))) {
                return;
            }
        }

        $special_pages = self::get_current_special_page_type();

        // Show only on selected special pages
        $show_on_special_pages = get_field('sqc_show_on_special_pages', 'option');

        if (!empty($show_on_special_pages)) {
            $show_on_special = false;
            foreach ($special_pages as $type) {
                if (in_array($type, $show_on_special_pages)) {
                    $show_on_special = true;
                    break;
                }
            }
            if (!$show_on_special) {
                return;
            }
        }

        // Hide on selected special pages
        $hide_on_special_pages = get_field('sqc_hide_on_special_pages', 'option');
        if (!empty($hide_on_special_pages)) {
            foreach ($special_pages as $type) {
                if (in_array($type, $hide_on_special_pages)) {
                    return;
                }
            }
        }

        // Get main settings with defaults
        $main_button = get_field('sqc_main_button', 'option') ?: [];
        $contacts = get_field('sqc_connectors', 'option') ?: [];

        // Get size values directly from ACF options
        $size_min = get_field('sqc_size_min', 'option') ? intval(get_field('sqc_size_min', 'option')) : 48;
        $size_max = get_field('sqc_size_max', 'option') ? intval(get_field('sqc_size_max', 'option')) : 72;

        // CSS Variables for dynamic sizing
        echo '<style>';
        echo ':root {';
        echo '--sqc-size-min: ' . $size_min . 'px;';
        echo '--sqc-size-max: ' . $size_max . 'px;';

        $fluid_size = $this->calculateFluidSize($size_min, $size_max);
        echo '--sqc-size: clamp(' . $fluid_size['min'] . ', ' . $fluid_size['preferred'] . ', ' . $fluid_size['max'] . ');';
        echo '}';

        // Anpassen der Button-Größen
        echo '.fixed-contact-button .main-button, .contact-options .contact-option {';
        echo 'width: var(--sqc-size);';
        echo 'height: var(--sqc-size);';
        echo 'font-size: calc(var(--sqc-size) * 0.5);';
        echo '}';
        echo '</style>';

        // Validate required data
        if (empty($main_button) || !is_array($main_button) || empty($contacts) || !is_array($contacts)) {
            return;
        }

        // Set defaults for main button
        $main_button = array_merge([
            'icon' => '',
            'icon_active' => '',
            'icon_image' => [],
            'icon_image_active' => [],
            'bg_color' => 'rgb(246,109,47)',
            'bg_color_active' => 'rgb(75,48,138)',
            'text_color' => 'rgba(255, 255, 255)',
            'text_color_active' => 'rgba(255, 255, 255)',
            'label' => ''
        ], $main_button);

        // Retrieve mobile position fields.
        $position_x_mobile = intval(get_field('sqc_position_x', 'option') ?: 20);
        $position_y_mobile = intval(get_field('sqc_position_y', 'option') ?: 20);
        $position_x_mobile_alignment = get_field('sqc_position_x_alignment', 'option') ?: 'right';
        $position_y_mobile_alignment = get_field('sqc_position_y_alignment', 'option') ?: 'bottom';

        // Retrieve desktop position fields.
        $position_x_dtp = intval(get_field('sqc_position_x_dtp', 'option') ?: 20);
        $position_y_dtp = intval(get_field('sqc_position_y_dtp', 'option') ?: 20);
        $position_x_dtp_alignment = get_field('sqc_position_x_alignment_dtp', 'option') ?: 'right';
        $position_y_dtp_alignment = get_field('sqc_position_y_alignment_dtp', 'option') ?: 'bottom';

        // Desktop breakpoint.
        $desktop_breakpoint = intval(get_field('field_67b21c4884e6c', 'option') ?: 768);

        /*
         * Determine mobile CSS variables.
         * For horizontal values: if alignment is left, set left offset; if right, set right offset.
         */
        $mobile_left  = $position_x_mobile_alignment === 'left' ? "{$position_x_mobile}px" : "auto";
        $mobile_right = $position_x_mobile_alignment === 'right' ? "{$position_x_mobile}px" : "auto";

        // For vertical values, we also add a transform in case of center alignment.
        if ($position_y_mobile_alignment === 'top') {
            $mobile_top       = "{$position_y_mobile}px";
            $mobile_bottom    = "auto";
            $mobile_transform = "none";
        } elseif ($position_y_mobile_alignment === 'center') {
            $mobile_top       = "50%";
            $mobile_bottom    = "auto";
            $mobile_transform = "translateY(-50%)";
        } else {
            $mobile_bottom    = "{$position_y_mobile}px";
            $mobile_top       = "auto";
            $mobile_transform = "none";
        }

        /*
         * Determine desktop CSS variables.
         */
        $desktop_left  = $position_x_dtp_alignment === 'left' ? "{$position_x_dtp}px" : "auto";
        $desktop_right = $position_x_dtp_alignment === 'right' ? "{$position_x_dtp}px" : "auto";

        if ($position_y_dtp_alignment === 'top') {
            $desktop_top       = "{$position_y_dtp}px";
            $desktop_bottom    = "auto";
            $desktop_transform = "none";
        } elseif ($position_y_dtp_alignment === 'center') {
            $desktop_top       = "50%";
            $desktop_bottom    = "auto";
            $desktop_transform = "translateY(-50%)";
        } else {
            $desktop_bottom    = "{$position_y_dtp}px";
            $desktop_top       = "auto";
            $desktop_transform = "none";
        }

        /*
         * Build an inline style attribute that defines CSS variables.
         * These variables are later used within the element's style rules,
         * either inline or via an inline media query below.
         */
        $inline_style  = "position: fixed; z-index: 9999; opacity: 0; visibility: hidden;";
        $inline_style .= " --sqc-mobile-left: {$mobile_left};";
        $inline_style .= " --sqc-mobile-right: {$mobile_right};";
        $inline_style .= " --sqc-mobile-top: {$mobile_top};";
        $inline_style .= " --sqc-mobile-bottom: {$mobile_bottom};";
        $inline_style .= " --sqc-mobile-transform: {$mobile_transform};";
        $inline_style .= " --sqc-desktop-left: {$desktop_left};";
        $inline_style .= " --sqc-desktop-right: {$desktop_right};";
        $inline_style .= " --sqc-desktop-top: {$desktop_top};";
        $inline_style .= " --sqc-desktop-bottom: {$desktop_bottom};";
        $inline_style .= " --sqc-desktop-transform: {$desktop_transform};";

        // Output an inline <style> block that uses the CSS variables.
        // (You could also put these rules in your external stylesheet.)
        echo '<style>';
        echo '.fixed-contact-button {';
        echo '  left: var(--sqc-mobile-left);';
        echo '  right: var(--sqc-mobile-right);';
        echo '  top: var(--sqc-mobile-top);';
        echo '  bottom: var(--sqc-mobile-bottom);';
        echo '  transform: var(--sqc-mobile-transform);';
        echo '}';
        echo "@media (min-width: {$desktop_breakpoint}px) {";
        echo '  .fixed-contact-button {';
        echo '    left: var(--sqc-desktop-left);';
        echo '    right: var(--sqc-desktop-right);';
        echo '    top: var(--sqc-desktop-top);';
        echo '    bottom: var(--sqc-desktop-bottom);';
        echo '    transform: var(--sqc-desktop-transform);';
        echo '  }';
        echo '}';
        echo '</style>';

        // Enqueue the registered styles and scripts.
        wp_enqueue_style('custom-connector-styles');
        wp_enqueue_script('custom-connector-scripts');

        // Render the container using the inline style with the CSS variable definitions.
        echo '<div class="fixed-contact-button" style="' . esc_attr($inline_style) . '"';
        echo ' data-trigger-type="' . esc_attr(get_field('sqc_display_trigger', 'option') ?: 'immediate') . '"';
        echo ' data-time-delay="' . esc_attr(get_field('sqc_display_delay', 'option') ?: 0) . '"';
        echo ' data-scroll-distance="' . esc_attr(get_field('sqc_scroll_distance', 'option') ?: 25) . '">';

        // Main button tooltip with left position
        echo '<button id="toggle-contact-options" class="main-button"
            style="background-color: ' . esc_attr($main_button['bg_color']) . '; color: ' . esc_attr($main_button['text_color']) . ';"
            data-icon-default="' . esc_attr($main_button['icon']) . '"
            data-icon-active="' . esc_attr($main_button['icon_active']) . '"
            data-bg-default="' . esc_attr($main_button['bg_color']) . '"
            data-bg-active="' . esc_attr($main_button['bg_color_active']) . '"
            data-text-default="' . esc_attr($main_button['text_color']) . '"
            data-text-active="' . esc_attr($main_button['text_color_active']) . '"';
        if ($main_button['label']) {
            echo ' uk-tooltip="title: ' . esc_attr($main_button['label']) . '; pos: left"';
        }
        echo '>';

        if (!empty($main_button['icon'])) {
            echo '<span class="iconify icon-default" data-icon="' . esc_attr($main_button['icon']) . '"></span>';
        } elseif (!empty($main_button['icon_image'])) {
            echo '<img src="' . esc_url($main_button['icon_image']['url']) . '" alt="' . esc_attr($main_button['icon_image']['alt']) . '" class="icon-image icon-default" />';
        }
        if (!empty($main_button['icon_active'])) {
            echo '<span class="iconify icon-active" data-icon="' . esc_attr($main_button['icon_active']) . '" style="display: none;"></span>';
        } elseif (!empty($main_button['icon_image_active'])) {
            echo '<img src="' . esc_url($main_button['icon_image_active']['url']) . '" alt="' . esc_attr($main_button['icon_image_active']['alt']) . '" class="icon-image icon-active" style="display: none;" />';
        }
        echo '</button>';

        // Contact options with left tooltips
        echo '<div id="contact-options" class="contact-options">';

        if ($contacts) {
            foreach ($contacts as $contact) {
                // Set defaults for each contact
                $contact = array_merge([
                    'url' => '',
                    'icon' => '',
                    'icon_image' => [],
                    'bg_color' => 'rgba(0, 123, 255, 1)',
                    'text_color' => 'rgba(255, 255, 255, 1)',
                    'label' => '',
                    'html_attributes' => ''
                ], $contact);

                // Skip if both URL is invalid AND there are no HTML attributes
                if (!$this->validateUrl($contact['url']) && empty($contact['html_attributes'])) {
                    continue;
                }

                $contact_bg_color = $contact['bg_color'] ?: 'rgba(0, 123, 255, 1)';
                $contact_text_color = $contact['text_color'] ?: 'rgba(255, 255, 255, 1)';

                echo '<a ' . (!empty($contact['url']) ? 'href="' . esc_url($contact['url']) . '"' : '') . ' 
                    class="contact-option" 
                    style="opacity: 0; background-color: ' . esc_attr($contact_bg_color) . '; color: ' . esc_attr($contact_text_color) . ';"';

                if (!empty($contact['label'])) {
                    echo ' uk-tooltip="title: ' . esc_attr($contact['label']) . '; pos: left"';
                }

                // Add the custom HTML attributes if they exist
                if (!empty($contact['html_attributes'])) {
                    echo ' ' . wp_kses_post($contact['html_attributes']);
                }

                echo '>';

                if (!empty($contact['icon'])) {
                    echo '<span class="iconify" data-icon="' . esc_attr($contact['icon']) . '" data-inline="false"></span>';
                } elseif (!empty($contact['icon_image'])) {
                    echo '<img src="' . esc_url($contact['icon_image']['url']) . '" alt="' . esc_attr($contact['icon_image']['alt']) . '" class="icon-image" />';
                }

                echo '</a>';
            }
        }
        echo '</div>';
        echo '</div>';

        // Add inline script for display logic
?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const button = document.querySelector('.fixed-contact-button');
                const triggerType = button.getAttribute('data-trigger-type');

                function showButton() {
                    button.style.transition = 'opacity 0.3s, visibility 0.3s';
                    button.style.opacity = '1';
                    button.style.visibility = 'visible';
                }

                switch (triggerType) {
                    case 'immediate':
                        showButton();
                        break;

                    case 'time':
                        const delay = parseInt(button.getAttribute('data-time-delay')) || 0;
                        setTimeout(showButton, delay);
                        break;

                    case 'scroll':
                        const scrollDistance = parseInt(button.getAttribute('data-scroll-distance')) || 25;
                        let buttonShown = false;

                        function checkScroll() {
                            if (buttonShown) return;

                            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;

                            if (scrollPercent >= scrollDistance) {
                                showButton();
                                buttonShown = true;
                                window.removeEventListener('scroll', checkScroll);
                            }
                        }

                        window.addEventListener('scroll', checkScroll);
                        // Check initial scroll position
                        checkScroll();
                        break;
                }
            });
        </script>
<?php
    }


    private function validateUrl($url)
    {
        // If URL is empty, check if we have HTML attributes in the parent array
        if (empty($url)) {
            // Get the current contact option being validated
            $current = current(get_field('sqc_connectors', 'option') ?: []);
            if (!empty($current['html_attributes'])) {
                return true; // Allow empty URL if HTML attributes exist
            }
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true; // Valid URL
        }

        if (preg_match('/^(tel:|mailto:)/', $url)) {
            return true; // Valid tel: or mailto: links
        }

        return false; // Invalid URL
    }


    public function validateUrlField($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }

        // Safely get HTML attributes
        $row = isset($_POST['acf'][$field['parent']]) ? $_POST['acf'][$field['parent']] : [];
        $html_attributes = isset($row['html_attributes']) ? $row['html_attributes'] : '';

        // Allow empty URL if HTML attributes exist
        if (empty($value) && !empty($html_attributes)) {
            return true;
        }

        // Allow valid URLs, tel: and mailto: links
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL) && !preg_match('/^(tel:|mailto:)/', $value)) {
            return 'Bitte eine gültige URL, eine Telefonnummer (tel:) oder eine E-Mail-Adresse (mailto:) eingeben. Die URL kann leer sein, wenn HTML-Attribute angegeben sind.';
        }

        return $valid;
    }


    public function addCustomCSS()
    {
        $button_active = get_field('sqc_activate_button', 'option');
        if (!$button_active) return;

        $custom_css = get_field('sqc_custom_css', 'option');
        if ($custom_css) {
            echo "\n<style id='sticky-quick-connector-custom-css'>\n";
            echo wp_strip_all_tags($custom_css) . "\n";
            echo "</style>\n";
        }
    }
}

// Initialize the plugin
new StickyQuickConnector();
