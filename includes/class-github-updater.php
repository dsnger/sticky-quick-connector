<?php

namespace StickyQuickConnector;

if (!defined('ABSPATH')) {
  exit;
}

class GitHubUpdater
{
  private $file;
  private $plugin;
  private $basename;
  private $active;
  private $github_response;
  private $github_url = 'https://github.com/dsnger/sticky-quick-connector'; // Replace with your GitHub repo URL
  private $github_username;
  private $github_repo;
  private $authorize_token;

  public function __construct($file)
  {
    $this->file = $file;
    add_action('admin_init', [$this, 'set_plugin_properties']);

    // Extract username and repo from GitHub URL
    $path = parse_url($this->github_url, PHP_URL_PATH);
    list($this->github_username, $this->github_repo) = array_slice(explode('/', trim($path, '/')), 0, 2);

    return $this;
  }

  public function set_plugin_properties()
  {
    $this->plugin = get_plugin_data($this->file);
    $this->basename = plugin_basename($this->file);
    $this->active = is_plugin_active($this->basename);
  }

  private function get_repository_info()
  {
    if (is_null($this->github_response)) {
      $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->github_username, $this->github_repo);

      $args = [];
      if ($this->authorize_token) {
        $args['headers']['Authorization'] = "Bearer {$this->authorize_token}";
      }

      $response = wp_remote_get($request_uri, $args);

      if (is_wp_error($response)) {
        return false;
      }

      $response_code = wp_remote_retrieve_response_code($response);
      if ($response_code !== 200) {
        return false;
      }

      $this->github_response = json_decode(wp_remote_retrieve_body($response));
    }
  }

  public function initialize()
  {
    add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient'], 10, 1);
    add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
    add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
  }

  public function modify_transient($transient)
  {
    if (!property_exists($transient, 'checked')) {
      return $transient;
    }

    $this->get_repository_info();

    if (is_null($this->github_response)) {
      return $transient;
    }

    $doUpdate = version_compare($this->github_response->tag_name, $transient->checked[$this->basename], 'gt');

    if ($doUpdate) {
      $package = $this->github_response->zipball_url;

      if ($this->authorize_token) {
        $package = add_query_arg(['access_token' => $this->authorize_token], $package);
      }

      $obj = new \stdClass();
      $obj->slug = $this->basename;
      $obj->new_version = $this->github_response->tag_name;
      $obj->url = $this->plugin["PluginURI"];
      $obj->package = $package;
      $obj->tested = '6.7.1'; // Update with your tested WordPress version
      $transient->response[$this->basename] = $obj;
    }

    return $transient;
  }

  public function plugin_popup($result, $action, $args)
  {
    if ($action !== 'plugin_information') {
      return $result;
    }

    if (!isset($args->slug) || $args->slug !== dirname($this->basename)) {
      return $result;
    }

    $this->get_repository_info();

    if (is_null($this->github_response)) {
      return $result;
    }

    $plugin = new \stdClass();
    $plugin->name = $this->plugin["Name"];
    $plugin->slug = $this->basename;
    $plugin->version = $this->github_response->tag_name;
    $plugin->author = $this->plugin["AuthorName"];
    $plugin->author_profile = $this->plugin["AuthorURI"];
    $plugin->last_updated = $this->github_response->published_at;
    $plugin->homepage = $this->plugin["PluginURI"];
    $plugin->short_description = $this->plugin["Description"];
    $plugin->sections = [
      'description' => $this->plugin["Description"],
      'changelog' => class_exists('Parsedown')
        ? \Parsedown::instance()->parse($this->github_response->body)
        : $this->github_response->body
    ];
    $plugin->download_link = $this->github_response->zipball_url;
    $plugin->tested = '6.4.3'; // Update with your tested WordPress version

    return $plugin;
  }

  public function after_install($response, $hook_extra, $result)
  {
    global $wp_filesystem;

    $install_directory = plugin_dir_path($this->file);
    $wp_filesystem->move($result['destination'], $install_directory);
    $result['destination'] = $install_directory;

    if ($this->active) {
      activate_plugin($this->basename);
    }

    return $result;
  }
}
