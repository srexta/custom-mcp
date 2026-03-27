<?php
/**
 * Bootstrap class for MCP Example.
 *
 * This is the orchestrator — it wires everything together by hooking into
 * the THREE WordPress actions that make MCP work.
 *
 * @package MCP_Example
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main bootstrap class.
 *
 * Registers the ability category, individual abilities (tools), and
 * optionally a custom MCP server.
 */
final class Custom_MCP_Bootstrap {

    /**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

    /**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

    /**
	 * Hook into the THREE key WordPress actions for MCP.
	 *
	 * These are the only three hooks you need to know:
	 *
	 * 1. wp_abilities_api_categories_init — Register your category (grouping)
	 * 2. wp_abilities_api_init            — Register your abilities (tools)
	 * 3. mcp_adapter_init                 — (Optional) Register a custom MCP server
	 */
	private function init(): void {
        add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
        add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
        add_action( 'mcp_adapter_init', array( $this, 'register_mcp_server' ) );
	}

    /*
	 * ========================================================================
	 * STEP 1: Register the ability category
	 * ========================================================================
	 *
	 * Categories are like menu sections — they group related tools together.
	 * When an AI asks "what can you do?", categories help organize the answer.
	 */

	/**
	 * Register the "User Management" ability category.
	 */
	public function register_category(): void {
		wp_register_ability_category(
			'custom-mcp',
			array(
				'label'       => __( 'Custom MCP (Example)', 'mcp-example' ),
				'description' => __( 'Example abilities for managing WordPress users via MCP.', 'mcp-example' ),
			)
		);
	}

    /*
	 * ========================================================================
	 * STEP 2: Register abilities (each one becomes an MCP "tool")
	 * ========================================================================
	 *
	 * Each ability maps to one MCP "tool" that an AI agent can call.
	 * The key fields for every ability are:
	 *
	 *   - input_schema:        JSON Schema defining what parameters the tool accepts.
	 *                          This is how the AI knows what to send.
	 *
	 *   - output_schema:       JSON Schema defining what the tool returns.
	 *                          This helps the AI understand the response.
	 *
	 *   - execute_callback:    The PHP function that does the actual work.
	 *                          This is YOUR code — the business logic.
	 *
	 *   - permission_callback: WordPress capability check.
	 *                          The AI must authenticate as a user with this capability.
	 *
	 *   - meta.annotations:    MCP metadata about the tool's behavior:
	 *                          - readonly:    Does this tool only read data?
	 *                          - destructive: Does this tool create/modify/delete data?
	 *                          - idempotent:  Is calling it twice the same as calling it once?
	 *
	 *                          Well-behaved AI agents use annotations to decide whether
	 *                          to ask for human confirmation before calling a tool.
	 */

	/**
	 * Register all user management abilities.
	 */
	public function register_abilities(): void {
		// Load the ability files (each contains one execute_callback function).
		
	}


    /**
	 * Register our custom MCP server with the MCP Adapter.
	 *
	 * @param \WP\MCP\Core\McpAdapter $adapter The MCP Adapter instance.
	 */
	public function register_mcp_server( $adapter ): void {
		
	}
	
}
