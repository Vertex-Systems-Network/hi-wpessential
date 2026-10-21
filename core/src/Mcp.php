<?php
/**
 * WPEssential MCP server: lets an AI assistant (Claude, Cursor, VS Code, Gemini CLI…) work on
 * this site through the Model Context Protocol, the same way the Elementor MCP does.
 *
 * Transport: MCP "Streamable HTTP" over a REST route, answering JSON (no streaming).
 * Auth: a WordPress Application Password of an administrator, sent as HTTP Basic auth.
 * The server is off until an administrator switches it on under WPEssential → AI Connect.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * MCP server and its admin screen.
 */
class Mcp {

	const OPTION   = 'wpessential_mcp';
	const ROUTE    = '/mcp';
	const PROTOCOL = '2025-06-18';
	const APP_NAME = 'WPEssential AI Connect';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_wpessential_mcp', array( __CLASS__, 'save' ) );
	}

	/**
	 * Whether the server answers.
	 *
	 * @return bool
	 */
	public static function enabled() {
		return ! empty( get_option( self::OPTION, array() )['enabled'] );
	}

	/**
	 * Endpoint URL.
	 *
	 * @return string
	 */
	public static function url() {
		return rest_url( Rest::NS . self::ROUTE );
	}

	/**
	 * REST route.
	 */
	public static function routes() {
		register_rest_route(
			Rest::NS,
			self::ROUTE,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'handle' ),
					'permission_callback' => array( __CLASS__, 'allowed' ),
				),
				array(
					// Clients probe GET for a server-sent event stream; this server has none.
					'methods'             => 'GET',
					'callback'            => static function () {
						return new \WP_REST_Response( null, 405 );
					},
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Only administrators, and only while switched on.
	 *
	 * @return bool|\WP_Error
	 */
	public static function allowed() {
		if ( ! self::enabled() ) {
			return new \WP_Error( 'wpe_mcp_off', __( 'The WPEssential MCP server is switched off. Turn it on under WPEssential → AI Connect.', 'hi-wpessential' ), array( 'status' => 403 ) );
		}
		return current_user_can( 'manage_options' );
	}

	/**
	 * JSON-RPC entry point (single message or batch).
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public static function handle( $request ) {
		$body = json_decode( (string) $request->get_body(), true );
		if ( ! is_array( $body ) ) {
			return new \WP_REST_Response( self::error( null, -32700, 'Parse error' ), 200 );
		}
		$batch = isset( $body[0] );
		$out   = array();
		foreach ( $batch ? $body : array( $body ) as $message ) {
			$reply = self::dispatch( (array) $message );
			if ( null !== $reply ) {
				$out[] = $reply;
			}
		}
		if ( ! $out ) {
			return new \WP_REST_Response( null, 202 ); // Notifications only.
		}
		return new \WP_REST_Response( $batch ? $out : $out[0], 200 );
	}

	/**
	 * One JSON-RPC message.
	 *
	 * @param array $m Message.
	 * @return array|null Reply, or null for notifications.
	 */
	private static function dispatch( array $m ) {
		$id     = $m['id'] ?? null;
		$method = (string) ( $m['method'] ?? '' );
		$params = (array) ( $m['params'] ?? array() );
		if ( ! array_key_exists( 'id', $m ) ) {
			return null;
		}
		switch ( $method ) {
			case 'initialize':
				return self::result(
					$id,
					array(
						'protocolVersion' => self::PROTOCOL,
						'capabilities'    => array( 'tools' => array( 'listChanged' => false ) ),
						'serverInfo'      => array(
							'name'    => 'wpessential',
							'title'   => 'WPEssential',
							'version' => defined( 'WPESSENTIAL_CORE_VERSION' ) ? WPESSENTIAL_CORE_VERSION : '1.0.0',
						),
						'instructions'    => self::instructions(),
					)
				);
			case 'ping':
				return self::result( $id, new \stdClass() );
			case 'tools/list':
				return self::result( $id, array( 'tools' => array_values( array_map( static fn( $t ) => array_diff_key( $t, array( 'run' => 0 ) ), self::tools() ) ) ) );
			case 'tools/call':
				return self::call( $id, (string) ( $params['name'] ?? '' ), (array) ( $params['arguments'] ?? array() ) );
		}
		return self::error( $id, -32601, 'Method not found: ' . $method );
	}

	/**
	 * Run a tool.
	 *
	 * @param mixed  $id   Request id.
	 * @param string $name Tool.
	 * @param array  $args Arguments.
	 * @return array
	 */
	private static function call( $id, $name, array $args ) {
		$tools = self::tools();
		if ( ! isset( $tools[ $name ] ) ) {
			return self::error( $id, -32602, 'Unknown tool: ' . $name );
		}
		try {
			$data = call_user_func( $tools[ $name ]['run'], $args );
		} catch ( \Throwable $e ) {
			$data = new \WP_Error( 'wpe_mcp_tool', $e->getMessage() );
		}
		$error = is_wp_error( $data );
		$text  = $error ? $data->get_error_message() : wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return self::result(
			$id,
			array(
				'content' => array(
					array(
						'type' => 'text',
						'text' => (string) $text,
					),
				),
				'isError' => $error,
			)
		);
	}

	/**
	 * Notes the assistant receives on connect.
	 *
	 * @return string
	 */
	private static function instructions() {
		return 'You are connected to the WordPress site "' . get_bloginfo( 'name' ) . '" (' . home_url( '/' ) . ') running the Hi WPEssential block theme. '
			. 'Pages and posts are block markup (Gutenberg). Use site_info first. Prefer theme patterns and the theme.json palette (slugs: primary, secondary, ink, surface, surface-alt, text, text-muted). '
			. 'wp_rest calls any WordPress REST route as the connected administrator (e.g. /wp/v2/pages, /wp/v2/templates, /wp/v2/global-styles, /wc/v3/products, /wpforms routes when available). '
			. 'Use create_form for forms (WPForms), import_demo for one-click demos. Ask before deleting content.';
	}

	/**
	 * Tool definitions: name => array( description, inputSchema, run ).
	 *
	 * @return array
	 */
	private static function tools() {
		$obj  = static function ( array $props, array $required = array() ) {
			return array_filter(
				array(
					'type'       => 'object',
					'properties' => $props ? $props : new \stdClass(),
					'required'   => $required,
				)
			);
		};
		$str  = static fn( $d ) => array(
			'type'        => 'string',
			'description' => $d,
		);
		$int  = static fn( $d ) => array(
			'type'        => 'integer',
			'description' => $d,
		);
		$list = array(
			'site_info'      => array(
				'description' => 'Site name, URL, WordPress and theme versions, active plugins, front page, menus and the theme style variations.',
				'inputSchema' => $obj( array() ),
				'run'         => array( __CLASS__, 'tool_site_info' ),
			),
			'wp_rest'        => array(
				'description' => 'Call any WordPress REST API route on this site as the connected administrator. Example: {"method":"POST","path":"/wp/v2/pages","body":{"title":"About","status":"publish","content":"<!-- wp:paragraph --><p>Hi</p><!-- /wp:paragraph -->"}}.',
				'inputSchema' => $obj(
					array(
						'method' => array(
							'type' => 'string',
							'enum' => array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ),
						),
						'path'   => $str( 'Route, e.g. /wp/v2/pages?per_page=20' ),
						'body'   => array(
							'type'        => 'object',
							'description' => 'JSON body for POST/PUT/PATCH.',
						),
					),
					array( 'method', 'path' )
				),
				'run'         => array( __CLASS__, 'tool_rest' ),
			),
			'list_content'   => array(
				'description' => 'List posts, pages, products or any post type with id, title, status, slug and link.',
				'inputSchema' => $obj(
					array(
						'post_type' => $str( 'post, page, product, wp_template, wp_template_part… Default page.' ),
						'search'    => $str( 'Optional search words.' ),
						'per_page'  => $int( 'Default 50.' ),
					)
				),
				'run'         => array( __CLASS__, 'tool_list' ),
			),
			'get_content'    => array(
				'description' => 'Full block markup and details of one post, page or product.',
				'inputSchema' => $obj( array( 'id' => $int( 'Post ID.' ) ), array( 'id' ) ),
				'run'         => array( __CLASS__, 'tool_get' ),
			),
			'save_content'   => array(
				'description' => 'Create (no id) or update a post, page or other post type with block markup.',
				'inputSchema' => $obj(
					array(
						'id'        => $int( 'Existing post ID to update.' ),
						'post_type' => $str( 'Default page.' ),
						'title'     => $str( 'Title.' ),
						'content'   => $str( 'Block markup.' ),
						'status'    => $str( 'publish, draft, private.' ),
						'slug'      => $str( 'URL slug.' ),
					)
				),
				'run'         => array( __CLASS__, 'tool_save' ),
			),
			'install_plugin' => array(
				'description' => 'Install (from WordPress.org) and activate a plugin by slug, e.g. wpforms-lite, woocommerce.',
				'inputSchema' => $obj( array( 'slug' => $str( 'WordPress.org plugin slug.' ) ), array( 'slug' ) ),
				'run'         => array( __CLASS__, 'tool_plugin' ),
			),
			'create_form'    => array(
				'description' => 'Create a WPForms form (installs WPForms Lite if missing). Fields: [{"type":"name|email|text|textarea|select|radio|checkbox|number|phone|url","label":"…","required":true,"choices":["A","B"]}]. Returns the form id and its shortcode.',
				'inputSchema' => $obj(
					array(
						'title'   => $str( 'Form title.' ),
						'fields'  => array(
							'type'  => 'array',
							'items' => array( 'type' => 'object' ),
						),
						'submit'  => $str( 'Submit button text.' ),
						'message' => $str( 'Confirmation message.' ),
					),
					array( 'title', 'fields' )
				),
				'run'         => array( __CLASS__, 'tool_form' ),
			),
			'list_demos'     => array(
				'description' => 'Demos available in the WPEssential library (id, title, builders, free or pro).',
				'inputSchema' => $obj( array() ),
				'run'         => array( __CLASS__, 'tool_demos' ),
			),
			'import_demo'    => array(
				'description' => 'Import a WPEssential demo (pages, products, menus, images, style). Replaces the previous demo\'s content.',
				'inputSchema' => $obj(
					array(
						'demo'    => $str( 'Demo id from list_demos.' ),
						'builder' => $str( 'gutenberg or elementor.' ),
					),
					array( 'demo', 'builder' )
				),
				'run'         => array( __CLASS__, 'tool_import' ),
			),
		);
		$list = (array) apply_filters( 'wpessential_mcp_tools', $list );
		foreach ( $list as $name => $tool ) {
			$list[ $name ]['name'] = $name;
		}
		return $list;
	}

	// Tools.

	/**
	 * Site overview.
	 *
	 * @return array
	 */
	public static function tool_site_info() {
		global $wp_version;
		$theme = wp_get_theme();
		$menus = get_posts(
			array(
				'post_type'   => 'wp_navigation',
				'numberposts' => 20,
			)
		);
		return array(
			'name'             => get_bloginfo( 'name' ),
			'tagline'          => get_bloginfo( 'description' ),
			'url'              => home_url( '/' ),
			'wordpress'        => $wp_version,
			'theme'            => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
			'style_variations' => array_map( static fn( $f ) => basename( $f, '.json' ), (array) glob( get_template_directory() . '/styles/*.json' ) ),
			'front_page'       => (int) get_option( 'page_on_front' ),
			'posts_page'       => (int) get_option( 'page_for_posts' ),
			'active_plugins'   => array_values( (array) get_option( 'active_plugins', array() ) ),
			'navigation_menus' => array_map(
				static fn( $p ) => array(
					'id'    => $p->ID,
					'title' => $p->post_title,
				),
				$menus
			),
			'woocommerce'      => class_exists( 'WooCommerce' ),
			'wpforms'          => function_exists( 'wpforms' ),
		);
	}

	/**
	 * Internal REST request.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_rest( array $a ) {
		$path  = '/' . ltrim( (string) ( $a['path'] ?? '' ), '/' );
		$parts = wp_parse_url( $path );
		$req   = new \WP_REST_Request( strtoupper( (string) ( $a['method'] ?? 'GET' ) ), $parts['path'] ?? '/' );
		if ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query );
			$req->set_query_params( $query );
		}
		if ( ! empty( $a['body'] ) && is_array( $a['body'] ) ) {
			$req->set_header( 'content-type', 'application/json' );
			$req->set_body( wp_json_encode( $a['body'] ) );
			$req->set_body_params( $a['body'] );
		}
		$res = rest_do_request( $req );
		return array(
			'status' => $res->get_status(),
			'data'   => rest_get_server()->response_to_data( $res, false ),
		);
	}

	/**
	 * List content.
	 *
	 * @param array $a Arguments.
	 * @return array
	 */
	public static function tool_list( array $a ) {
		$posts = get_posts(
			array(
				'post_type'   => sanitize_key( $a['post_type'] ?? 'page' ),
				'post_status' => 'any',
				's'           => (string) ( $a['search'] ?? '' ),
				'numberposts' => min( 200, (int) ( $a['per_page'] ?? 50 ) ),
			)
		);
		return array_map(
			static fn( $p ) => array(
				'id'     => $p->ID,
				'title'  => $p->post_title,
				'status' => $p->post_status,
				'slug'   => $p->post_name,
				'link'   => get_permalink( $p ),
			),
			$posts
		);
	}

	/**
	 * One post.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_get( array $a ) {
		$p = get_post( (int) ( $a['id'] ?? 0 ) );
		if ( ! $p ) {
			return new \WP_Error( 'wpe_mcp_404', 'Not found.' );
		}
		return array(
			'id'        => $p->ID,
			'post_type' => $p->post_type,
			'title'     => $p->post_title,
			'status'    => $p->post_status,
			'slug'      => $p->post_name,
			'link'      => get_permalink( $p ),
			'template'  => get_page_template_slug( $p ),
			'content'   => $p->post_content,
		);
	}

	/**
	 * Create or update.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_save( array $a ) {
		$data = array_filter(
			array(
				'ID'           => (int) ( $a['id'] ?? 0 ),
				'post_type'    => empty( $a['id'] ) ? sanitize_key( $a['post_type'] ?? 'page' ) : null,
				'post_title'   => isset( $a['title'] ) ? sanitize_text_field( $a['title'] ) : null,
				'post_content' => $a['content'] ?? null,
				'post_status'  => isset( $a['status'] ) ? sanitize_key( $a['status'] ) : ( empty( $a['id'] ) ? 'draft' : null ),
				'post_name'    => isset( $a['slug'] ) ? sanitize_title( $a['slug'] ) : null,
			),
			static fn( $v ) => null !== $v && 0 !== $v
		);
		$id   = empty( $data['ID'] ) ? wp_insert_post( wp_slash( $data ), true ) : wp_update_post( wp_slash( $data ), true );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		return array(
			'id'   => $id,
			'link' => get_permalink( $id ),
		);
	}

	/**
	 * Install and activate a plugin.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_plugin( array $a ) {
		$slug = sanitize_key( $a['slug'] ?? '' );
		$res  = Plugins::apply(
			array(
				'slug'   => $slug,
				'name'   => $slug,
				'source' => 'wporg',
				'state'  => 'required',
			)
		);
		return is_wp_error( $res ) ? $res : array( 'active' => $slug );
	}

	/**
	 * Create a WPForms form.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_form( array $a ) {
		$id = Forms::create( (string) ( $a['title'] ?? 'Form' ), (array) ( $a['fields'] ?? array() ), (string) ( $a['submit'] ?? '' ), (string) ( $a['message'] ?? '' ) );
		return is_wp_error( $id ) ? $id : array(
			'id'        => $id,
			'shortcode' => '[wpforms id="' . $id . '"]',
			'block'     => '<!-- wp:wpforms/form-selector {"formId":"' . $id . '"} /-->',
		);
	}

	/**
	 * Demo list.
	 *
	 * @return array
	 */
	public static function tool_demos() {
		return array_map(
			static fn( $d ) => array(
				'id'       => $d['id'],
				'title'    => $d['title'] ?? $d['id'],
				'type'     => $d['type'] ?? 'free',
				'builders' => array_keys( (array) ( $d['builders'] ?? array() ) ),
				'locked'   => ! empty( $d['locked'] ),
			),
			Demos::all()
		);
	}

	/**
	 * Import a demo to the end.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_import( array $a ) {
		$job = Importer::start(
			sanitize_key( $a['demo'] ?? '' ),
			sanitize_key( $a['builder'] ?? 'gutenberg' ),
			array(
				'plugins'  => 1,
				'media'    => 1,
				'content'  => 1,
				'products' => 1,
				'settings' => 1,
				'reset'    => 1,
			)
		);
		if ( is_wp_error( $job ) ) {
			return $job;
		}
		$state = array();
		for ( $i = 0; $i < 400; $i++ ) {
			$state = Importer::run();
			if ( is_wp_error( $state ) || 'running' !== ( $state['status'] ?? '' ) ) {
				break;
			}
		}
		return is_wp_error( $state ) ? $state : array(
			'status'     => $state['status'] ?? '',
			'front_page' => home_url( '/' ),
		);
	}

	// JSON-RPC helpers.

	/**
	 * Result envelope.
	 *
	 * @param mixed $id     Id.
	 * @param mixed $result Result.
	 * @return array
	 */
	private static function result( $id, $result ) {
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => $result,
		);
	}

	/**
	 * Error envelope.
	 *
	 * @param mixed  $id      Id.
	 * @param int    $code    Code.
	 * @param string $message Message.
	 * @return array
	 */
	private static function error( $id, $code, $message ) {
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => array(
				'code'    => $code,
				'message' => $message,
			),
		);
	}

	// Admin screen.

	/**
	 * Submenu.
	 */
	public static function menu() {
		$hook = add_submenu_page( Admin::SLUG, __( 'AI Connect (MCP)', 'hi-wpessential' ), __( 'AI Connect', 'hi-wpessential' ), 'manage_options', 'wpessential-mcp', array( __CLASS__, 'screen' ) );
		if ( $hook ) {
			add_action( 'admin_print_styles-' . $hook, array( __CLASS__, 'assets' ) );
			add_filter(
				'admin_body_class',
				static function ( $classes ) use ( $hook ) {
					$screen = get_current_screen();
					return $screen && $hook === $screen->id ? $classes . ' wpe-core-screen' : $classes;
				}
			);
		}
	}

	/**
	 * Dashboard styles on the AI Connect screen.
	 */
	public static function assets() {
		wp_enqueue_style( 'wpessential-core-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts.
		wp_enqueue_style( 'wpessential-core', wpessential_core_url( 'assets/admin.css' ), array(), WPESSENTIAL_CORE_ASSET_VERSION );
	}

	/**
	 * Client keys and labels.
	 *
	 * @return array key => label
	 */
	public static function clients() {
		$out = array();
		foreach ( array_keys( self::snippets( '', '' ) ) as $label ) {
			$out[ sanitize_title( $label ) ] = $label;
		}
		return $out;
	}

	/**
	 * Switch on/off, create or revoke the connection password.
	 */
	public static function save() {
		check_admin_referer( 'wpessential_mcp' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'hi-wpessential' ) );
		}
		$do  = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : '';
		$opt = (array) get_option( self::OPTION, array() );
		$uid = get_current_user_id();
		self::revoke( $uid );
		$opt['enabled'] = 0;
		$client         = isset( $_POST['client'] ) ? sanitize_key( wp_unslash( $_POST['client'] ) ) : '';
		if ( isset( self::clients()[ $client ] ) ) {
			$opt['client'] = $client;
		}
		if ( 'connect' === $do ) {
			$made = \WP_Application_Passwords::create_new_application_password( $uid, array( 'name' => self::APP_NAME ) );
			if ( ! is_wp_error( $made ) ) {
				$opt['enabled'] = 1;
				$opt['user']    = $uid;
				// Shown once on the next screen, never stored in plain text.
				set_transient( 'wpessential_mcp_secret_' . $uid, $made[0], 10 * MINUTE_IN_SECONDS );
			}
		}
		update_option( self::OPTION, $opt, false );
		wp_safe_redirect( admin_url( 'admin.php?page=wpessential-mcp' ) );
		exit;
	}

	/**
	 * Remove this tool's application passwords for a user.
	 *
	 * @param int $uid User.
	 */
	private static function revoke( $uid ) {
		foreach ( \WP_Application_Passwords::get_user_application_passwords( $uid ) as $item ) {
			if ( self::APP_NAME === $item['name'] ) {
				\WP_Application_Passwords::delete_application_password( $uid, $item['uuid'] );
			}
		}
	}

	/**
	 * Client setup snippets for a URL and Basic auth token.
	 *
	 * @param string $url   Endpoint.
	 * @param string $token base64(user:password), or a placeholder.
	 * @return array label => array( where, snippet )
	 */
	public static function snippets( $url, $token ) {
		$header = 'Basic ' . $token;
		$json   = static fn( $v ) => wp_json_encode( $v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		return array(
			'Claude Code'          => array( __( 'Run in a terminal:', 'hi-wpessential' ), 'claude mcp add --transport http wpessential ' . $url . ' --header "Authorization: ' . $header . '"' ),
			'Claude Desktop'       => array(
				__( 'Settings → Developer → Edit Config (claude_desktop_config.json), then restart Claude. Needs Node.js.', 'hi-wpessential' ),
				$json(
					array(
						'mcpServers' => array(
							'wpessential' => array(
								'command' => 'npx',
								'args'    => array( '-y', 'mcp-remote', $url, '--header', 'Authorization:${WPE_AUTH}' ),
								'env'     => array( 'WPE_AUTH' => $header ),
							),
						),
					)
				),
			),
			'Cursor'               => array(
				__( 'Settings → MCP → Add new MCP server, or ~/.cursor/mcp.json:', 'hi-wpessential' ),
				$json(
					array(
						'mcpServers' => array(
							'wpessential' => array(
								'url'     => $url,
								'headers' => array( 'Authorization' => $header ),
							),
						),
					)
				),
			),
			'VS Code (Copilot)'    => array(
				__( 'Command palette → MCP: Add Server → HTTP, or .vscode/mcp.json:', 'hi-wpessential' ),
				$json(
					array(
						'servers' => array(
							'wpessential' => array(
								'type'    => 'http',
								'url'     => $url,
								'headers' => array( 'Authorization' => $header ),
							),
						),
					)
				),
			),
			'Windsurf'             => array(
				__( 'Settings → Cascade → MCP servers → View raw config (mcp_config.json):', 'hi-wpessential' ),
				$json(
					array(
						'mcpServers' => array(
							'wpessential' => array(
								'serverUrl' => $url,
								'headers'   => array( 'Authorization' => $header ),
							),
						),
					)
				),
			),
			'Gemini CLI'           => array(
				__( 'Add to ~/.gemini/settings.json:', 'hi-wpessential' ),
				$json(
					array(
						'mcpServers' => array(
							'wpessential' => array(
								'httpUrl' => $url,
								'headers' => array( 'Authorization' => $header ),
							),
						),
					)
				),
			),
			'Any other MCP client' => array( __( 'Streamable HTTP server. Send this header with every request:', 'hi-wpessential' ), "URL: {$url}\nAuthorization: {$header}" ),
		);
	}

	/**
	 * Admin screen: pick an AI client, generate a code, copy only that client's setup.
	 */
	public static function screen() {
		$on     = self::enabled();
		$uid    = get_current_user_id();
		$secret = get_transient( 'wpessential_mcp_secret_' . $uid );
		delete_transient( 'wpessential_mcp_secret_' . $uid );
		$user    = wp_get_current_user();
		$token   = $secret ? base64_encode( $user->user_login . ':' . str_replace( ' ', '', $secret ) ) : 'YOUR_TOKEN'; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- HTTP Basic auth value.
		$ssl     = is_ssl() || 0 === strpos( home_url(), 'https://' );
		$clients = self::clients();
		$opt     = (array) get_option( self::OPTION, array() );
		$chosen  = isset( $clients[ $opt['client'] ?? '' ] ) ? $opt['client'] : (string) array_key_first( $clients );
		$snips   = self::snippets( self::url(), $token );
		$snip    = $snips[ $clients[ $chosen ] ];

		echo '<div class="wpe-app wpe-mcp"><main class="wpe-main">';
		echo '<div class="wpe-head"><div><span class="wpe-eyebrow">' . esc_html__( 'AI Connect · MCP', 'hi-wpessential' ) . '</span><h1>' . esc_html__( 'Connect your AI assistant', 'hi-wpessential' ) . '</h1><p>' . esc_html__( 'Pick your AI, generate a code and paste it in. The assistant can then read and edit pages, templates, styles, menus, forms and products, install plugins and import demos — as you, an administrator.', 'hi-wpessential' ) . '</p></div>';
		echo '<span class="wpe-mcp-status ' . ( $on ? 'is-on' : 'is-off' ) . '">' . ( $on ? esc_html__( 'Connected', 'hi-wpessential' ) : esc_html__( 'Off', 'hi-wpessential' ) ) . '</span></div>';
		if ( ! $ssl ) {
			echo '<div class="wpe-callout wpe-callout--warning wpe-keep">' . esc_html__( 'This site does not use HTTPS. Application Passwords need HTTPS on live sites; use it only for local testing.', 'hi-wpessential' ) . '</div>';
		}

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="wpe-card wpe-mcp-card">' . wp_nonce_field( 'wpessential_mcp', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpessential_mcp">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<h2 class="wpe-mcp-step"><b>1</b>' . esc_html__( 'Choose your AI', 'hi-wpessential' ) . '</h2><div class="wpe-mcp-clients" role="radiogroup">';
		foreach ( $clients as $key => $label ) {
			echo '<label class="wpe-mcp-client"><input type="radio" name="client" value="' . esc_attr( $key ) . '"' . checked( $chosen, $key, false ) . '><span><strong>' . esc_html( $label ) . '</strong></span></label>';
		}
		echo '</div><h2 class="wpe-mcp-step"><b>2</b>' . esc_html__( 'Generate the connection code', 'hi-wpessential' ) . '</h2><div class="wpe-mcp-actions">';
		echo '<button class="wpe-btn wpe-btn--grad wpe-btn--lg" name="do" value="connect">' . ( $on ? esc_html__( 'Generate a new code', 'hi-wpessential' ) : esc_html__( 'Switch on and generate', 'hi-wpessential' ) ) . '</button>';
		if ( $on ) {
			echo '<button class="wpe-btn wpe-btn--danger wpe-btn--lg" name="do" value="off">' . esc_html__( 'Switch off and revoke', 'hi-wpessential' ) . '</button>';
		}
		echo '</div><p class="wpe-muted">' . esc_html__( 'A new code replaces the old one, and switching off deletes it.', 'hi-wpessential' ) . '</p></form>';

		if ( $on ) {
			echo '<section class="wpe-card wpe-mcp-card"><h2 class="wpe-mcp-step"><b>3</b>' . esc_html( sprintf( /* translators: %s: AI client name. */ __( 'Paste it into %s', 'hi-wpessential' ), $clients[ $chosen ] ) ) . '</h2>';
			if ( $secret ) {
				echo '<div class="wpe-callout wpe-callout--warning wpe-keep"><strong>' . esc_html__( 'Copy it now — the code is shown only once.', 'hi-wpessential' ) . '</strong> ' . esc_html__( 'Treat it like a password: never paste it into a chat. Generate a new one if it gets lost.', 'hi-wpessential' ) . '</div>';
			} else {
				echo '<p class="wpe-muted">' . esc_html__( 'The code was shown once when it was generated. Replace YOUR_TOKEN with it, or generate a new code.', 'hi-wpessential' ) . '</p>';
			}
			echo '<p>' . esc_html( $snip[0] ) . '</p><div class="wpe-mcp-code"><textarea id="wpe-mcp-snippet" readonly rows="' . (int) min( 16, substr_count( $snip[1], "\n" ) + 2 ) . '" onclick="this.select()">' . esc_textarea( $snip[1] ) . '</textarea>';
			echo '<button type="button" class="wpe-btn wpe-btn--primary wpe-btn--sm" onclick="var t=document.getElementById(\'wpe-mcp-snippet\');t.select();navigator.clipboard&&navigator.clipboard.writeText(t.value);this.textContent=\'' . esc_js( __( 'Copied', 'hi-wpessential' ) ) . '\'">' . esc_html__( 'Copy', 'hi-wpessential' ) . '</button></div></section>';

			echo '<details class="wpe-card wpe-mcp-card"><summary>' . esc_html( sprintf( /* translators: %d: number of tools. */ __( 'What the AI can do (%d tools)', 'hi-wpessential' ), count( self::tools() ) ) ) . '</summary><ul class="wpe-mcp-tools">';
			foreach ( self::tools() as $name => $tool ) {
				echo '<li><code>' . esc_html( $name ) . '</code><span>' . esc_html( $tool['description'] ) . '</span></li>';
			}
			echo '</ul></details>';
		}
		echo '</main></div>';
	}
}
