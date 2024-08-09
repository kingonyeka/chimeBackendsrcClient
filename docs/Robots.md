## Admin Robots Management (/admin/robots)

This section covers the routes related to managing robots in the admin panel.

---

### Get All Robots

- **Description**: Retrieves a list of all robots.
- **Method**: GET
- **Route**: `/admin/robots/`
- **JWT Required**: Yes
- **Query Parameters**:
  - `sort_by` (optional): Specifies the field to sort the results by. Default is `title`.
  - `length` or `limit` (optional): Specifies the number of items to fetch per page. Default is 10.
  - `start` or `offset` (optional): Specifies the starting index of the items to fetch. Default is 0.
  - `search` (optional): Specifies a search value to filter the results.
  - `order[0][dir]` (optional): Specifies the sort order (asc or desc). Default is asc.
- **Response**:
  ```json
  {
    "code": 200,
    "message": "fetched successfully",
    "items": 10,
    "totalRecords": 50,
    "filteredRecords": 50,
    "data": [
      {
        "robot_id": "robot-id",
        "title": "Robot Title",
        "price": 1000,
        "description": "Robot Description",
        "author": "admin-user-id",
        "cat_id": "category-id",
        "created_at": "2024-05-31T12:00:00Z",
        "last_updated_at": "2024-05-31T12:00:00Z"
      },
      { ... }
    ]
  }
  ```

---

### Create New Robot

- **Description**: Creates a new robot.
- **Method**: POST
- **Route**: `/admin/robots/create`
- **JWT Required**: Yes
- **Request Body**:
  ```json
  {
    "title": "Robot Title",
    "price": 1000,
    "description": "Robot Description",
    "author": "admin-user-id",
    "category": "category-id"
  }
  ```
- **Response**:
  - Success (201):
    ```json
    {
      "code": 201,
      "message": "robot created successfully",
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