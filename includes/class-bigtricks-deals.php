<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://bigtricks.in
 * @since      1.0.0
 *
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/includes
 * @author     Bigtricks <sonugpc@gmail.com>
 */
class Bigtricks_Deals {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bigtricks_Deals_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BTDEALS_VERSION' ) ) {
			$this->version = BTDEALS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bigtricks-deals';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bigtricks_Deals_Loader. Orchestrates the hooks of the plugin.
	 * - Bigtricks_Deals_i18n. Defines internationalization functionality.
	 * - Bigtricks_Deals_Admin. Defines all hooks for the admin area.
	 * - Bigtricks_Deals_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bigtricks-deals-loader.php';

		/**
		 * The class responsible for rendering content.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bigtricks-deals-content-helper.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bigtricks-deals-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-bigtricks-deals-public.php';

		$this->loader = new Bigtricks_Deals_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Bigtricks_Deals_Admin( $this->get_plugin_name(), $this->get_version() );

	$this->loader->add_action( 'init', $plugin_admin, 'register_deal_cpt' );
	$this->loader->add_action( 'init', $plugin_admin, 'register_store_taxonomy' );
	$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_deal_meta_box' );
	$this->loader->add_action( 'save_post_deal', $plugin_admin, 'save_deal_meta_data' );
	$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_rest_fields' );
	$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_media_library' );
	$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_import_menu' );
	$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_menu' );

	$this->loader->add_action( 'wp_ajax_load_more_content', $plugin_admin, 'load_more_content_callback' );
	$this->loader->add_action( 'wp_ajax_nopriv_load_more_content', $plugin_admin, 'load_more_content_callback' );
	$this->loader->add_action( 'wp_ajax_bt_get_similar_deals', $plugin_admin, 'get_similar_deals_callback' );
	$this->loader->add_action( 'wp_ajax_nopriv_bt_get_similar_deals', $plugin_admin, 'get_similar_deals_callback' );
	$this->loader->add_action( 'wp_ajax_bt_get_post_store', $plugin_admin, 'get_post_store_callback' );
	$this->loader->add_action( 'wp_ajax_nopriv_bt_get_post_store', $plugin_admin, 'get_post_store_callback' );
	$this->loader->add_action( 'wp_ajax_bt_track_event', $plugin_admin, 'track_event_callback' );
	$this->loader->add_action( 'wp_ajax_nopriv_bt_track_event', $plugin_admin, 'track_event_callback' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Bigtricks_Deals_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'add_shortcodes' );
		$this->loader->add_filter( 'single_template', $plugin_public, 'load_single_deal_template' );

		$this->loader->add_action( 'wp_ajax_load_more_deals', $plugin_public, 'load_more_deals_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_nopriv_load_more_deals', $plugin_public, 'load_more_deals_ajax_handler' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bigtricks_Deals_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
