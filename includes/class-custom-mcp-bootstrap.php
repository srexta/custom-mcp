<?php
/**
 * Bootstrap class for Custom MCP.
 *
 * This is the orchestrator — it wires everything together by hooking into
 * the THREE WordPress actions that make MCP work.
 *
 * @package CustomMCP
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
	 * Ability names registered by this plugin.
	 * We track these so we can pass them to our custom MCP server.
	 *
	 * @var string[]
	 */
	private array $ability_names = array();

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
		/*
		 * Priority 20 matches mcp-example: run after core (10) and MCP Adapter default abilities (10)
		 * so $this->ability_names is filled in a predictable order.
		 */
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ), 20 );
		/*
		 * Priority 20 runs after DefaultServerFactory::create (10), which calls wp_get_abilities()
		 * and triggers the abilities API so $this->ability_names is populated.
		 */
		add_action( 'mcp_adapter_init', array( $this, 'register_mcp_server' ), 20 );
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
				'label'       => __( 'Custom MCP (Example)', 'custom-mcp' ),
				'description' => __( 'Example abilities for managing WordPress users via MCP.', 'custom-mcp' ),
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
		require_once CUSTOM_MCP_DIR . 'includes/abilities/class-ability-create-user.php';

		// Register each ability.
		$this->register_ability_create_user();
	}

	/**
	 * Tool 3: Create User — a DESTRUCTIVE tool.
	 *
	 * This is the key teaching moment: when destructive is true, well-behaved
	 * AI agents will ask the human for confirmation before calling this tool.
	 * This is how MCP keeps humans in the loop for dangerous operations.
	 */
	private function register_ability_create_user(): void {
		$name = 'custom-mcp/create-user';

		wp_register_ability(
			$name,
			array(
				'label'       => __( 'Create User', 'custom-mcp' ),
				'description' => __( 'Create a new WordPress user. Requires username and email. Returns the created user details.', 'custom-mcp' ),
				'category'    => 'custom-mcp',
				'input_schema' => array(
					'type'                 => 'object',
					'properties'           => array(
						'username'   => array(
							'type'        => 'string',
							'description' => 'Login username for the new user.',
						),
						'email'      => array(
							'type'        => 'string',
							'description' => 'Email address for the new user.',
						),
						'role'       => array(
							'type'        => 'string',
							'description' => 'WordPress role to assign.',
							'enum'        => array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ),
							'default'     => 'subscriber',
						),
						'first_name' => array(
							'type'        => 'string',
							'description' => 'First name of the user.',
						),
						'last_name'  => array(
							'type'        => 'string',
							'description' => 'Last name of the user.',
						),
					),
					'required'             => array( 'username', 'email' ),
					'additionalProperties' => false,
				),

				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id'  => array( 'type' => 'integer' ),
						'username' => array( 'type' => 'string' ),
						'email'    => array( 'type' => 'string' ),
						'role'     => array( 'type' => 'string' ),
						'message'  => array( 'type' => 'string' ),
					),
				),

				'execute_callback' => 'custom_mcp_execute_create_user',

				/*
				 * PERMISSION: create_users capability required.
				 * Only Administrators have this by default.
				 * The AI must authenticate as an admin to use this tool.
				 */
				'permission_callback' => static function ( $input = array() ): bool {
					return current_user_can( 'create_users' );
				},

				/*
				 * ANNOTATIONS — The critical difference from readonly tools:
				 *
				 *   readonly: false     → This tool WRITES data.
				 *   destructive: true   → This tool CREATES something that may be hard to undo.
				 *   idempotent: false   → Calling it twice creates TWO users (not one).
				 *
				 * When an AI agent sees destructive: true, it should ask the human:
				 * "I'd like to create a user called 'john'. Should I proceed?"
				 * This keeps humans in control of destructive operations.
				 */
				'meta' => array(
					'annotations' => array(
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => false,
					),
					'mcp'         => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			)
		);

		$this->ability_names[] = $name;
	}

	/**
	 * Register our custom MCP server with the MCP Adapter.
	 *
	 * @param \WP\MCP\Core\McpAdapter $adapter The MCP Adapter instance.
	 */
	public function register_mcp_server( $adapter ): void {
		require_once CUSTOM_MCP_DIR . 'includes/class-custom-mcp-server.php';
		Custom_MCP_Server::register( $adapter, $this->ability_names );
	}

	/**
	 * Get the list of registered ability names.
	 *
	 * @return string[]
	 */
	public function get_ability_names(): array {
		return $this->ability_names;
	}
}
