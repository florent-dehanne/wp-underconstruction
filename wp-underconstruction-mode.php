<?php
/**
 * Plugin Name: WP Under Construction Mode
 * Description: Display an Under Construction page on your website.
 * Version: 1.2
 * Author: Florent Dehanne
 * Author URI: https://florentdehanne.net
 * Text Domain: wp-underconstruction-mode
 */

  define('UNDERCONSTRUCTION_URL', plugin_dir_url(__FILE__));
  define('UNDERCONSTRUCTION_PATH', __DIR__);

  class UnderConstruction {

    // Plugin version.
    public $version;
    public $maintenance = false;

    public function __construct()
    {
      $data = get_file_data(__FILE__, ['Version'], 'plugin');
      $this->version = $data[0];
      $this->maintenance = get_option('wp_underconstruction');

      add_action('wp_enqueue_scripts', [$this, 'loadFrontendAssets']);
      add_action('admin_notices', [$this, 'maintenanceNotice']);
      add_action('get_header', [$this, 'checkMaintenance']);
      add_action('admin_menu', [$this, 'adminPages']);
      add_action('admin_action_wp_underconstruction_update', [$this, 'update']);
      add_action('admin_bar_menu', [$this, 'maintenanceNoticeInToolbar'], 10000);
    }

    function loadFrontendAssets() {
      wp_enqueue_style('underconstruction', UNDERCONSTRUCTION_URL.'assets/css/frontend.css', [], $this->version);
    } // loadFrontendAssets()

    /** Display a notice in admin bar when maintenance is enabled. */
    function maintenanceNoticeInToolbar($wp_admin_bar)
    {
      if ($this->maintenance['enabled'])
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
    }

    /** Display a notice in backend when maintenance is enabled. */
    function maintenanceNotice()
    {
      if ($this->maintenance['enabled'])
        echo '<div class="notice notice-error"><p><strong>Le mode maintenance est activé : votre site n\'est pas accessible aux visiteurs !</strong></p></div>';
    }

    /** Redirect to maintenance page */
    function checkMaintenance()
    {
      if (!current_user_can('edit_themes') || !is_user_logged_in())
      {
        // Check if maintenance enabled
        if ($this->maintenance['enabled'])
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
    }

    function adminPages() {
      add_menu_page('Under construction', 'Under construction', 'manage_options', 'underconstruction', [$this, 'maintenancePageContent']);
    }

    function maintenancePageContent() {
      include(__DIR__.'/views/admin.php');
    }

    function update()
    {
      update_option('wp_underconstruction', stripslashes($this->_POST['wp_underconstruction']));
      wp_redirect(admin_url('admin.php?page=underconstruction'));
      exit;
    }
  }

  $UnderConstruction = new UnderConstruction();
