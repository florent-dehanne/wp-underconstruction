<?php
/**
 * Plugin Name: WP Under Construction
 * Description: Display an Under Construction page on your website.
 * Version: 1.0
 * Author: Florent Dehanne
 * Text Domain: wp-underconstruction
 */

  define('UNDERCONSTRUCTION_URL', plugin_dir_url(__FILE__));

  class UnderConstruction {

    // Plugin version.
    public $version;
    public $maintenance = false;

    public function __construct()
    {
      $data = get_file_data(__FILE__, ['Version'], 'plugin');
      $this->version = $data[0];
      $this->maintenance = get_option('wp_underconstruction');

      add_action('admin_init', [$this, 'unescapeRequest']);
      add_action('admin_notices', [$this, 'maintenanceNotice']);
      add_action('get_header', [$this, 'checkMaintenance']);
      add_action('admin_menu', [$this, 'adminPages']);
      add_action('admin_action_wp_underconstruction_update', [$this, 'update']);
    }

    /** Display a notice when maintenance is enabled. */
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
      update_option('wp_underconstruction', $this->_POST['wp_underconstruction']);
      wp_redirect(admin_url('admin.php?page=underconstruction'));
      exit;
    }

    /* Wordpress issue with magic quotes.
     * See: https://stackoverflow.com/q/8949768/1320311 */
    function unescapeRequest()
    {
      $this->_GET = array_map('stripslashes_deep', $_GET);
      $this->_POST = array_map('stripslashes_deep', $_POST);
      $this->_COOKIE = array_map('stripslashes_deep', $_COOKIE);
      $this->_SERVER = array_map('stripslashes_deep', $_SERVER);
      $this->_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    }
  }

  $UnderConstruction = new UnderConstruction();