# Cart

#### Overview

The Cart feature allows users to manage their shopping carts, including adding, updating, fetching, and removing products from their carts.

#### Endpoints

### 1. Upsert Cart
- **URL:** `/cart/upsert`
- **Method:** POST
- **Payload:**
    ```json
    {
        "email": "user@example.com",
        "products": [
            {
                "slug": "product-slug-1",
                "quantities": 2
            },
            {
                "slug": "product-slug-2",
                "quantities": 1
            }
        ]
    }
    ```
- **Response:**
    ```json
    {
        "code": 200,
        "message": "cart processed successfully",
        "data": {
            "total": 2,
            "total_valid": 2,
            "total_invalid": 0,
            "valid_products": [
                {
                    "slug": "product-slug-1",
                    "quantities": 2,
                    "type": "course",
                    "title": "Course Title",
                    "author": "Author Name",
                    "price": 100.00
                },
                {
                    "slug": "product-slug-2",
                    "quantities": 1,
                    "type": "robot",
                    "title": "Robot Title",
                    "author": "Author Name",
                    "price": 200.00
                }
            ],
            "invalid_products": []
        }
    }
    ```

### 2. Fetch Cart by Email
- **URL:** `/cart/fetch`
- **Method:** GET
- **Query Parameters:**
  - `email`: User's email address
- **Response:**
    ```json
    {
        "code": 200,
        "message": "cart fetched successfully",
        "data": {
            "total_items": 3,
            "total_amount": 400.00,
            "products": [
                {
                    "slug": "product-slug-1",
                    "quantities": 2,
                    "type": "course",
                    "title": "Course Title",
                    "author": "Author Name",
                    "price": 100.00
                },
                {
                    "slug": "product-slug-2",
                    "quantities": 1,
                    "type": "robot",
                    "title": "Robot Title",
                    "author": "Author Name",
                    "price": 200.00
                }
            ]
        }
    }
    ```

### 3. Delete Cart
- **URL:** `/cart/delete`
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
        "message": "Cart deleted successfully",
        "data": null
    }
    ```

### 4. Remove Item from Cart
- **URL:** `/cart/remove-item`
- **Method:** POST
- **Payload:**
    ```json
    {
        "email": "user@example.com",
        "products": [
            {
                "slug": "product-slug-1"
            }
        ]
    }
    ```
- **Response:**
    ```json
    {
        "code": 200,
        "message": "cart updated successfully",
        "data": {
            "total": 1,
            "total_valid": 1,
            "total_invalid": 0,
            "remaining_products": [
                {
                    "slug": "product-slug-2",
                    "quantities": 1,
                    "type": "robot",
                    "title": "Robot Title",
                    "author": "Author Name",
                    "price": 200.00
                }
            ],
            "invalid_products": []
        }
    }
    ```

### Cart Routes

```php
$app->group('/cart', function (RouteCollectorProxy $group) {
    $group->post('/upsert', CartController::class . ':upsert'); // Unified create/update route
    $group->get('/fetch', CartController::class . ':fetchByEmail'); // Fetch by user email
    $group->post('/delete', CartController::class . ':delete'); // Delete by cart ID
    $group->post('/remove', CartController::class . ':removeItem'); // Delete by cart ID
})->add(new JwtMiddleware($app->getContainer()->get('settings')['jwt']['secret']));
```

#### CartController Methods

- **upsert**: Handles the creation or update of a user's cart, merging existing products and updating quantities as necessary.
- **fetchByEmail**: Retrieves the cart details for a user based on their email address.
- **delete**: Clears all products from a user's cart.
- **removeItem**: Removes specific products from a user's cart based on the provided slugs.

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---
