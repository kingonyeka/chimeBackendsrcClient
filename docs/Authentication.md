# Authentication API

This section details the authentication-related API routes and their functionalities in the Chime Forex Trading project.

---


## POST /auth/login
- **Description:** Logs in a user.
- **Request Body:**
  ```json
  {
    "email": "user@example.com",
    "password": "user_password"
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "login successful",
      "data": {
        "jwt": "generated_jwt_token",
        "refreshToken": "generated_refresh_token"
      }
    }
    ```
  - Error (400/401):
    ```json
    {
      "code": 400/401,
      "message": "Error message",
      "data": null
    }
    ```

## POST /auth/signup
- **Description:** Registers a new user.
- **Request Body:**
  ```json
  {
    "email": "user@example.com",
    "password": "user_password"
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "signup successful",
      "data": null
    }
    ```
  - Error (400/500):
    ```json
    {
      "code": 400/500,
      "message": "Error message",
      "data": null
    }
    ```

## POST /auth/details
- **Description:** Saves user details.
- **JWT Required:** Yes
- **Request Body:**
  ```json
  {
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "M",
    "address": "123 Street, City",
    "email": "user@example.com"
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "Details saved successfully",
      "data": null
    }
    ```
  - Error (400):
    ```json
    {
      "code": 400,
      "message": "Error message",
      "data": null
    }
    ```

## POST /auth/confirm-token
- **Description:** Decodes and validates a JWT token.
- **JWT Required:** No
- **Request Body:**
  ```json
  {
    "token": "the_encoded_jwt_token_sent_from_the_email"
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "Token is valid",
      "data": null
    }
    ```
  - Error (400/401):
    ```json
    {
      "code": 400/401,
      "message": "Error message",
      "data": null
    }
    ```

### POST /auth/refresh
- **Description:** Refreshes an expired JWT token using a refresh token.
- **Requires JWT:** Yes
- **Request Body:**
  ```json
  {
    "token": "your_refresh_token"
  }
  ```
- **Response:**
  - Success (201):
    ```json
    {
      "code": 201,
      "message": "token refreshed successfully",
      "data": {
        "jwt": "new_jwt_token",
        "token": "new_refresh_token"
      }
    }
    ```
  - Error (401):
    ```json
    {
      "code": 401,
      "message": "Unauthorized",
      "data": null
    }
    ```

---

#### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---