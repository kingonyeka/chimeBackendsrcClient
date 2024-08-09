# Environment Variables

To set up the Chime Forex Trading project, create a `.env` file in the root directory of your project and add the following environment variables with their corresponding values:

```plaintext
# SMTP Configuration
SMTP_HOST=smtp.hostinger.com
SENDER_EMAIL=noreply@chimetrading.com
SENDER_PASSWORD=chimeShifuXtephen@24
SMTP_SECURE=ssl
SMTP_PORT=465
IMAP_PORT=993
POP3_PORT=995
SMTP_FROM_ADDRESS=noreply@chimetrading.com
SMTP_FROM_NAME=NoReply

SUPERADMIN_EMAIL=noreply@chimetrading.com

# Application Configuration
APP_ENV=development
APP_DEBUG=true

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=chime_db
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your_jwt_secret_key

# Application URL
APPLICATION_FRONTEND_URL=https://chime-five.vercel.app
APPLICATION_BACKEND_URL=http://localhost:8000

REFRESH_TOKEN_EXPIRY=3600  # 1 hour in seconds

PAYSTACK_SECRET_KEY=sk_test_------------------

PUBLIC_KEY=FLWPUBK_TEST-___________
SECRET_KEY=FLWSECK_TEST-___________
ENCRYPTION_KEY=FLWSECK_------------
ENV='staging'
```

Replace `your_smtp_host`, `your_sender_email`, `your_sender_password`, `your_from_address`, and `your_jwt_secret_key` with your actual SMTP and JWT secret key values.

---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---