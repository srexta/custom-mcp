<?php
/**
 * Dedicated MCP server registration for Custom MCP plugin.
 *
 * REST route: /wp-json/mcp/custom-mcp
 *
 * @package CustomMCP
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the custom-mcp MCP server with the MCP Adapter.
 */
final class Custom_MCP_Server {

	/**
	 * Server ID — used in REST routes and WP-CLI (must match mcp.json path segment).
	 *
	 * @var string
	 */
	public const SERVER_ID = 'custom-mcp';

	/**
	 * Register the custom MCP server with the MCP Adapter.
	 *
	 * @param \WP\MCP\Core\McpAdapter $adapter       The MCP Adapter instance (provided by the hook).
	 * @param string[]                $ability_names Array of ability names to expose as MCP tools.
	 */
	public static function register( $adapter, array $ability_names ): void {
		$result = $adapter->create_server(
			self::SERVER_ID,
			'mcp',
			self::SERVER_ID,
			__( 'Custom MCP — Create User', 'custom-mcp' ),
			__( 'MCP server exposing the create-user ability for WordPress.', 'custom-mcp' ),
			CUSTOM_MCP_VERSION,
			array( \WP\MCP\Transport\HttpTransport::class ),
			\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,
			\WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class,
			$ability_names,
			array(),
			array()
		);

		if ( is_wp_error( $result ) ) {
			error_log(
				sprintf(
					'Custom MCP: Failed to create MCP server. Error: %s (Code: %s)',
					$result->get_error_message(),
					$result->get_error_code()
				)
			);
		}
	}
}
