<?php

/**
 * Plugin Name: DSG Sticky Quick Connector
 * Description: Ein fixierter Kontaktbutton mit erweiterten Optionen basierend auf ACF.
 * Version: 1.0.0
 * Author: Daniel Sänger (webmaster@daniel-saenger.de)
 * License: GPL-2.0-or-later
 * Text Domain: stickyquickconnector
 */

namespace StickyQuickConnector;

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

if (!defined('ABSPATH')) {
    exit;
}

class StickyQuickConnector
{
    public function __construct()
    {
        register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);
        add_action('admin_init', [$this, 'checkDependencies']);
        if ($this->isAcfActive()) {
            add_action('init', [$this, 'registerAssets']);
            add_action('wp_footer', [$this, 'renderContactButton']);
            add_action('acf/init', [$this, 'registerACFFields']);
            add_action('admin_menu', [$this, 'addOptionsPage']);
            add_action('wp_head', [$this, 'addCustomCSS']);
            add_filter('acf/validate_value/key=field_67912b13c5343', [$this, 'validateUrlField'], 10, 4);
        }
    }

    public static function uninstall()
    {
        // This method is required for the uninstall hook, but the actual cleanup
        // is handled in uninstall.php
    }

    private function isAcfActive()
    {
        return class_exists('ACF');
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

    /**
     * Register ACF fields for plugin settings.
     */
    public function registerACFFields()
    {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'group_67912b274485f',
                'title' => 'Quick Connector Optionen',
                'fields' => [
                    [
                        'key' => 'field_display_tab',
                        'label' => 'Anzeige',
                        'name' => '',
                        'type' => 'tab',
                        'placement' => 'top',
                    ],
                    [
                        'key' => 'field_67912e6f0a1b9',
                        'label' => 'Quick Connector aktivieren?',
                        'name' => 'sqc_activate_button',
                        'type' => 'true_false',
                        'instructions' => 'Aktiviere oder deaktiviere den Quick Connector Button.',
                        'default_value' => 1,
                        'ui' => 1,
                        'wrapper' => ['width' => '20'],
                    ],
                    [
                        'key' => 'field_6791fb55eea87',
                        'label' => 'Position X',
                        'name' => 'sqc_position_x',
                        'type' => 'number',
                        'instructions' => 'Abstand vom Rand in Pixel.',
                        'required' => 0,
                        'default_value' => 20,
                        'wrapper' => ['width' => '20'],
                    ],
                    [
                        'key' => 'field_6791fb4c29a32',
                        'label' => 'Position Y',
                        'name' => 'sqc_position_y',
                        'type' => 'number',
                        'instructions' => 'Abstand vom Rand in Pixel.',
                        'required' => 0,
                        'default_value' => 20,
                        'wrapper' => ['width' => '20'],
                    ],
                    [
                        'key' => 'field_67922b010d35b',
                        'label' => 'Horizontale Position',
                        'name' => 'sqc_position_x_alignment',
                        'type' => 'select',
                        'instructions' => 'Wähle die horizontale Position des Buttons.',
                        'choices' => [
                            'left' => 'Links',
                            'right' => 'Rechts',
                        ],
                        'default_value' => 'right',
                        'wrapper' => ['width' => '20'],
                    ],
                    [
                        'key' => 'field_67922b0a25cd9',
                        'label' => 'Vertikale Position',
                        'name' => 'sqc_position_y_alignment',
                        'type' => 'select',
                        'instructions' => 'Wähle die vertikale Position des Buttons.',
                        'choices' => [
                            'top' => 'Oben',
                            'center' => 'Mitte',
                            'bottom' => 'Unten',
                        ],
                        'default_value' => 'bottom',
                        'wrapper' => ['width' => '20'],
                    ],
                    [
                        'key' => 'field_67912e921e77c',
                        'label' => 'Seiten ausschließen',
                        'name' => 'sqc_exclude_pages',
                        'type' => 'post_object',
                        'instructions' => 'Wähle Seiten, Beiträge oder CPTs aus, die den Button nicht anzeigen sollen.',
                        'multiple' => 1,
                        'return_format' => 'id',
                        'wrapper' => ['width' => '50'],
                    ],
                    [
                        'key' => 'field_67912e9ce0cb1',
                        'label' => 'Seiten explizit anzeigen',
                        'name' => 'sqc_include_pages',
                        'type' => 'post_object',
                        'instructions' => 'Wähle Seiten, Beiträge oder CPTs aus, die den Button explizit anzeigen sollen.',
                        'multiple' => 1,
                        'return_format' => 'id',
                        'wrapper' => ['width' => '50'],
                    ],
                    [
                        'key' => 'field_67922b1bd1948',
                        'label' => 'Anzeigetrigger',
                        'name' => 'sqc_display_trigger',
                        'type' => 'select',
                        'instructions' => 'Wähle aus, wann der Button angezeigt werden soll.',
                        'choices' => [
                            'immediate' => 'Sofort',
                            'time' => 'Nach Zeitverzögerung',
                            'scroll' => 'Nach Scrollposition'
                        ],
                        'default_value' => 'immediate',
                        'wrapper' => ['width' => '50'],
                    ],
                    [
                        'key' => 'field_67922b24a9c17',
                        'label' => 'Zeitverzögerung',
                        'name' => 'sqc_display_delay',
                        'type' => 'number',
                        'instructions' => 'Verzögerung in Sekunden.',
                        'required' => 0,
                        'default_value' => 0,
                        'min' => 0,
                        'max' => 60,
                        'step' => 0.5,
                        'wrapper' => ['width' => '50'],
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'field_67922b1bd1948',
                                    'operator' => '==',
                                    'value' => 'time',
                                ]
                            ]
                        ]
                    ],
                    [
                        'key' => 'field_67922b2e6e174',
                        'label' => 'Scrollposition',
                        'name' => 'sqc_scroll_distance',
                        'type' => 'number',
                        'instructions' => 'Scrollposition in Prozent (0-100).',
                        'required' => 0,
                        'default_value' => 25,
                        'min' => 0,
                        'max' => 100,
                        'step' => 5,
                        'wrapper' => ['width' => '50'],
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'field_67922b1bd1948',
                                    'operator' => '==',
                                    'value' => 'scroll',
                                ]
                            ]
                        ]
                    ],
                    [
                        'key' => 'field_67934e6f1c421',
                        'label' => 'Benutzerdefiniertes CSS',
                        'name' => 'sqc_custom_css',
                        'type' => 'acfe_code_editor',
                        'instructions' => 'Fügen Sie hier benutzerdefiniertes CSS hinzu. Beispiel: .fixed-contact-button { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }',
                        'placeholder' => '',
                        'maxlength' => '',
                        'rows' => 6,
                        'new_lines' => '',
                        'wrapper' => ['width' => '100'],
                    ],
                    [
                        'key' => 'field_67922b393b8ca',
                        'label' => 'Hauptbutton',
                        'name' => '',
                        'type' => 'tab',
                        'placement' => 'top',
                    ],
                    [
                        'key' => 'field_67920b34628ef',
                        'label' => 'Hauptbutton-Einstellungen',
                        'name' => 'sqc_main_button',
                        'type' => 'group',
                        'sub_fields' => [
                            [
                                'key' => 'field_6791fb41430e4',
                                'label' => 'Beschriftung',
                                'name' => 'label',
                                'type' => 'text',
                                'instructions' => 'Optional: Beschriftung für den Hauptbutton.',
                                'required' => 0,
                                'default_value' => 'Kontakt',
                                'wrapper' => ['width' => '25'],
                            ],
                            [
                                'key' => 'field_6791fb35a0a03',
                                'label' => 'Icon',
                                'name' => 'icon',
                                'type' => 'text',
                                'instructions' => 'Gib das <a href="https://iconify.design/" target="_blank">Iconify-Icon</a> oder SVG für den Hauptbutton an (z.B. mdi:menu).',
                                'required' => 0,
                                'default_value' => 'mdi:menu',
                                'wrapper' => ['width' => '25'],
                            ],
                            [
                                'key' => 'field_6791fb2b96f39',
                                'label' => 'Hintergrundfarbe',
                                'name' => 'bg_color',
                                'type' => 'color_picker',
                                'instructions' => 'Wähle die Hintergrundfarbe für den Hauptbutton.',
                                'required' => 0,
                                'enable_opacity' => 1,
                                'return_format' => 'string',
                                'default_value' => 'rgb(246,109,47)',
                                'wrapper' => ['width' => '25'],
                            ],
                            [
                                'key' => 'field_6791fb2173265',
                                'label' => 'Text-/Icon-Farbe',
                                'name' => 'text_color',
                                'type' => 'color_picker',
                                'instructions' => 'Wähle die Text- oder Iconfarbe für den Hauptbutton.',
                                'required' => 0,
                                'enable_opacity' => 1,
                                'return_format' => 'string',
                                'default_value' => 'rgba(255, 255, 255)',
                                'wrapper' => ['width' => '25'],
                            ],
                            [
                                'key' => 'field_6791fb35a0a04',
                                'label' => 'Icon (Aktiv)',
                                'name' => 'icon_active',
                                'type' => 'text',
                                'instructions' => 'Gib das <a href="https://iconify.design/" target="_blank">Iconify-Icon</a> oder SVG für den aktiven Zustand an (z.B. mdi:close).',
                                'required' => 0,
                                'default_value' => 'mdi:close',
                                'wrapper' => ['width' => '33'],
                            ],
                            [
                                'key' => 'field_6791fb2b96f40',
                                'label' => 'Hintergrundfarbe (Aktiv)',
                                'name' => 'bg_color_active',
                                'type' => 'color_picker',
                                'instructions' => 'Wähle die Hintergrundfarbe für den aktiven Zustand.',
                                'required' => 0,
                                'enable_opacity' => 1,
                                'return_format' => 'string',
                                'default_value' => 'rgb(75,48,138)',
                                'wrapper' => ['width' => '33'],
                            ],
                            [
                                'key' => 'field_679213bac8cc0',
                                'label' => 'Text-/Icon-Farbe (Aktiv)',
                                'name' => 'text_color_active',
                                'type' => 'color_picker',
                                'instructions' => 'Wähle die Text- oder Iconfarbe für den aktiven Zustand.',
                                'required' => 0,
                                'enable_opacity' => 1,
                                'return_format' => 'string',
                                'default_value' => 'rgba(255, 255, 255)',
                                'wrapper' => ['width' => '33'],
                            ],
                        ],
                    ],
                    [
                        'key' => 'field_67922b4223fd0',
                        'label' => 'Kontaktoptionen',
                        'name' => '',
                        'type' => 'tab',
                        'placement' => 'top',
                    ],
                    [
                        'key' => 'field_67912b33064e2',
                        'label' => 'Kontaktmöglichkeiten',
                        'name' => 'sqc_connectors',
                        'type' => 'repeater',
                        'button_label' => 'Kontakt hinzufügen',
                        'sub_fields' => [
                            [
                                'key' => 'field_67912b0ad3a02',
                                'label' => 'Label',
                                'name' => 'label',
                                'type' => 'text',
                            ],
                            [
                                'key' => 'field_67912b13c5343',
                                'label' => 'URL',
                                'name' => 'url',
                                'type' => 'text',
                                'instructions' => 'URL kann auch "tel:" oder "mailto:"-Links enthalten.',
                            ],
                            [
                                'key' => 'field_67912b1c74de8',
                                'label' => 'Icon',
                                'name' => 'icon',
                                'type' => 'text',
                                'instructions' => 'Name des <a href="https://iconify.design/" target="_blank">Iconify-Icon</a> (z.B. mdi:email) oder SVG.',
                            ],
                            [
                                'key' => 'field_6791fb161a539',
                                'label' => 'Hintergrundfarbe',
                                'name' => 'bg_color',
                                'type' => 'color_picker',
                                'instructions' => 'Wähle die Hintergrundfarbe für den Kontaktbutton.',
                                'enable_opacity' => 1,
                                'return_format' => 'string',
                                'default_value' => 'rgb(75,48,138)',
                            ],
                            [
                                'key' => 'field_6791fb08ac942',
                                'label' => 'Text-/Icon-Farbe',
                                'name' => 'text_color',
                                'type' => 'color_picker',
                                'instructions' => 'Wähle die Text- oder Iconfarbe für den Kontaktbutton.',
                                'enable_opacity' => 1,
                                'return_format' => 'string',
                                'default_value' => 'rgba(255, 255, 255, 1)',
                            ],
                        ],
                    ],
                
                ],
                'location' => [
                    [
                        [
                            'param' => 'options_page',
                            'operator' => '==',
                            'value' => 'sticky-quick-connector-settings',
                        ],
                    ],
                ],
            ]);

            if (function_exists('acf_add_options_page')) {
                acf_add_options_page([
                    'page_title' => 'Quick Connector Einstellungen',
                    'menu_title' => 'Quick Connector',
                    'menu_slug' => 'sticky-quick-connector-settings',
                    'capability' => 'edit_posts',
                    'redirect' => false,
                    'icon_url' => 'dashicons-admin-comments',
                ]);
            }
        }
    }

    /**
     * Render the main contact button and options.
     */
    public function renderContactButton()
    {
        $button_active = get_field('sqc_activate_button', 'option');
        if (!$button_active) return;

        $exclude_pages = get_field('sqc_exclude_pages', 'option');
        $include_pages = get_field('sqc_include_pages', 'option');
        $position_x = get_field('sqc_position_x', 'option');
        $position_y = get_field('sqc_position_y', 'option');
        $position_x_alignment = get_field('sqc_position_x_alignment', 'option') ?: 'right';
        $position_y_alignment = get_field('sqc_position_y_alignment', 'option') ?: 'bottom';
        $current_post_id = get_the_ID();

        // Exclude/Include logic
        if (is_array($exclude_pages) && in_array($current_post_id, $exclude_pages)) return;
        if (is_array($include_pages) && !empty($include_pages) && !in_array($current_post_id, $include_pages)) return;

        // Get main button settings with default values
        $main_button = get_field('sqc_main_button', 'option');
        $contacts = get_field('sqc_connectors', 'option');

        if (!is_array($main_button) || !is_array($contacts)) {
            return;
        }

        // Enqueue styles and scripts
        wp_enqueue_style('custom-connector-styles');
        wp_enqueue_script('custom-connector-scripts');

        // Calculate position styles
        $position_styles = [];

        // Horizontal positioning
        if ($position_x_alignment === 'left') {
            $position_styles[] = "left: {$position_x}px";
            $position_styles[] = "right: auto";
        } else {
            $position_styles[] = "right: {$position_x}px";
            $position_styles[] = "left: auto";
        }

        // Vertical positioning
        if ($position_y_alignment === 'top') {
            $position_styles[] = "top: {$position_y}px";
            $position_styles[] = "bottom: auto";
            $position_styles[] = "transform: none";
        } elseif ($position_y_alignment === 'center') {
            $position_styles[] = "top: 50%";
            $position_styles[] = "bottom: auto";
            $position_styles[] = "transform: translateY(-50%)";
        } else {
            $position_styles[] = "bottom: {$position_y}px";
            $position_styles[] = "top: auto";
            $position_styles[] = "transform: none";
        }

        // Render the contact button and options
        echo '<div class="fixed-contact-button" 
            style="position: fixed; ' . esc_attr(implode('; ', $position_styles)) . '; z-index: 9999; opacity: 0; visibility: hidden;" 
            data-trigger-type="' . esc_attr(get_field('sqc_display_trigger', 'option') ?: 'immediate') . '"
            data-time-delay="' . esc_attr(get_field('sqc_display_delay', 'option') ?: 0) . '"
            data-scroll-distance="' . esc_attr(get_field('sqc_scroll_distance', 'option') ?: 25) . '">';

        // Main button tooltip with left position
        echo '<button id="toggle-contact-options" class="main-button" 
            style="background-color: ' . esc_attr($main_button['bg_color']) . '; color: ' . esc_attr($main_button['text_color']) . ';"
            data-icon-default="' . esc_attr($main_button['icon']) . '"
            data-icon-active="' . esc_attr($main_button['icon_active']) . '"
            data-bg-default="' . esc_attr($main_button['bg_color']) . '"
            data-bg-active="' . esc_attr($main_button['bg_color_active']) . '"
            data-text-default="' . esc_attr($main_button['text_color']) . '"
            data-text-active="' . esc_attr($main_button['text_color_active']) . '"
            data-position-y="' . esc_attr($position_y_alignment) . '"';
        if ($main_button['label']) {
            echo ' uk-tooltip="title: ' . esc_attr($main_button['label']) . '; pos: left"';
        }
        echo '>';

        if ($main_button['icon']) {
            echo '<span class="iconify icon-default" data-icon="' . esc_attr($main_button['icon']) . '"></span>';
            echo '<span class="iconify icon-active" data-icon="' . esc_attr($main_button['icon_active']) . '" style="display: none;"></span>';
        }
        echo '</button>';

        // Contact options with left tooltips
        echo '<div id="contact-options" class="contact-options" style="position: absolute; right: 0; display: none;">';

        if ($contacts) {

            foreach ($contacts as $index => $contact) {
                $url = $contact['url'];
                if (!$this->validateUrl($url)) {
                    continue;
                }

                $contact_bg_color = $contact['bg_color'] ?: 'rgba(0, 123, 255, 1)';
                $contact_text_color = $contact['text_color'] ?: 'rgba(255, 255, 255, 1)';

                echo '<a href="' . esc_url($contact['url']) . '" 
                    class="contact-option" 
                    style="opacity: 0; background-color: ' . esc_attr($contact_bg_color) . '; color: ' . esc_attr($contact_text_color) . ';"';

                if (!empty($contact['label'])) {
                    echo ' uk-tooltip="title: ' . esc_attr($contact['label']) . '; pos: left"';
                }

                echo '>';

                if (!empty($contact['icon'])) {
                    echo '<span class="iconify" data-icon="' . esc_attr($contact['icon']) . '" data-inline="false"></span>';
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

    /**
     * Validate URL or custom links like tel: and mailto:
     */
    private function validateUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true; // Gültige URL
        }

        if (preg_match('/^(tel:|mailto:)/', $url)) {
            return true; // Gültige tel: oder mailto: Links
        }

        return false; // Ungültige URL
    }

    /**
     * Validate URL field to allow tel: and mailto: links.
     */
    public function validateUrlField($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid; // Skip if already invalid
        }

        // Allow valid URLs, tel: and mailto: links
        if (!filter_var($value, FILTER_VALIDATE_URL) && !preg_match('/^(tel:|mailto:)/', $value)) {
            $valid = 'Bitte eine gültige URL, eine Telefonnummer (tel:) oder eine E-Mail-Adresse (mailto:) eingeben.';
        }

        return $valid;
    }

    public function addOptionsPage()
    {
        // Implementation of addOptionsPage method
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
