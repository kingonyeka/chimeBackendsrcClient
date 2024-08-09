# Users Routes

## GET /users
- **Description**: Retrieves a list of all users with their payment status and associated courses.
- **JWT Required:** Yes
- **Query Parameters**:
  - `sort_by` (optional): Field to sort by (default is `first_name`).
  - `length` or `limit` (optional): Number of records to return (default is 10).
  - `start` or `offset` (optional): Number of records to skip (default is 0).
  - `search` (optional): Search term to filter the users.
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
          "user_id": 1,
          "first_name": "John",
          "last_name": "Doe",
          "email": "john.doe@example.com",
          "hasUserPaid": true,
          "courses": [
            {"course_id": 1, "course_name": "Course 1"},
            {"course_id": 2, "course_name": "Course 2"}
          ],
          "robots": [
            {"robot_id": 1, "robot_name": "Robot 1"},
            {"robot_id": 2, "robot_name": "Robot 2"}
          ],
          "total_amount": 150.00
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

## GET /user/details
- **Description**: Retrieves details of the authenticated user.
- **JWT Required:** Yes
- **Response**:
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "User details fetched successfully",
      "data": {
        "user_id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "address": "123 Street, City, Country",
        "phone_number": "+1234567890",
        "courses": [
          {"course_id": 1, "course_name": "Course 1"},
          {"course_id": 2, "course_name": "Course 2"}
        ],
        "robots": [
          {"robot_id": 1, "robot_name": "Robot 1"},
          {"robot_id": 2, "robot_name": "Robot 2"}
        ],
      }
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

## POST /user/details/update
- **Description**: Updates the authenticated user's details.
- **JWT Required:** Yes
- **Request Body:**
  ```json
  {
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "M",
    "address": "456 Avenue, Town",
    "phone_number": "+0987654321",
    "old_password": "old_password",
    "new_password": "new_password",
    "confirm_new_password": "new_password"
  }
  ```
- **Note:** All parameters are optional except for the password update, which requires `old_password`, `new_password`, and `confirm_new_password` to be provided together.
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "User details updated successfully",
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

## POST /admin/users/ban
- **Description**: Bans a user from the system.
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
    "code": 200,
    "message": "User banned successfully",
    "data": null
  }
  ```

## POST /admin/users/update
- **Description**: Updates a user in the system.
- **JWT Required:** Yes
- **Request Body:** `NOTE:` All parameters are optional, except for passwords, which require all 3 attributes `old_password`, `new_password`, `confirm_new_password` to be provided if the password is to be changed.
  ```json
  {
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "M",
    "address": "456 Avenue, Town",
    "email": "user@example.com",
    "old_password": "old_password",
    "new_password": "new_password",
    "confirm_new_password": "new_password"
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "User updated successfully",
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

This documentation provides a comprehensive overview of the user-related API routes, including endpoints for retrieving, updating, and managing users.


---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---