<?php
/**
 * Custom MCP Server for the Example plugin.
 *
 * ============================================================================
 * LAYER 2: Why create your own MCP server?
 * ============================================================================
 *
 * The MCP Adapter already provides a DEFAULT server at:
 *   /wp-json/mcp/mcp-adapter-default-server
 *
 * Any ability with mcp.public = true is auto-discovered by that server.
 * So why create your own?
 *
 * 1. FOCUSED ENDPOINT — Your server only exposes YOUR tools.
 *    The default server exposes ALL public tools from ALL plugins.
 *    If 5 plugins each register 10 tools, the AI sees 50 tools on the
 *    default server. With your own server, the AI only sees your 3.
 *
 * 2. SEPARATE CONFIGURATION — AI clients configure each server independently.
 *    In their .mcp.json, they add YOUR endpoint with YOUR auth settings.
 *    This is cleaner than one big endpoint with everything mixed together.
 *
 * 3. INDEPENDENT VERSIONING — Your server has its own version number.
 *    You can iterate without affecting other plugins.
 *
 * 4. CUSTOM ERROR HANDLING — Choose your own error handler and
 *    observability settings per server.
 *
 * For this example plugin, the custom server lives at:
 *   /wp-json/mcp/mcp-example-users
 *
 * ============================================================================
 *
 * @package MCP_Example
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a dedicated MCP server for the example user management tools.
 */
final class Custom_MCP_Example_Server {

	/**
	 * Server ID — used in REST routes and WP-CLI commands.
	 *
	 * @var string
	 */
	public const SERVER_ID = 'custom-mcp';

	/**
	 * Register the custom MCP server with the MCP Adapter.
	 *
	 * @param \WP\MCP\Core\McpAdapter $adapter       The MCP Adapter instance (provided by the hook).
	 * @param string[]                $ability_names  Array of ability names to expose as tools.
	 */
	public static function register( $adapter, array $ability_names ): void {
		/*
		 * create_server() parameters:
		 *
		 * 1. Server ID:          Unique identifier (used in WP-CLI: wp mcp serve mcp-example-users)
		 * 2. Route namespace:    REST API namespace (usually 'mcp')
		 * 3. Route:              REST API route (appended to namespace)
		 * 4. Server name:        Human-readable name shown to AI clients
		 * 5. Description:        What this server does
		 * 6. Version:            Your plugin version
		 * 7. Transports:         Array of transport classes (HTTP is the standard one)
		 * 8. Error handler:      How to handle MCP errors (ErrorLog writes to wp-content/debug.log)
		 * 9. Observability:      Monitoring handler (Null = no monitoring, good for simple plugins)
		 * 10. Tool abilities:    Array of ability names to expose as MCP tools
		 * 11. Resource abilities: Array of ability names to expose as MCP resources (empty for us)
		 * 12. Prompt abilities:  Array of ability names to expose as MCP prompts (empty for us)
		 *
		 * Result: REST endpoint at /wp-json/mcp/mcp-example-users
		 */
		$result = $adapter->create_server(
			self::SERVER_ID,                                                           // 1. server_id
			'mcp',                                                                        // 2. route_namespace
			self::SERVER_ID,                                                           // 3. route
			'MCP Example - Create User',                                           // 4. server_name
			'Example MCP server demonstrating creating user WordPress.',   // 5. description
			CUSTOM_MCP_VERSION,                                                       // 6. version
			array( \WP\MCP\Transport\HttpTransport::class ),                           // 7. transports
			\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler::class,       // 8. error_handler
			\WP\MCP\Infrastructure\Observability\NullMcpObservabilityHandler::class,   // 9. observability
			$ability_names,                                                            // 10. tools
			array(),                                                                   // 11. resources (none)
			array()                                                                    // 12. prompts (none)
		);

		if ( is_wp_error( $result ) ) {
			error_log(
				sprintf(
					'MCP Example: Failed to create MCP server. Error: %s (Code: %s)',
					$result->get_error_message(),
					$result->get_error_code()
				)
			);
		}
	}
}
