## Payments

#### Overview

The Payments feature enables users to process payments using various payment vendors such as Paystack, Flutterwave.

#### Endpoints

## 1. Process Payment
- **URL:** `/payments/process`
- **Method:** POST
- **Payload:**
    ```json
    {
        "amount": 1000,
        "currency": "USD",
        "email": "user@example.com",
        "metadata": {
            "order_id": "12345",
            "description": "Product purchase"
        },
        "vendor": "paystack"
    }
    ```
- **Response:**
    ```json
    {
        "code": 200,
        "message": "Payment processed successfully with Paystack",
        "data": {
            "amount": 1000,
            "currency": "USD",
            "email": "user@example.com",
            "metadata": {
                "order_id": "12345",
                "description": "Product purchase"
            },
            "vendor": "paystack"
        }
    }
    ```

## 2. Paystack Callback
- **URL:** `/payments/paystack/callback`
- **Method:** GET
- **Query Parameters:**
  - `trxref`: Transaction reference
- **Response:**
    ```json
    {
        "code": 200,
        "message": "Payment verified and processed successfully"
    }
    ```

## 3. Flutterwave Initialize
- **URL:** `/payments/flutterwave/initialize`
- **Method:** POST
- **Payload:**
    ```json
    {
        "amount": 1000,
        "currency": "USD",
        "email": "user@example.com",
        "metadata": {
            "order_id": "12345",
            "description": "Product purchase"
        }
    }
    ```
- **Response:**
    ```json
    {
        "code": 200,
        "message": "Payment Initialized Successfully",
        "data": {
            "redirect_url": "https://flutterwave.com/redirect"
        }
    }
    ```

## 4. Flutterwave Callback
- **URL:** `/payments/flutterwave/callback`
- **Method:** POST
- **Payload:** (Callback data from Flutterwave)
- **Response:**
    ```json
    {
        "code": 200,
        "message": "Callback processed successfully"
    }
    ```

### Payment Vendors

1. **Paystack**
   - URL: https://paystack.com/
   - Description: Paystack is a payment gateway that allows businesses in Nigeria to accept payments from various sources.

2. **Flutterwave**
   - URL: https://flutterwave.com/
   - Description: Flutterwave provides a platform for businesses and individuals to make and receive payments across Africa and beyond.

---

## Admin Payments Management (/admin/payments)

### Get All Payments

- **Description**: Retrieves a list of all payments.
- **Method**: GET
- **Route**: `/admin/revenues/`
- **JWT Required**: Yes
- **Query Parameters**:
  - `sort_by` (optional): Specifies the field to sort the results by. Default is `created_at`.
  - `length` or `limit` (optional): Specifies the number of items to fetch per page. Default is 10.
  - `start` or `offset` (optional): Specifies the starting index of the items to fetch. Default is 0.
  - `search` (optional): Specifies a search value to filter the results.
  - `order[0][dir]` (optional): Specifies the sort order (asc or desc). Default is desc.
- **Response**:
  ```json
  {
    "code": 200,
    "message": "fetched successfully",
    "totalAmountMade": 5000,
    "items": 10,
    "totalRecords": 50,
    "filteredRecords": 50,
    "data": [
      {
        "payment_id": "payment-id",
        "amount": 1000,
        "user_id": "user-id",
        "status": "completed",
        "created_at": "2024-05-31T12:00:00Z",
        "updated_at": "2024-05-31T12:00:00Z"
      },
      { ... }
    ]
  }
  ```

---

## Payments Routes

```php
$app->group('/payments', function (RouteCollectorProxy $group) {
    $group->post('/process', PaymentController::class . ':processPayment');

    // Define a route group for Paystack callbacks
    $group->group('/paystack', function ($group) {
        $group->get('/callback', PaymentController::class . ':handlePaystackCallback');
    });

    // Define a route group for Flutterwave callbacks
    $paymentHandler = new FlutterwavePaymentHandler();

    $group->group('/flutterwave', function ($group) use ($paymentHandler) {
        $group->post('/initialize', PaymentController::class . ':initializePayment');
        $group->post('/callback', PaymentController::class . ':processCallback');
    });

});
```

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---