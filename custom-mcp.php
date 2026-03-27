<?php
/**
 * Plugin Name: Custom MCP Plugin
 * Plugin URI:  https://developer.wordpress.org/
 * Description: A teaching plugin that demonstrates how to expose WordPress functionality to AI agents via MCP (Model Context Protocol).
 * Version:     2.0.0
 * Author:      Sagar Shrestha
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-mcp
 * Requires at least: 6.9
 * Requires PHP: 7.4
 *
 * @package CustomMCP
 */

declare( strict_types=1 );

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version — used when registering the custom MCP server.
 */
define( 'CUSTOM_MCP_VERSION', '2.0.0' );

/**
 * Plugin directory path — used to require files.
 */
define( 'CUSTOM_MCP_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Initialize the plugin after all plugins are loaded.
 *
 * We hook into 'plugins_loaded' to ensure the MCP Adapter and
 * WordPress Abilities API are available before we try to use them.
 */
function custom_mcp_init(): void {

	/*
	 * DEPENDENCY CHECK:
	 * The WordPress Abilities API (wp_register_ability) is available in WP 6.9+.
	 * The MCP Adapter plugin must also be active — it provides the MCP server
	 * infrastructure that converts our abilities into MCP tools.
	 */
	if ( ! function_exists( 'wp_register_ability' ) ) {
		add_action( 'admin_notices', 'custom_mcp_missing_abilities_notice' );
		return;
	}

	// Load the bootstrap class and ability files.
	require_once CUSTOM_MCP_DIR . 'includes/class-custom-mcp-bootstrap.php';

	// Start the plugin — this registers our category, abilities, and MCP server.
	Custom_MCP_Bootstrap::instance();
}
add_action( 'plugins_loaded', 'custom_mcp_init' );

/**
 * Admin notice shown when the Abilities API is not available.
 */
function custom_mcp_missing_abilities_notice(): void {
	wp_admin_notice(
		esc_html__(
			'Custom MCP requires WordPress 6.9+ with the Abilities API and the MCP Adapter plugin active.',
			'custom-mcp'
		),
		array(
			'type'    => 'error',
			'dismiss' => true,
		)
	);
}