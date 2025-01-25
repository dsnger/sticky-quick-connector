<?php

namespace StickyQuickConnector;

if (!defined('ABSPATH')) {
    exit;
}

class ImportExport {
    public function __construct() {
        add_action('admin_menu', [$this, 'addImportExportPage'], 99);
        add_action('admin_post_sqc_export_settings', [$this, 'exportSettings']);
        add_action('admin_post_sqc_import_settings', [$this, 'importSettings']);
        add_action('admin_notices', [$this, 'showImportExportNotices']);
    }

    /**
     * Add Import/Export submenu page
     */
    public function addImportExportPage() {
        add_submenu_page(
            'sticky-quick-connector-settings',
            'Import/Export',
            'Import/Export',
            'manage_options',
            'sqc-import-export',
            [$this, 'renderImportExportPage'],
        );
    }

    /**
     * Render the Import/Export page
     */
    public function renderImportExportPage() {
        ?>
        <div class="wrap">
            <h1>Quick Connector Import/Export</h1>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Export Settings</h2>
                <p>Export all Quick Connector settings as a JSON file.</p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('sqc_export_nonce', 'sqc_export_nonce'); ?>
                    <input type="hidden" name="action" value="sqc_export_settings">
                    <button type="submit" class="button button-primary">Export Settings</button>
                </form>
            </div>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Import Settings</h2>
                <p>Import Quick Connector settings from a JSON file. This will overwrite all existing settings.</p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('sqc_import_nonce', 'sqc_import_nonce'); ?>
                    <input type="hidden" name="action" value="sqc_import_settings">
                    <input type="file" name="import_file" accept=".json" required>
                    <p><button type="submit" class="button button-primary">Import Settings</button></p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Handle settings export
     */
    public function exportSettings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        if (!isset($_POST['sqc_export_nonce']) || !wp_verify_nonce($_POST['sqc_export_nonce'], 'sqc_export_nonce')) {
            wp_die('Invalid nonce');
        }

        // Get all ACF option fields
        $export_data = [
            'sqc_activate_button' => get_field('sqc_activate_button', 'option'),
            'sqc_position_x' => get_field('sqc_position_x', 'option'),
            'sqc_position_y' => get_field('sqc_position_y', 'option'),
            'sqc_position_x_alignment' => get_field('sqc_position_x_alignment', 'option'),
            'sqc_position_y_alignment' => get_field('sqc_position_y_alignment', 'option'),
            'sqc_exclude_pages' => get_field('sqc_exclude_pages', 'option'),
            'sqc_include_pages' => get_field('sqc_include_pages', 'option'),
            'sqc_display_trigger' => get_field('sqc_display_trigger', 'option'),
            'sqc_display_delay' => get_field('sqc_display_delay', 'option'),
            'sqc_scroll_distance' => get_field('sqc_scroll_distance', 'option'),
            'sqc_custom_css' => get_field('sqc_custom_css', 'option'),
            'sqc_main_button' => get_field('sqc_main_button', 'option'),
            'sqc_connectors' => get_field('sqc_connectors', 'option'),
        ];

        // Set headers for JSON download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=quick-connector-settings-' . date('Y-m-d') . '.json');
        header('Pragma: no-cache');

        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Handle settings import
     */
    public function importSettings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        if (!isset($_POST['sqc_import_nonce']) || !wp_verify_nonce($_POST['sqc_import_nonce'], 'sqc_import_nonce')) {
            wp_die('Invalid nonce');
        }

        if (!isset($_FILES['import_file'])) {
            wp_redirect(add_query_arg('import-error', 'no-file', wp_get_referer()));
            exit;
        }

        $file = $_FILES['import_file'];
        
        // Basic file validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg('import-error', 'upload-failed', wp_get_referer()));
            exit;
        }

        // Read and decode JSON file
        $json_data = file_get_contents($file['tmp_name']);
        $settings = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_redirect(add_query_arg('import-error', 'invalid-json', wp_get_referer()));
            exit;
        }

        // Update all ACF option fields
        foreach ($settings as $field_name => $value) {
            update_field($field_name, $value, 'option');
        }

        // Redirect back with success message
        wp_redirect(add_query_arg('import-success', '1', wp_get_referer()));
        exit;
    }

    /**
     * Show admin notices for import/export actions
     */
    public function showImportExportNotices() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'sqc-import-export') {
            return;
        }

        if (isset($_GET['import-success'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Settings imported successfully!</p></div>';
        }

        if (isset($_GET['import-error'])) {
            $error = $_GET['import-error'];
            $message = 'An error occurred during import.';
            
            switch ($error) {
                case 'no-file':
                    $message = 'No file was uploaded.';
                    break;
                case 'upload-failed':
                    $message = 'File upload failed.';
                    break;
                case 'invalid-json':
                    $message = 'Invalid JSON file.';
                    break;
            }
            
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }
} 