
# Password Reset (PasswordResetController)

#### Overview

The Password Reset feature allows users to reset their password via email. The process includes three main steps:
1. Requesting a password reset.
2. Verifying the password reset token.
3. Resetting the password.

#### Endpoints

##### Request Password Reset
- **URL:** `/password/request-reset`
- **Method:** POST
- **Payload:**
  ```json
  {
      "email": "user@example.com"
  }
  ```
- **Response:**
  ```json
  {
      "code": 200,
      "message": "password reset email sent"
  }
  ```

##### Verify Token
- **URL:** `/password/verify-token`
- **Method:** GET
- **Query Parameters:**
  - `token`: The password reset token.
- **Response:**
  ```json
  {
      "code": 200,
      "message": "token is valid"
  }
  ```

##### Reset Password
- **URL:** `/password/reset`
- **Method:** POST
- **Payload:**
  ```json
  {
      "token": "reset_token",
      "new_password": "new_password123",
      "confirm_password": "new_password123"
  }
  ```
- **Response:**
  ```json
  {
      "code": 200,
      "message": "password has been reset"
  }
  ```

#### Notifications

##### Password Reset Email
- Sent when a user requests a password reset.
- Contains a link to reset the password, which expires in 15 minutes.

##### Password Reset Success Email
- Sent when a user's password has been successfully reset.
- **Template Path:** `../src/Assets/email/templates/email_reset_success.html`

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---