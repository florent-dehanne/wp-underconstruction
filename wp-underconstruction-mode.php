<?php
/**
 * Plugin Name: WP Under Construction Mode
 * Description: Display an Under Construction page on your website.
 * Version: 1.4
 * Author: Florent Dehanne
 * Author URI: https://florentdehanne.net
 * Text Domain: wp-underconstruction-mode
 */

  define('UNDERCONSTRUCTION_URL', plugin_dir_url(__FILE__));
  define('UNDERCONSTRUCTION_PATH', __DIR__);

  class UnderConstruction {

    // Plugin version
    public $version;
    public $maintenance = false;

    public function __construct()
    {
      $data = get_file_data(__FILE__, ['Version'], 'plugin');
      $this->version = $data[0];
      $this->maintenance = get_option('wp_underconstruction');
      $this->maintenance = !empty($this->maintenance) ? $this->maintenance : ['enabled' => false];

      add_action('admin_enqueue_scripts', [$this, 'loadBackendAssets']);
      add_action('wp_enqueue_scripts', [$this, 'loadFrontendAssets']);
      add_action('admin_notices', [$this, 'maintenanceNoticeInBackend']);
      add_action('get_header', [$this, 'checkMaintenance']);
      add_action('admin_menu', [$this, 'adminPages']);
      add_action('admin_action_wp_underconstruction_update', [$this, 'update']);
      add_action('admin_bar_menu', [$this, 'maintenanceNoticeInToolbar'], 10000);
    }

    function loadFrontendAssets() {
      wp_enqueue_style('underconstruction-frontend', UNDERCONSTRUCTION_URL.'assets/css/frontend.css', [], $this->version);
    } // loadFrontendAssets()

    function loadBackendAssets() {
      wp_enqueue_style('underconstruction-backend', UNDERCONSTRUCTION_URL.'assets/css/backend.css', [], $this->version);
      wp_enqueue_script('underconstruction-backend', UNDERCONSTRUCTION_URL.'assets/js/backend.js', [], $this->version, true);
    } // loadBackendAssets()

    /** Display a notice in admin bar when maintenance is enabled. */
    function maintenanceNoticeInToolbar($wp_admin_bar)
    {
      if (array_key_exists('enabled', $this->maintenance) && $this->maintenance['enabled'])
      {
  			$wp_admin_bar->add_node([
    			'id' => 'underconstruction',
    			'parent' => 'top-secondary',
    			'title' => 'Under construction mode enabled',
    			'href' => admin_url('admin.php?page=underconstruction'),
          'meta' => [
            'class' => 'underconstruction-enabled'
          ]
    		]);
      }
    } // maintenanceNoticeInToolbar()

    /** Display a notice in backend when maintenance is enabled. */
    function maintenanceNoticeInBackend()
    {
      if (array_key_exists('enabled', $this->maintenance) && $this->maintenance['enabled'])
        echo '<div class="notice notice-error"><p><strong>Le mode maintenance est activé : votre site n\'est pas accessible aux visiteurs !</strong></p></div>';
    } // maintenanceNoticeInBackend()

    /** Redirect to maintenance page */
    function checkMaintenance()
    {
      // Check if maintenance is enabled
      if (array_key_exists('enabled', $this->maintenance) && $this->maintenance['enabled'])
      {
        // Check access (by default: limited to administrators only)
        $access = array_key_exists('access', $this->maintenance) ? $this->maintenance['access'] : 1;

        if ($access == 1)
          $accessAllowed = current_user_can('administrator');
        else
          $accessAllowed = is_user_logged_in();

        if (!$accessAllowed)
        {
          if ($this->maintenance['mode'] == 2 && $this->maintenance['url'])
          {
            // Redirect to custom maintenance page
            wp_redirect($this->maintenance['url'], 302);
            exit;
          }
          elseif ($this->maintenance['mode'] == 3 && $this->maintenance['html'])
          {
            // Display HTML page
            echo $this->maintenance['html'];
            exit;
          }
          else
          {
            // Display maintenance message
            $message = $this->maintenance['message'] ? $this->maintenance['message'] : 'Maintenance en cours';
            wp_die($message);
          }
        }
      }
    } // checkMaintenance()

    function adminPages() {
      add_menu_page('Under construction', 'Under construction', 'manage_options', 'underconstruction', [$this, 'maintenancePageContent']);
    } // adminPages()

    function maintenancePageContent() {
      include(__DIR__.'/views/admin.php');
    } // maintenancePageContent()

    function update()
    {
      update_option('wp_underconstruction', stripslashes_deep($_POST['wp_underconstruction']));
      wp_redirect(admin_url('admin.php?page=underconstruction'));
      exit;
    } // update()
  }

  $UnderConstruction = new UnderConstruction();