# Categories

#### Overview

The Categories feature enables users to manage categories in the system, including creating new categories and fetching all categories.

## Endpoints

### 1. Get All Categories
- **URL:** `/categories`
- **Method:** GET
- **Headers:**
  - `Authorization: Bearer <token>`
- **Query Parameters:**
  - `sort_by` (optional): Field to sort by, default is `created_at`
  - `limit` (optional): Number of records to return, default is `10`
  - `offset` (optional): Number of records to skip, default is `0`
  - `search` (optional): Search value
  - `order[0][dir]` (optional): Order direction, default is `desc`
- **Response:**
    ```json
    {
        "code": 200,
        "message": "fetched successfully",
        "items": 2,
        "totalRecords": 5,
        "filteredRecords": 5,
        "data": [
            {
                "title": "Category 1",
                "description": "Description for category 1",
                "cat_id": "cat1",
                "created_at": "2023-01-01 12:00:00",
                "last_updated_at": "2023-01-01 12:00:00"
            },
            {
                "title": "Category 2",
                "description": "Description for category 2",
                "cat_id": "cat2",
                "created_at": "2023-01-02 12:00:00",
                "last_updated_at": "2023-01-02 12:00:00"
            }
        ]
    }
    ```

### 2. Create New Category
- **URL:** `/categories/create`
- **Method:** POST
- **Headers:**
  - `Authorization: Bearer <token>`
- **Payload:**
    ```json
    {
        "name": "New Category",
        "description": "Description for new category"
    }
    ```
- **Response:**
    ```json
    {
        "code": 201,
        "message": "category created successfully",
        "data": null
    }
    ```

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---