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
        
	}

	
}
