<?php
/**
 * Plugin Name: PowerComment
 * Plugin URI: https://github.com/claudiosmweb/powercomment
 * Description: Validate the comments of your blog using jQuery, avoiding fields are left blank or filled in with invalid.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 2.1.2
 * License: GPLv2 or later
 * Text Domain: powercomment
 * Domain Path: /languages/
 */

class Power_Comment {

	/**
	 * Class construct.
	 */
	public function __construct() {

		// Load textdomain.
		add_action( 'plugins_loaded', array( $this, 'languages' ), 0 );

		// Adds admin menu.
		add_action( 'admin_menu', array( $this, 'menu' ) );

		// Init plugin options form.
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );

		// Admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Front-end scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'front_end_scripts' ), 999 );

		// Install default settings.
		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function languages() {
		load_plugin_textdomain( 'powercomment', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load admin scripts.
	 *
	 * @return void
	 */
	public function admin_scripts( $hook_suffix ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'powercomment' ) {

			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_script(
				'powercomment-admin',
				plugins_url( '/js/powercomment-admin.min.js', __FILE__ ),
				array( 'wp-color-picker' ),
				false,
				true
			);

		}
	}

	/**
	 * Sets default settings.
	 *
	 * @return array Plugin default settings.
	 */
	protected function default_settings() {

		$settings = array(
			'notifications' => array(
				'title' => __( 'Notifications', 'powercomment' ),
				'type' => 'section'
			),
			'author' => array(
				'title' => __( 'Author', 'powercomment' ),
				'default' => __( 'Please fill in your name.', 'powercomment' ),
				'type' => 'text',
				'section' => 'notifications'
			),
			'email' => array(
				'title' => __( 'Email', 'powercomment' ),
				'default' => __( 'Enter a valid email address.', 'powercomment' ),
				'type' => 'text',
				'section' => 'notifications'
			),
			'url' => array(
				'title' => __( 'URL', 'powercomment' ),
				'default' => __( 'Please use a valid website address (use http://).', 'powercomment' ),
				'type' => 'text',
				'section' => 'notifications'
			),
			'comment' => array(
				'title' => __( 'Comment', 'powercomment' ),
				'default' => __( 'The comment should be at least 10 characters.', 'powercomment' ),
				'type' => 'text',
				'section' => 'notifications'
			),
			'settings' => array(
				'title' => __( 'Settings', 'powercomment' ),
				'type' => 'section'
			),
			'comment_limit' => array(
				'title' => __( 'Comment Settings', 'powercomment' ),
				'default' => 10,
				'type' => 'text',
				'description' => __( 'Minimum number of characters that a comment may have.', 'powercomment' ),
				'section' => 'settings'
			),
			'design' => array(
				'title' => __( 'Design', 'powercomment' ),
				'type' => 'section'
			),
			'background_color' => array(
				'title' => __( 'Background Color', 'powercomment' ),
				'default' => '#ffd2d2',
				'type' => 'color',
				'section' => 'design'
			),
			'border_color' => array(
				'title' => __( 'Border Color', 'powercomment' ),
				'default' => '#cc0000',
				'type' => 'color',
				'section' => 'design'
			),
			'text_color' => array(
				'title' => __( 'Text Color', 'powercomment' ),
				'default' => '#000000',
				'type' => 'color',
				'section' => 'design'
			),
		);

		return $settings;
	}

	/**
	 * Installs default settings on plugin activation.
	 *
	 * @return void
	 */
	public function install() {
		$options = array();

		foreach ( $this->default_settings() as $key => $value ) {
			if ( 'section' != $value['type'] ) {
				$options[ $key ] = $value['default'];
			}
		}

		add_option( 'powercmm_settings', $options );
	}

	/**
	 * Update plugin settings.
	 *
	 * @return void
	 */
	public function update() {
		if ( ! get_option( 'powercmm_settings' ) && get_option( 'powerc_author' ) ) {

			$options = array(
				'author' => get_option( 'powerc_author' ),
				'email' => get_option( 'powerc_email' ),
				'url' => get_option( 'powerc_url' ),
				'comment' => get_option( 'powerc_comment' ),
				'comment_limit' => get_option( 'powerc_comment_limite' ),
				'background_color' => get_option( 'powerc_notf_bg' ),
				'border_color' => get_option( 'powerc_notf_border' ),
				'text_color' => get_option( 'powerc_notf_font' )
			);

			// Updates options
			update_option( 'powercmm_settings', $options );

			// Removes old options.
			delete_option( 'powerc_author' );
			delete_option( 'powerc_email' );
			delete_option( 'powerc_url' );
			delete_option( 'powerc_comment' );
			delete_option( 'powerc_comment_limite' );
			delete_option( 'powerc_notf_bg' );
			delete_option( 'powerc_notf_border' );
			delete_option( 'powerc_notf_font' );

		} elseif ( ! get_option( 'powercmm_settings' ) ) {
			// Install default options.
			$this->install();
		}
	}

	/**
	 * Add plugin settings menu.
	 *
	 * @return void
	 */
	public function menu() {
		add_options_page(
			__( 'PowerComment', 'powercomment' ),
			__( 'PowerComment', 'powercomment' ),
			'manage_options',
			'powercomment',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Plugin settings page.
	 *
	 * @return string Settings page HTML.
	 */
	public function settings_page() {
		?>

		<div class="wrap">
			<h2><?php _e( 'PowerComment Settings', 'powercomment' ); ?></h2>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'powercmm_settings' );
					do_settings_sections( 'powercmm_settings' );
					submit_button();
				?>
			</form>

		</div>

		<?php
	}

	/**
	 * Plugin settings form fields.
	 *
	 * @return void
	 */
	public function plugin_settings() {
		$option = 'powercmm_settings';

		// Create option in wp_options.
		if ( false == get_option( $option ) ) {
			$this->update();
		}

		foreach ( $this->default_settings() as $key => $value ) {

			switch ( $value['type'] ) {
				case 'section':
					add_settings_section(
						$key,
						$value['title'],
						'__return_false',
						$option
					);
					break;
				case 'text':
					add_settings_field(
						$key,
						$value['title'],
						array( $this , 'text_element_callback' ),
						$option,
						$value['section'],
						array(
							'menu' => $option,
							'id' => $key,
							'default' => $value['default'],
							'class' => 'regular-text',
							'description' => isset( $value['description'] ) ? $value['description'] : ''
						)
					);
					break;
				case 'color':
					add_settings_field(
						$key,
						$value['title'],
						array( $this , 'color_element_callback' ),
						$option,
						$value['section'],
						array(
							'menu' => $option,
							'id' => $key,
							'default' => $value['default'],
							'description' => isset( $value['description'] ) ? $value['description'] : ''
						)
					);
					break;

				default:
					break;
			}

		}

		// Register settings.
		register_setting( $option, $option, array( $this, 'validate_options' ) );
	}

	/**
	 * Text element fallback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string      Text field.
	 */
	public function text_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$class = isset( $args['class'] ) ? $args['class'] : 'small-text';

		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$current = $options[ $id ];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="%4$s" />', $id, $menu, $current, $class );

		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Color element fallback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string      Color field.
	 */
	function color_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];

		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$current = $options[ $id ];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '#ffffff';
		}

		$html = sprintf( '<input type="text" id="color-%1$s" name="%2$s[%1$s]" value="%3$s" class="powercmm-color-field" />', $id, $menu, $current );

		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Valid options.
	 *
	 * @param  array $input Options to valid.
	 *
	 * @return array        Validated options.
	 */
	public function validate_options( $input ) {
		$output = array();

		foreach ( $input as $key => $value ) {
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		return $output;
	}

	/**
	 * Register front-end scripts.
	 *
	 * @return void
	 */
	public function front_end_scripts() {
		if ( is_single() || is_page() ) {
			$options = get_option('powercmm_settings');

			wp_enqueue_script(
				'powercomment',
				plugins_url( '/js/powercomment.min.js', __FILE__ ),
				array( 'jquery' ),
				false,
				true
			);

			wp_localize_script(
				'powercomment',
				'powercomment_params',
				array(
					'comment_limit' => $options['comment_limit'],
					'author'        => $options['author'],
					'email'         => $options['email'],
					'url'           => $options['url'],
					'comment'       => $options['comment'],
					'text'          => $options['text_color'],
					'background'    => $options['background_color'],
					'border'        => $options['border_color'],
				)
			);

		}
	}

} // close Power_Comment class.

new Power_Comment();
