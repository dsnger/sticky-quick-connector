<?php

namespace StickyQuickConnector;

if (!defined('ABSPATH')) {
  exit;
}

class ACFFields
{
  public function __construct()
  {
    add_action('acf/init', [$this, 'registerACFFields']);
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
            'default_value' => 0,
            'ui' => 1,
            'wrapper' => ['width' => '100'],
          ],
          [
            'key' => 'field_679758fa43481',
            'label' => 'Minimale Größe',
            'name' => 'sqc_size_min',
            'type' => 'number',
            'instructions' => 'Minimale Größe des Buttons in Pixel (für mobile Geräte).',
            'required' => 0,
            'default_value' => 48,
            'min' => 32,
            'max' => '',
            'step' => 1,
            'wrapper' => ['width' => '20'],
          ],
          [
            'key' => 'field_6797590589b50',
            'label' => 'Maximale Größe',
            'name' => 'sqc_size_max',
            'type' => 'number',
            'instructions' => 'Maximale Größe des Buttons in Pixel (für Desktop).',
            'required' => 0,
            'default_value' => 72,
            'min' => 32,
            'max' => '',
            'step' => 1,
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
                'wrapper' => ['width' => '20'],
              ],
              [
                'key' => 'field_6791fb35a0a03',
                'label' => 'Icon',
                'name' => 'icon',
                'type' => 'text',
                'instructions' => 'Gib das <a href="https://iconify.design/" target="_blank">Iconify-Icon</a> oder SVG für den Hauptbutton an (z.B. mdi:menu).',
                'required' => 0,
                'default_value' => 'mdi:chat-alert',
                'wrapper' => ['width' => '20'],
              ],
              [
                'key' => 'field_6794dfb9ee584',
                'label' => 'Icon Bild',
                'name' => 'icon_image',
                'type' => 'image',
                'instructions' => 'Wähle ein Bild für den Hauptbutton.',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'wrapper' => ['width' => '20'],
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
                'wrapper' => ['width' => '20'],
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
                'wrapper' => ['width' => '20'],
              ],
              [
                'key' => 'field_6791fb35a0a04',
                'label' => 'Icon (Aktiv)',
                'name' => 'icon_active',
                'type' => 'text',
                'instructions' => 'Gib das <a href="https://iconify.design/" target="_blank">Iconify-Icon</a> oder SVG für den aktiven Zustand an (z.B. mdi:close).',
                'required' => 0,
                'default_value' => 'mdi:close',
                'wrapper' => ['width' => '25'],
              ],
              [
                'key' => 'field_6794e01bcd2f9',
                'label' => 'Icon Bild (Aktiv)',
                'name' => 'icon_image_active',
                'type' => 'image',
                'instructions' => 'Alternativ kannst du ein Bild für den aktiven Zustand auswählen.',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'wrapper' => ['width' => '25'],
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
                'wrapper' => ['width' => '25'],
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
                'wrapper' => ['width' => '25'],
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
                'key' => 'field_6794dfc6476b5',
                'label' => 'Icon Bild',
                'name' => 'icon_image',
                'type' => 'image',
                'instructions' => 'Alternativ kannst du ein Bild für den Kontaktbutton auswählen.',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
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
              [
                'key' => 'field_6794d5d349784',
                'label' => 'HTML Attribute',
                'name' => 'html_attributes',
                'type' => 'text',
                'instructions' => 'Optionale HTML-Attribute (z.B. uk-toggle="target: #modal" oder onClick="myFunction()")',
                'placeholder' => '',
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
          'parent_slug' => '',
          'capability' => 'edit_posts',
          'redirect' => false,
          'icon_url' => 'dashicons-admin-comments',
          'position' => 30,
          'update_button' => 'Speichern',
          'updated_message' => 'Einstellungen gespeichert',
        ]);

        // Add the main settings as a submenu
        acf_add_options_sub_page([
          'page_title' => 'Einstellungen',
          'menu_title' => 'Einstellungen',
          'parent_slug' => 'sticky-quick-connector-settings',
          'menu_slug' => 'sticky-quick-connector-settings-main',
          'capability' => 'edit_posts',
        ]);
      }
    }
  }
}
