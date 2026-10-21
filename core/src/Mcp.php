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
	const SESSIONS = 'wpessential_mcp_sessions';
	const RECENT   = 15;

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_filter( 'get_user_metadata', array( __CLASS__, 'hide_on_profile' ), 10, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'hide_in_rest' ), 10, 3 );
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
		self::track( $name, $args, $error ? $data->get_error_message() : '' );
		$text = $error ? $data->get_error_message() : wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
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

	// Dashboard (the AI Connect tab of the WPEssential app).

	/**
	 * Old AI Connect screen URL opens the dashboard tab.
	 */
	public static function menu() {
		$hook = add_submenu_page( '', __( 'AI Connect', 'hi-wpessential' ), '', 'manage_options', 'wpessential-mcp', '__return_null' );
		if ( $hook ) {
			add_action(
				'load-' . $hook,
				static function () {
					wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::SLUG . '#ai' ) );
					exit;
				}
			);
		}
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
	 * State for the dashboard tab.
	 *
	 * @return array
	 */
	public static function state() {
		$opt     = (array) get_option( self::OPTION, array() );
		$clients = self::clients();
		$client  = isset( $clients[ $opt['client'] ?? '' ] ) ? $opt['client'] : (string) array_key_first( $clients );
		$snips   = self::snippets( self::url(), 'YOUR_TOKEN' );
		return array(
			'enabled'  => self::enabled(),
			'url'      => self::url(),
			'https'    => is_ssl() || 0 === strpos( home_url(), 'https://' ),
			'client'   => $client,
			'clients'  => array_map(
				static fn( $key, $label ) => array(
					'id'    => $key,
					'label' => $label,
				),
				array_keys( $clients ),
				$clients
			),
			// Placeholder setup of the chosen client only, while a code exists.
			'setup'    => self::enabled() ? self::setup( $snips[ $clients[ $client ] ] ) : null,
			'sessions' => self::sessions(),
			'tools'    => array_values(
				array_map(
					static fn( $t ) => array(
						'name'        => $t['name'],
						'description' => $t['description'],
					),
					self::tools()
				)
			),
		);
	}

	/**
	 * Snippet pair as an object.
	 *
	 * @param array $snip array( where, code ).
	 * @return array
	 */
	private static function setup( array $snip ) {
		return array(
			'where' => $snip[0],
			'code'  => $snip[1],
		);
	}

	/**
	 * Switch on with a new code and return the setup of one client only.
	 *
	 * @param string $client Client key.
	 * @return array|\WP_Error
	 */
	public static function connect( $client ) {
		$clients = self::clients();
		if ( ! isset( $clients[ $client ] ) ) {
			return new \WP_Error( 'wpe_mcp_client', __( 'Choose an AI first.', 'hi-wpessential' ) );
		}
		$uid  = get_current_user_id();
		$made = \WP_Application_Passwords::create_new_application_password( $uid, array( 'name' => self::APP_NAME . ' · ' . $clients[ $client ] ) );
		if ( is_wp_error( $made ) ) {
			return $made;
		}
		$sessions                     = self::registry();
		$sessions[ $made[1]['uuid'] ] = array(
			'user'    => $uid,
			'client'  => $client,
			'created' => time(),
			'calls'   => 0,
			'errors'  => 0,
			'recent'  => array(),
		);
		update_option( self::SESSIONS, $sessions, false );
		$opt            = (array) get_option( self::OPTION, array() );
		$opt['enabled'] = 1;
		$opt['user']    = $uid;
		$opt['client']  = $client;
		update_option( self::OPTION, $opt, false );
		$user  = wp_get_current_user();
		$token = base64_encode( $user->user_login . ':' . str_replace( ' ', '', $made[0] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- HTTP Basic auth value.
		$state = self::state();
		// The real code is returned once, never stored in plain text.
		$state['setup'] = self::setup( self::snippets( self::url(), $token )[ $clients[ $client ] ] );
		$state['fresh'] = true;
		return $state;
	}

	/**
	 * Switch off and delete every session's code.
	 */
	public static function disconnect() {
		foreach ( self::registry() as $uuid => $session ) {
			\WP_Application_Passwords::delete_application_password( (int) $session['user'], $uuid );
		}
		delete_option( self::SESSIONS );
		self::revoke( get_current_user_id() );
		$opt            = (array) get_option( self::OPTION, array() );
		$opt['enabled'] = 0;
		update_option( self::OPTION, $opt, false );
	}

	/**
	 * Whether an application password belongs to AI Connect.
	 *
	 * @param array $item Application password.
	 * @return bool
	 */
	private static function is_ours( $item ) {
		return 0 === strpos( (string) ( $item['name'] ?? '' ), self::APP_NAME );
	}

	/**
	 * Keep AI Connect sessions out of Users → Profile → Application Passwords; they are
	 * managed on the AI Connect tab. Only the profile screens are filtered, so sign-in works.
	 *
	 * @param mixed  $value    Short-circuit value.
	 * @param int    $user_id  User.
	 * @param string $meta_key Key.
	 * @return mixed
	 */
	public static function hide_on_profile( $value, $user_id, $meta_key ) {
		global $pagenow;
		if ( '_application_passwords' !== $meta_key || ! is_admin() || ! in_array( $pagenow, array( 'profile.php', 'user-edit.php' ), true ) ) {
			return $value;
		}
		remove_filter( 'get_user_metadata', array( __CLASS__, 'hide_on_profile' ), 10 );
		$list = get_user_meta( $user_id, '_application_passwords', true );
		add_filter( 'get_user_metadata', array( __CLASS__, 'hide_on_profile' ), 10, 3 );
		return array( is_array( $list ) ? array_values( array_filter( $list, static fn( $i ) => ! self::is_ours( $i ) ) ) : $list );
	}

	/**
	 * Same for the REST list the profile screen refreshes from.
	 *
	 * @param \WP_REST_Response $response Response.
	 * @param \WP_REST_Server   $server   Server.
	 * @param \WP_REST_Request  $request  Request.
	 * @return \WP_REST_Response
	 */
	public static function hide_in_rest( $response, $server, $request ) {
		if ( 'GET' === $request->get_method() && preg_match( '#^/wp/v2/users/[^/]+/application-passwords$#', (string) $request->get_route() ) && is_array( $response->get_data() ) ) {
			$response->set_data( array_values( array_filter( $response->get_data(), static fn( $i ) => ! self::is_ours( (array) $i ) ) ) );
		}
		return $response;
	}

	/**
	 * Session records: uuid => user, client, created, calls, errors, recent.
	 *
	 * @return array
	 */
	private static function registry() {
		return (array) get_option( self::SESSIONS, array() );
	}

	/**
	 * Count a tool call against the session (application password) that made it.
	 *
	 * @param string $tool  Tool.
	 * @param array  $args  Arguments.
	 * @param string $error Error message, empty on success.
	 */
	private static function track( $tool, array $args, $error ) {
		$uuid = function_exists( 'rest_get_authenticated_app_password' ) ? (string) rest_get_authenticated_app_password() : '';
		$all  = self::registry();
		if ( ! $uuid || ! isset( $all[ $uuid ] ) ) {
			return;
		}
		$s = $all[ $uuid ];
		++$s['calls'];
		if ( $error ) {
			++$s['errors'];
		}
		$target = $args['id'] ?? ( $args['path'] ?? ( $args['slug'] ?? ( $args['name'] ?? '' ) ) );
		array_unshift(
			$s['recent'],
			array(
				'tool'   => $tool,
				'target' => is_scalar( $target ) ? substr( (string) $target, 0, 80 ) : '',
				'at'     => time(),
				'error'  => substr( $error, 0, 160 ),
			)
		);
		$s['recent']  = array_slice( $s['recent'], 0, self::RECENT );
		$s['last_ip'] = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$s['last_at'] = time();
		$all[ $uuid ] = $s;
		update_option( self::SESSIONS, $all, false );
	}

	/**
	 * Live sessions with tracking; records whose password was deleted elsewhere are dropped.
	 *
	 * @return array
	 */
	public static function sessions() {
		$all     = self::registry();
		$clients = self::clients();
		$out     = array();
		$changed = false;
		foreach ( $all as $uuid => $s ) {
			$item = \WP_Application_Passwords::get_user_application_password( (int) $s['user'], $uuid );
			if ( ! $item ) {
				unset( $all[ $uuid ] );
				$changed = true;
				continue;
			}
			$user  = get_userdata( (int) $s['user'] );
			$out[] = array(
				'id'        => $uuid,
				'client'    => $clients[ $s['client'] ] ?? $s['client'],
				'user'      => $user ? $user->display_name : '#' . $s['user'],
				'created'   => (int) $s['created'],
				'last_used' => (int) max( (int) ( $item['last_used'] ?? 0 ), (int) ( $s['last_at'] ?? 0 ) ),
				'last_ip'   => (string) ( $s['last_ip'] ?? ( $item['last_ip'] ?? '' ) ),
				'calls'     => (int) $s['calls'],
				'errors'    => (int) $s['errors'],
				'recent'    => array_values( (array) $s['recent'] ),
			);
		}
		// Codes made before session tracking existed, still able to connect.
		foreach ( \WP_Application_Passwords::get_user_application_passwords( get_current_user_id() ) as $item ) {
			if ( 0 === strpos( (string) $item['name'], self::APP_NAME ) && ! isset( $all[ $item['uuid'] ] ) ) {
				$all[ $item['uuid'] ] = array(
					'user'    => get_current_user_id(),
					'client'  => __( 'Older code', 'hi-wpessential' ),
					'created' => (int) $item['created'],
					'calls'   => 0,
					'errors'  => 0,
					'recent'  => array(),
				);
				$changed              = true;
				$out[]                = array(
					'id'        => $item['uuid'],
					'client'    => __( 'Older code', 'hi-wpessential' ),
					'user'      => wp_get_current_user()->display_name,
					'created'   => (int) $item['created'],
					'last_used' => (int) ( $item['last_used'] ?? 0 ),
					'last_ip'   => (string) ( $item['last_ip'] ?? '' ),
					'calls'     => 0,
					'errors'    => 0,
					'recent'    => array(),
				);
			}
		}
		if ( $changed ) {
			update_option( self::SESSIONS, $all, false );
		}
		usort( $out, static fn( $a, $b ) => $b['created'] <=> $a['created'] );
		return $out;
	}

	/**
	 * Revoke one session.
	 *
	 * @param string $uuid Session id.
	 * @return array|\WP_Error State.
	 */
	public static function revoke_session( $uuid ) {
		$all = self::registry();
		if ( ! isset( $all[ $uuid ] ) ) {
			return new \WP_Error( 'wpe_mcp_session', __( 'That session no longer exists.', 'hi-wpessential' ) );
		}
		\WP_Application_Passwords::delete_application_password( (int) $all[ $uuid ]['user'], $uuid );
		unset( $all[ $uuid ] );
		update_option( self::SESSIONS, $all, false );
		if ( ! $all ) {
			$opt            = (array) get_option( self::OPTION, array() );
			$opt['enabled'] = 0;
			update_option( self::OPTION, $opt, false );
		}
		return self::state();
	}

	/**
	 * Remove this tool's application passwords for a user.
	 *
	 * @param int $uid User.
	 */
	private static function revoke( $uid ) {
		foreach ( \WP_Application_Passwords::get_user_application_passwords( $uid ) as $item ) {
			if ( 0 === strpos( (string) $item['name'], self::APP_NAME ) ) {
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
}
