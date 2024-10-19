 # Auth API Plugin

## Description
The **Auth API for** plugin is designed to expose an API endpoint that allows users to authenticate via their username and password. This plugin provides functionality for user login through a RESTful API, using JSON Web Tokens (JWT) for secure authentication.

## Features
- **User Authentication**: Allows users to log in by providing their username and password.
- **JSON Web Token (JWT)**: Implements JWT for generating tokens upon successful login.
- **RESTful API Endpoint**: Exposes a `/login` endpoint that accepts POST requests with `username` and `password` parameters.
- **Error Handling**: Returns appropriate HTTP status codes and error messages in case of missing data or invalid credentials.

## Installation
1. Download and extract the plugin files to your WordPress plugins directory (`wp-content/plugins`).
2. Activate the plugin through the 'Plugins' menu in the WordPress admin dashboard.

## Usage
### API Endpoint
To authenticate a user, make a POST request to the following endpoint:
```
POST /wp-json/auth-api/v1/login
```
**Request Body**:
```json
{
  "username": "your_username",
  "password": "your_password"
}
```

### Response
A successful login will return a JSON response containing the user data and a JWT token:
```json
{
  "token": "your.jwt.token.here",
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "name": "John Doe",
    "roles": ["subscriber"]
  }
}
```

### User Data Endpoint
To retrieve user data using the JWT token, make a GET request to the following endpoint:
```
GET /wp-json/auth-api/v1/me
```
**Headers**:
```
Authorization: Bearer your.jwt.token.here
```

### Response
A successful request will return a JSON response containing the user data:
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "name": "John Doe",
  "roles": ["subscriber"]
}
```

### Error Handling
- **400**: Missing token.
- **401**: Invalid token.

## Dependencies
This plugin requires the following dependencies:
- PHP 7.2+
- WordPress 5.x+

## Contributing
Contributions are welcome! Please open an issue to discuss major changes before sending a pull request.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support
For support, please create an issue on the GitHub repository.
