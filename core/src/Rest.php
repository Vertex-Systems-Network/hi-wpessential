<?php
/**
 * REST API: wpessential/v1.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Routes used by the dashboard.
 */
class Rest {

	const NS = 'wpessential/v1';

	/**
	 * Register routes. All need manage_options and the REST nonce.
	 */
	public function register() {
		$routes = array(
			'/demos'              => array( 'GET', 'demos' ),
			'/import/start'       => array( 'POST', 'import_start' ),
			'/import/run'         => array( 'POST', 'import_run' ),
			'/import/status'      => array( 'GET', 'import_status' ),
			'/import/cancel'      => array( 'POST', 'import_cancel' ),
			'/import/remove'      => array( 'POST', 'import_remove' ),
			'/plugins'            => array( 'GET', 'plugins' ),
			'/plugins/action'     => array( 'POST', 'plugin_action' ),
			'/demo-plugins'       => array( 'GET', 'demo_plugins' ),
			'/license'            => array( 'GET', 'license' ),
			'/license/activate'   => array( 'POST', 'license_activate' ),
			'/license/deactivate' => array( 'POST', 'license_deactivate' ),
			'/status'             => array( 'GET', 'status' ),
			'/account'            => array( 'GET', 'account' ),
			'/account/login'      => array( 'POST', 'account_login' ),
			'/account/register'   => array( 'POST', 'account_register' ),
			'/account/logout'     => array( 'POST', 'account_logout' ),
			'/account/use'        => array( 'POST', 'account_use' ),
			'/account/checkout'   => array( 'POST', 'account_checkout' ),
			'/account/action'     => array( 'POST', 'account_action' ),
			'/brand'              => array( 'POST', 'brand' ),
			'/mcp'                => array( 'GET', 'mcp' ),
			'/mcp/connect'        => array( 'POST', 'mcp_connect' ),
			'/mcp/off'            => array( 'POST', 'mcp_off' ),
		);
		foreach ( $routes as $route => $def ) {
			register_rest_route(
				self::NS,
				$route,
				array(
					'methods'             => $def[0],
					'callback'            => array( $this, $def[1] ),
					'permission_callback' => static function () {
						return current_user_can( 'manage_options' );
					},
				)
			);
		}
	}

	/**
	 * Wrap results.
	 *
	 * @param mixed $data Data or WP_Error.
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function respond( $data ) {
		if ( is_wp_error( $data ) ) {
			$data->add_data( array( 'status' => 400 ) );
			return $data;
		}
		return rest_ensure_response( $data );
	}

	/**
	 * GET /demos.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function demos( $request ) {
		$all             = Demos::all( (bool) $request->get_param( 'refresh' ) );
		$all['imported'] = Importer::imported() ? array_intersect_key( Importer::imported(), array_flip( array( 'demo', 'title', 'builder', 'time' ) ) ) : null;
		$job             = Importer::current();
		$all['job']      = ! empty( $job['id'] ) && in_array( $job['status'], array( 'running', 'failed' ), true ) ? array(
			'demo'    => $job['demo'],
			'title'   => $job['title'],
			'builder' => $job['builder'],
			'status'  => $job['status'],
		) : null;
		return $this->respond( $all );
	}

	/**
	 * GET /demo-plugins?demo=&builder=.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function demo_plugins( $request ) {
		$demo = Demos::get( (string) $request->get_param( 'demo' ) );
		if ( ! $demo ) {
			return $this->respond( new \WP_Error( 'wpe_demo', __( 'Demo not found.', 'hi-wpessential' ) ) );
		}
		$plugins = (array) ( $demo['builders'][ (string) $request->get_param( 'builder' ) ]['plugins'] ?? array() );
		return $this->respond( array_map( array( Plugins::class, 'describe' ), $plugins ) );
	}

	/**
	 * POST /import/start.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function import_start( $request ) {
		return $this->respond( Importer::start( (string) $request->get_param( 'demo' ), (string) $request->get_param( 'builder' ), (array) $request->get_param( 'options' ) ) );
	}

	/**
	 * POST /import/run.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function import_run( $request ) {
		$job = Importer::current();
		if ( ! empty( $job['id'] ) && 'failed' === $job['status'] && $request->get_param( 'retry' ) ) {
			$job['status']                           = 'running';
			$job['finished']                         = 0;
			$job['steps'][ $job['index'] ]['status'] = 'pending';
			$job['inflight']                         = array(
				'step'     => '',
				'cursor'   => -1,
				'attempts' => 0,
			);
			update_option( Importer::JOB, $job, false );
		}
		return $this->respond( Importer::run( (int) $request->get_param( 'since' ) ) );
	}

	/**
	 * GET /import/status.
	 */
	public function import_status() {
		$job = Importer::current();
		return $this->respond( empty( $job['id'] ) ? null : Importer::run( 0 ) );
	}

	/**
	 * POST /import/cancel.
	 */
	public function import_cancel() {
		return $this->respond( Importer::cancel() );
	}

