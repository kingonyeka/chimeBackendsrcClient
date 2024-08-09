# Admin Routes


## GET /admin
- **Description**: Retrieves a list of all admin users.
- **JWT Required:** Yes
- **Query Parameters**:
  - `sort_by` (optional): Field to sort by (default is `first_name`).
  - `length` or `limit` (optional): Number of records to return (default is 10).
  - `start` or `offset` (optional): Number of records to skip (default is 0).
  - `search` or `search[value]` (optional): Search term to filter the users.
  - `order[0][dir]` (optional): Direction of sorting (default is `asc`).
- **Response**:
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "fetched successfully",
      "items": 10,
      "totalRecords": 100,
      "filteredRecords": 100,
      "data": [
        {
          "admin_id": 1,
          "first_name": "Admin",
          "last_name": "User",
          "email": "admin.user@example.com"
        },
        ...
      ]
    }
    ```
  - Error (401/500):
    ```json
    {
      "code": 401/500,
      "message": "Error message",
      "data": null
    }
    ```


## POST /admin/auth/login
- **Description**: Authenticates an admin user and returns a JWT and refresh token.
- **Request Body**:
  ```json
  {
    "email": "admin@example.com",
    "password": "password123"
  }
  ```
- **Response**:
  ```json
  {
    "code": 200,
    "message": "Login successful",
    "data" : {
        "jwt": "jwt-token",
        "refresh_token": "refresh-token"
    }
  }
  ```

## POST /admin/auth/refresh
- **Description**: Refreshes the authentication token.
- **Request Body**:
  ```json
  {
    "token": "expired-jwt-token"
  }
  ```
- **Response**:
  ```json
  {
    "code": 200,
    "message": "Token refreshed successfully",
    "data": {
        "jwt": "new-jwt-token",
        "refresh_token": "new-refresh-token"
    }
  }
  ```
  
## POST /admin/admin/create
- **Description**: Creates a new user by the admin. Note: This assumes the user is already a regular user to ensure information sharing is faster, easier, and secure.
- **JWT Required:** Yes
- **Request Body**:
  ```json
  {
    "email": "newuser@example.com"
  }
  ```
- **Response**:
  ```json
  {
    "code": 201,
    "message": "user created successfully",
    "data": null
  }
  ```


## POST /admin/users/update
- **Description**: Updates a admin in the system
- **JWT Required:** Yes
- **Request Body:** 
  ```json
  {
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "M",
    "address": "456 Avenue, Town",
    "email": "user@example.com",
    "new_password": "new_password",
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "user updated successfully",
      "data": null
    }
    ```
  - Error (401/500):
    ```json
    {
      "code": 401/500,
      "message": "Error message",
      "data": null
    }
    ```

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---
