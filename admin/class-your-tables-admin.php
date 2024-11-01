<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Your_Tables_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * @var Your_Tables_Menus
	 */
	private $menus;
	/**
	 * @var Your_Tables_Shared
	 */
	private $shared;
	/**
	 * @var Your_Tables_View
	 */
	private $view;
	/**
	 * @var Your_Tables_Model
	 */
	private $model;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependencies();
		$this->shared = new Your_Tables_Shared();
		$this->menus  = new Your_Tables_Menus( $this->shared );
		$this->model  = new Your_Tables_Model( $this->shared );
		$this->view   = new Your_Tables_View( $this->shared );
	}

	/**
	 *
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-your-tables-shared.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-your-tables-menus.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-your-tables-view.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-your-tables-form.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-your-tables-model.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 */
	public function hook_menus() {

		$this->menus->hook_menus( $this );

	}

	/**
	 * @param $option is received from the WordPress hook and contains the name of the option that is being changed
	 */
	public function updated_option( $option ) {
		//when changes are made to the settings, the new capabilities must be activated
		if ( $option == 'your_tables_users' || $option == 'your_tables_admins' ) {
			$this->shared->RegisterSettings();
			$this->model->AssignCapabilities();
		}
	}

	/**
	 * Get the settings from the WordPress database
	 */
	public function register_settings() {
		$this->shared->RegisterSettings();
	}

	/**
	 * Loading the CSS
	 */
	public function enqueue_styles() {

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/your-tables-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		if (!wp_style_is('dashicons')) $this->shared->dashIconsPresent=false;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'jquery-ui-datepicker' );
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/your-tables-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Controller function to control all flow in the WordPress Admin area
	 */
	public function your_tables_admin_page() {
		// $action represents the requested action
		$action = 'show_table';
		if ( isset( $_REQUEST['wp_your_tables__action'] ) ) {
			$action = $_REQUEST['wp_your_tables__action'];
		}
		// $usedButton contains the value of the submit button which was pressed
		$usedButton = 'save';
		if ( isset( $_REQUEST['wp_your_tables__whichbutton'] ) ) {
			$usedButton = $_REQUEST['wp_your_tables__whichbutton'];
		}
		// $table contains the name of the table we are using now
		$table = 'wp_your_tables';
		if ( isset( $_REQUEST['wp_your_tables__table'] ) ) {
			$table = $_REQUEST['wp_your_tables__table'];
		}
		switch ( $action ) {
			case 'show_table':
				$this->view->DisplayTable( $table );
				break;
			case 'deleteitem':
				$this->model->DeleteItem();
				$this->view->DisplayTable( $table );
				break;
			case 'createitem':
				if ( $usedButton == 'cancel' ) {
					$this->view->DisplayTable( $table );
				} elseif ( $usedButton == 'createandnew' ) {
					$this->model->CreateItem();
					$_REQUEST['wp_your_tables__action'] = 'newform';
					$this->view->DisplayForm();
				} elseif ( $usedButton == 'ready' ) {
					$this->model->CreateItem();
					$this->view->DisplayTable( $table );
				} else {
					$this->model->CreateItem();
					$_REQUEST['wp_your_tables__action'] = 'editform';
					$this->view->DisplayForm();
				}
				break;
			case 'saveitem':
				if ( $usedButton == 'cancel' ) {
					$this->view->DisplayTable( $table );
				} elseif ( $usedButton == 'ready' ) {
					$this->model->SaveItem();
					$this->view->DisplayTable( $table );
				} else {
					$this->model->SaveItem();
					$_REQUEST['wp_your_tables__action'] = 'editform';
					$this->view->DisplayForm();
				}
				break;
			case 'editform':
				$this->view->DisplayForm();
				break;
			case 'newform':
				$this->view->DisplayForm();
				break;
		}
	}

	/**
	 * WordPress hook for displaying the settings form
	 */
	public function your_tables_admin_settings_page() {

		$this->view->DisplaySettings();
	}


	/**
	 * This function handles the request directly from the WordPress menu
	 * WordPress wants to hard-call a function based on the GET parameters
	 */
	function __call( $func, $params ) {

		$this->view->DisplayTable( $func );
	}

}
