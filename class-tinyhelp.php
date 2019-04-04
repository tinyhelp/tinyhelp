<?php
if ( ! class_exists( 'TinyHelp' ) ) {
	class TinyHelp {
		protected $slug      = 'slug';
		protected $args      = array();
		protected $modules   = array();
		protected $dismissed = array();


		public function __construct( $args ) {
			if ( ! apply_filters( 'tinyhelp_show', true ) ) {
				return false;
			}
			if ( ! apply_filters( "tinyhelp_{$this->slug}_show", true ) ) {
				return false;
			}

			$this->slug      = $args['slug'];
			$this->args      = $args;
			$this->modules   = $args['modules'];
			$this->dismissed = get_option( "tinyhelp_{$this->slug}_dismissed", $this->dismissed );

			add_action( 'current_screen', array( $this, 'start' ) );
			add_action( 'rest_api_init', array( $this, 'api' ) );
		}


		public function start( $screen ) {
			if ( 'plugin-install' !== $screen->base ) {
				return false;
			}
			if ( isset( $_GET['paged'] ) && 1 !== $_GET['paged'] ) {
				return false;
			}
			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
			add_filter( 'plugins_api_result', array( $this, 'inject' ), 100, 3 );
			add_filter( 'self_admin_url', array( $this, 'details' ) );
			add_filter( 'plugin_install_action_links', array( $this, 'links' ), 10, 2 );
		}

		public function assets() {
			wp_register_script(
				'tinyhelp',
				plugins_url( 'tinyhelp.js', __FILE__ ),
				array( 'jquery' ),
				null,
				true
			);
			$data = array(
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'base_rest_url' => rest_url( "/tinyhelp/v1/modules/{$this->slug}" ),
				'bottom_text'   => $this->args['bottom_text'],
			);
			wp_localize_script( 'tinyhelp', 'TinyHelp_Data', $data );
			wp_enqueue_script( 'tinyhelp' );

			wp_register_style(
				'tinyhelp',
				plugins_url( 'tinyhelp.css', __FILE__ )
			);
			wp_enqueue_style( 'tinyhelp' );
		}

		public function inject( $result, $action, $args ) {
			if ( ! property_exists( $args, 'search' ) ) {
				return $result;
			}
			$modules = $this->filter( $this->modules, $args->search );
			foreach ( $modules as $module => $data ) {
				$inject = $this->prepare( $module );
				array_unshift( $result->plugins, $inject );
			}
			return $result;
		}

		public function details( $link ) {
			foreach ( $this->modules as $module => $data ) {
				$link = str_replace( "plugin={$module}", "plugin={$this->slug}&module={$module}", $link );
			}
			return $link;
		}

		public function links( $links, $plugin ) {
			if ( ! isset( $plugin['tinyhelp'] ) || ! $plugin['tinyhelp'] ) {
				return $links;
			}
			if ( ! in_array( $plugin['module'], array_keys( $this->modules ), true ) ) {
				return $links;
			}

			remove_filter( 'self_admin_url', array( $this, 'details' ) );

			$links       = array();
			$all_links   = $this->modules[ $plugin['module'] ]['links'];
			$all_links[] = $this->generate_dismiss_link();
			foreach ( $all_links as $link ) {
				$defaults   = array(
					'type'       => 'link',
					'link'       => '',
					'title'      => '',
					'attributes' => array(),
				);
				$link       = wp_parse_args( $link, $defaults );
				$attributes = $link['attributes'];
				if ( $link['link'] ) {
					$attributes['href'] = $link['link'];
				}
				$attributes['class'] = 'tinyhelp-action-link';
				if ( 'button' === $link['type'] ) {
					$attributes['class'] .= ' button tinyhelp-button';
				}
				if ( 'dismiss' === $link['type'] ) {
					$attributes['class']      .= ' tinyhelp-dismiss';
					$attributes['data-module'] = $plugin['module'];
				}
				foreach ( $attributes as $key => $value ) {
					$attributes[ $key ] = $key . '="' . esc_attr( $value ) . '"';
				}
				$attributes = implode( ' ', $attributes );
				$links[]    = '<a ' . $attributes . '>' . esc_html( $link['title'] ) . '</a>';
			}
			return $links;
		}


		public function api() {
			register_rest_route(
				'tinyhelp/v1',
				"modules/{$this->slug}",
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'api_callback' ),
					'permission_callback' => array( $this, 'api_permission' ),
					'args'                => array(
						'module' => array(
							'default'           => '',
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => array( $this, 'api_validate' ),
						),
					),
				)
			);
		}

		public function api_callback( WP_REST_Request $request ) {
			return $this->dismiss( $request['module'] )
				? rest_ensure_response( array( 'code' => 'success' ) )
				: new WP_Error( 'not_dismissed', esc_html__( 'The card could not be dismissed', 'tinyhelp' ), array( 'status' => 400 ) );
		}

		public function api_permission() {
			return current_user_can( 'manage_options' );
		}

		public function api_validate( $value, $request, $param ) {
			return isset( $this->modules[ $value ] );
		}



		protected function dismiss( $module ) {
			if ( in_array( $module, $this->dismissed, true ) ) {
				return true;
			}
			$this->dismissed[] = $module;
			return update_option( "tinyhelp_{$this->slug}_dismissed", $this->dismissed );
		}

		protected function filter( $modules, $search ) {
			foreach ( $this->dismissed as $module ) {
				unset( $modules[ $module ] );
			}
			foreach ( $modules as $key => $item ) {
				$match = false;
				foreach ( $item['search_terms'] as $term ) {
					$term = "/{$term}/ims";
					if ( 1 === preg_match( $term, $search ) ) {
						$match = true;
					}
				}
				if ( ! $match ) {
					unset( $modules[ $key ] );
				}
			}
			return $modules;
		}

		protected function prepare( $module ) {
			if ( ! isset( $this->modules[ $module ] ) ) {
				return false;
			}

			$inject                      = $this->modules[ $module ];
			$defaults                    = array();
			$defaults['tinyhelp']        = true;
			$defaults['slug']            = 'tinyhelp';
			$defaults['plugin']          = $this->slug;
			$defaults['module']          = $module;
			$defaults['num_ratings']     = 0;
			$defaults['rating']          = 0;
			$defaults['last_updated']    = 0;
			$defaults['active_installs'] = 0;
			$defaults['author']          = "<a href='{$inject['author_uri']}' target='_blank'>{$inject['author_name']}</a>";
			$inject                      = wp_parse_args( $inject, $defaults );

			unset( $inject['search_terms'] );
			unset( $inject['author_uri'] );
			unset( $inject['author_name'] );

			return $inject;
		}

		protected function generate_dismiss_link() {
			return array(
				'type'  => 'dismiss',
				'title' => __( 'Hide this suggestion', 'tinyhelp' ),
				'link'  => '',
			);
		}
	}
}
