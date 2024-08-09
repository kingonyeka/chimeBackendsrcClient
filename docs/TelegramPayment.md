# Telegram Payments

### Overview

The Payments feature enables users to process payments for joining a Telegram group using Paystack.

---


#### Initialize Payment

- **URL:** `/payments/telegram/initialize`
- **Method:** POST
- **Response:**
    ```json
    {
        "code": 200,
        "message": "Payment URL for Paystack",
        "data": {
            "payment_url": "https://paystack.com/pay/nfsfu-chime-test"
        }
    }
    ```

#### Payment Callback

- **URL:** `/payments/telegram/callback`
- **Method:** GET
- **Query Parameters:**
  - `reference`: Transaction reference
- **Response:** Redirects to the checkout URL with the status and message in the query parameters.
    - **Success Redirect Example:**
      ```
      https://your-frontend-url.com/checkout?status=success&message=payment%20verified%20and%20processed%20successfully
      ```
    - **Failure Redirect Example:**
      ```
      https://your-frontend-url.com/checkout?status=failed&message=payment%20failed%20or%20was%20not%20completed
      ```

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---