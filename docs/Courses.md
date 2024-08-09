# Courses Routes

Here's the documentation for the `/courses` routes based on the provided functions:

## GET /courses
- **Description**: Retrieves a list of all courses.
- **JWT Required:** Yes
- **Query Parameters**:
  - `sort_by` (optional): Field to sort by (default is `title`).
  - `length` or `limit` (optional): Number of records to return (default is 10).
  - `start` or `offset` (optional): Number of records to skip (default is 0).
  - `search` or `search[value]` (optional): Search term to filter the courses.
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
          "course_id": 1,
          "title": "Course Title",
          "price": 100,
          "description": "Course Description",
          "author": "Author Name",
          "category_id": 1,
          "created_at": "2023-01-01 00:00:00",
          "last_updated_at": "2023-01-01 00:00:00"
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

## POST /courses/create
- **Description**: Creates a new course.
- **JWT Required:** Yes
- **Request Body**:
  ```json
  {
    "title": "New Course Title",
    "price": 100,
    "description": "Course Description",
    "author": "Author Name",
    "category": "Category Name"
  }
  ```
- **Response**:
  - Success (201):
    ```json
    {
      "code": 201,
      "message": "course created successfully",
      "data": null
    }
    ```
  - Error (401):
    - Missing fields:
      ```json
      {
        "code": 401,
        "message": "empty fields",
        "data": null
      }
      ```
    - Invalid price:
      ```json
      {
        "code": 401,
        "message": "invalid price",
        "data": null
      }
      ```
    - Course already exists:
      ```json
      {
        "code": 401,
        "message": "course exists already",
        "data": null
      }
      ```
    - Category does not exist:
      ```json
      {
        "code": 401,
        "message": "category does not exist",
        "data": null
      }
      ```
    - Author does not exist:
      ```json
      {
        "code": 401,
        "message": "author does not exist",
        "data": null
      }
      ```
    - Failed to create course:
      ```json
      {
        "code": 401,
        "message": "failed to create course",
        "data": null
      }
      ```
      
---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---
