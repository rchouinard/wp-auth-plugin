<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Renders the options page for the plugin.
 * 
 * @return void
 */
function auth_api_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('auth-api-options-group');
            do_settings_sections('auth-api-options-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('JWT Secret Key', 'auth-api'); ?></th>
                    <td><input type="text" name="jwt_auth_secret_key" value="<?php echo esc_attr(get_option('jwt_auth_secret_key')); ?>" class="regular-text"/></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Handles user login via REST API and generates a JWT token for authentication.
 * 
 * This function processes the username and password from a WP_REST_Request, authenticates the user,
 * and then creates a JSON Web Token (JWT) using the secret key stored in the plugin options. The generated
 * token includes details about the authenticated user such as username, email, name, and roles.
 * 
 * @param WP_REST_Request $request The REST API request object containing the username and password.
 * 
 * @return WP_Error|WP_REST_Response Returns a WP_Error if there are issues with the input or authentication,
 *                                  otherwise returns a WP_REST_Response with the JWT token and user details.
 */
function auth_api_login(WP_REST_Request $request)
{
    $username = sanitize_text_field($request->get_param('username'));
    $password = sanitize_text_field($request->get_param('password'));
    $secret_key = get_option('jwt_auth_secret_key');

    if (empty($secret_key)) {
        return new WP_Error('missing_key', 'Required plugin configuration not set.', ['status' => 500]);
    }

    if (empty($username) || empty($password)) {
        return new WP_Error('missing_data', 'Username or password is missing.', ['status' => 400]);
    }

    $user = authenticate($username, $password);
    if (is_wp_error($user)) {
        return new WP_Error('invalid_credentials', 'Invalid username or password.', ['status' => 401]);
    }

    $user_data = [
        'username' => $user->user_login,
        'email' => $user->user_email,
        'name' => $user->display_name,
        'roles' => $user->roles,
    ];

    $issuedAt = time();
    $expiresAt = $issuedAt + (60 * 60); // Expire in 1 hour
    $payload = [
        'iss' => get_site_url(),
        'iat' => $issuedAt,
        'exp' => $expiresAt,
        'sub' => $user->ID,
        'data' => $user_data,
    ];

    $token = JWT::encode($payload, $secret_key, 'HS256');

    return new WP_REST_Response(['token' => $token, 'user' => $user_data], 200);
}

/**
 * Retrieves user data based on the provided JWT token.
 *
 * This function decodes and verifies a JWT token to extract user information. It checks if the authorization header contains a valid token, verifies it, retrieves the associated user data from the database, and returns it as a WP_REST_Response object.
 *
 * @param WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response|WP_Error The user data if successful, or an error object on failure.
 */
function auth_api_get_user_data(WP_REST_Request $request)
{
    $token = trim(str_replace('Bearer', '', $request->get_header('Authorization')));
    if (!$token) {
        return new WP_Error('invalid_credentials', 'Invalid token.', ['status' => 401]);
    }

    $data = auth_api_verify_jwt_token($token);
    if (is_wp_error($data)) {
        return $data;
    }

    $user = get_userdata($data['sub']);
    if (!$user) {
        return new WP_Error('user_error', 'Invalid user ID.', ['status' => 400]);
    }

    $user_data = [
        'username' => $user->user_login,
        'email' => $user->user_email,
        'name' => $user->display_name,
        'roles' => $user->roles,
    ];

    return new WP_REST_Response($user_data, 200);
}

/**
 * Verifies a JWT token.
 *
 * This function decodes and validates the provided JWT token using the secret key stored in WordPress options.
 * It checks if the token is valid, not expired, and issued by the correct issuer (site URL).
 *
 * @param string $token The JWT token to verify.
 * @return array|WP_Error An array containing decoded token data or a WP_Error object if verification fails.
 */
function auth_api_verify_jwt_token($token)
{
    $secret_key = get_option('jwt_auth_secret_key');

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    } catch (Exception $e) {
        return new WP_Error('jwt_invalid', 'Invalid token provided.', ['status' => 403]);
    }

    if ($decoded->iss && $decoded->iss !== get_site_url()) {
        return new WP_Error('jwt_invalid', 'Invalid token issuer.', ['status' => 403]);
    }

    if ($decoded->exp && $decoded->exp < time()) {
        return new WP_Error('jwt_expired', 'Token has expired.', ['status' => 401]);
    }

    return (array) $decoded;
}