	/**
	 * POST /import/remove.
	 */
	public function import_remove() {
		$record = Importer::imported();
		if ( empty( $record['demo'] ) ) {
			return $this->respond( new \WP_Error( 'wpe_nothing', __( 'No imported demo was found.', 'hi-wpessential' ) ) );
		}
		$count = Importer::remove();
		return $this->respond(
			array(
				/* translators: 1: demo, 2: count. */
				'message' => sprintf( __( '%1$s removed: %2$d items deleted and your previous settings restored.', 'hi-wpessential' ), $record['title'], $count ),
			)
		);
	}

	/**
	 * GET /plugins.
	 */
	public function plugins() {
		return $this->respond( Plugins::catalogue() );
	}

	/**
	 * POST /plugins/action.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function plugin_action( $request ) {
		$slug   = sanitize_key( (string) $request->get_param( 'slug' ) );
		$action = (string) $request->get_param( 'action' );
		$entry  = null;
		foreach ( Plugins::catalogue() as $plugin ) {
			if ( $plugin['slug'] === $slug ) {
				$entry = $plugin;
			}
		}
		if ( ! $entry ) {
			return $this->respond( new \WP_Error( 'wpe_plugin', __( 'This plugin is not managed by WPEssential.', 'hi-wpessential' ) ) );
		}
		switch ( $action ) {
			case 'install':
				$result = Plugins::install( $entry );
				if ( ! is_wp_error( $result ) ) {
					$result = Plugins::activate( $slug );
				}
				break;
			case 'activate':
				$result = Plugins::activate( $slug );
				break;
			case 'deactivate':
				Plugins::deactivate( $slug );
				$result = true;
				break;
			case 'delete':
				$result = Plugins::delete( $slug );
				break;
			default:
				$result = new \WP_Error( 'wpe_action', __( 'Unknown action.', 'hi-wpessential' ) );
		}
		return $this->respond( is_wp_error( $result ) ? $result : Plugins::describe( $entry ) );
	}

	/**
	 * GET /license.
	 */
	public function license() {
		return $this->respond( License::public_data() );
	}

	/**
	 * POST /license/activate.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function license_activate( $request ) {
		return $this->respond( License::activate( (string) $request->get_param( 'key' ) ) );
	}

	/**
	 * POST /license/deactivate.
	 */
	public function license_deactivate() {
		return $this->respond( License::deactivate() );
	}

	/**
	 * GET /status.
	 */
	public function status() {
		return $this->respond(
			array(
				'checks'      => Status::checks(),
				'environment' => Status::environment(),
			)
		);
	}

	/**
	 * GET /account.
	 */
	public function account() {
		return $this->respond( Account::me() );
	}

	/**
	 * POST /account/login.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function account_login( $request ) {
		return $this->respond( Account::login( (string) $request->get_param( 'login' ), (string) $request->get_param( 'password' ) ) );
	}

	/**
	 * POST /account/register.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function account_register( $request ) {
		return $this->respond( Account::register( (string) $request->get_param( 'name' ), (string) $request->get_param( 'email' ), (string) $request->get_param( 'password' ) ) );
	}

	/**
	 * POST /account/logout.
	 */
	public function account_logout() {
		return $this->respond( Account::logout() );
	}

	/**
	 * POST /account/use.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function account_use( $request ) {
		$result = Account::use_license( (int) $request->get_param( 'license_id' ) );
		return $this->respond(
			is_wp_error( $result ) ? $result : array(
				'license' => $result,
				'account' => Account::me(),
			)
		);
	}

	/**
	 * POST /account/checkout.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function account_checkout( $request ) {
		return $this->respond( Account::checkout( sanitize_key( (string) $request->get_param( 'plan' ) ), (int) $request->get_param( 'license_id' ), sanitize_key( (string) $request->get_param( 'mode' ) ) ) );
	}

	/**
	 * POST /account/action.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function account_action( $request ) {
		return $this->respond( Account::license_action( (int) $request->get_param( 'license_id' ), sanitize_key( (string) $request->get_param( 'action' ) ), (string) $request->get_param( 'domain' ) ) );
	}

	/**
	 * POST /brand (white label only).
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function brand( $request ) {
		if ( empty( License::data()['white_label'] ) || ! License::is_active() ) {
			return $this->respond( new \WP_Error( 'wpe_white_label', __( 'Branding can be changed with a White Label plan.', 'hi-wpessential' ) ) );
		}
		update_option( 'wpessential_core_brand', sanitize_text_field( (string) $request->get_param( 'name' ) ) );
		return $this->respond( array( 'brand' => Admin::brand() ) );
	}

	/**
	 * AI Connect state.
	 *
	 * @return \WP_REST_Response
	 */
	public function mcp() {
		return rest_ensure_response( Mcp::state() );
	}

	/**
	 * AI Connect: new code for one client.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function mcp_connect( $request ) {
		return $this->respond( Mcp::connect( sanitize_key( (string) $request->get_param( 'client' ) ) ) );
	}

	/**
	 * AI Connect: switch off and revoke.
	 *
	 * @return \WP_REST_Response
	 */
	public function mcp_off() {
		Mcp::disconnect();
		return rest_ensure_response( Mcp::state() );
	}
}
