<?php
/**
 * Ability: Create a WordPress User.
 *
 * THIS IS THE KEY TEACHING MOMENT FOR MCP.
 *
 * This tool is marked as "destructive" in its annotations. When an AI agent
 * sees destructive: true, it should ask the human for confirmation before
 * calling this tool. For example:
 *
 *   AI: "I'd like to create a new user 'john@example.com' with the
 *        subscriber role. Should I proceed?"
 *   Human: "Yes, go ahead."
 *   AI: [calls the tool]
 *
 * This is how MCP keeps humans in control. The annotations aren't just
 * metadata — they're a safety contract between your plugin and the AI.
 *
 * Compare with list-users (readonly: true) — the AI can call that freely
 * without asking, because reading data is safe.
 *
 * @package MCP_Example
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Execute callback for the mcp-example/create-user ability.
 *
 * @param array $input {
 *     Input parameters from the AI agent.
 *
 *     @type string $username   Required. Login username.
 *     @type string $email      Required. Email address.
 *     @type string $role       Optional. WordPress role. Default 'subscriber'.
 *     @type string $first_name Optional. First name.
 *     @type string $last_name  Optional. Last name.
 * }
 * @return array|WP_Error Created user details or error.
 */
function custom_mcp_execute_create_user( $input = array() ) {
	$input = is_array( $input ) ? $input : array();

	// Sanitize all inputs.
	$username   = sanitize_user( $input['username'] ?? '' );
	$email      = sanitize_email( $input['email'] ?? '' );
	$role       = sanitize_text_field( $input['role'] ?? 'subscriber' );
	$first_name = sanitize_text_field( $input['first_name'] ?? '' );
	$last_name  = sanitize_text_field( $input['last_name'] ?? '' );

	// Validate required fields.
	if ( empty( $username ) ) {
		return new WP_Error( 'missing_username', __( 'Username is required.', 'custom-mcp' ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', __( 'A valid email address is required.', 'custom-mcp' ) );
	}

	// Validate the role is allowed.
	$allowed_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
	if ( ! in_array( $role, $allowed_roles, true ) ) {
		$role = 'subscriber';
	}

	// Generate a secure random password.
	$password = wp_generate_password( 16, true, true );

	// Create the user using WordPress core function.
	$user_id = wp_insert_user(
		array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'role'       => $role,
			'first_name' => $first_name,
			'last_name'  => $last_name,
		)
	);

	// wp_insert_user returns WP_Error on failure — pass it through.
	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	return array(
		'user_id'  => $user_id,
		'username' => $username,
		'email'    => $email,
		'role'     => $role,
		'message'  => sprintf(
			/* translators: 1: username, 2: role */
			__( 'User "%1$s" created successfully with the "%2$s" role. A random password has been generated.', 'custom-mcp' ),
			$username,
			$role
		),
	);
}
