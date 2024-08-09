# Changelog

All notable changes to the Chime Forex Trading project will be documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and adheres to Semantic Versioning.

## [Unreleased]
- Uploading Courses
- Payment Gateway [50%]

## [v2.7.0] - 2024-06-14

- Added more documentation so the project can be fully understood and can be developed more

#### Added 

- LICENSE
- createNewCourse copy.html
- docs/Cart.md
- docs/FileUpload.md
- src/Controllers/CartController.php
- src/Models/Cart.php
- src/Models/CategoriesType.php
- upload.js

#### Modified
- .env
- .gitignore
- CHANGELOG.md
- ReadMe.md
- checkout.html
- composer.json
- createNewCourse.html
- docs/Admin.md
- docs/Authentication.md
- docs/Courses.md
- docs/EnvironmentVariables.md
- docs/PasswordReset.md
- docs/Payments.md
- docs/Robots.md
- docs/Users.md
- logs/app.log
- src/Assets/email/templates/email_payment_successful.html
- src/Controllers/Admins/AdminCategoryController.php
- src/Controllers/Admins/AdminCoursesController.php
- src/Controllers/Admins/AdminRobotsController.php
- src/Controllers/AuthController.php
- src/Controllers/PaymentHandlers/FlutterwavePaymentHandler.php
- src/Controllers/PaymentHandlers/PaystackPaymentHandler.php
- src/Models/Categories.php
- src/Models/Courses.php
- src/Models/Robots.php
- src/Models/UserCourse.php
- src/Models/UserRobot.php
- src/routes.php

#### Removed

- flutterwave-php-2024-05-28.log
- flutterwave-php-2024-05-30.log
- flutterwave-php-2024-05-31.log
- src/log/flutterwave.log


## [v2.5.0] - 2024-06-05

### Added
- The frontend can now `add`, `update` and also `fetch` thier cart details.
- Payment using paystack is fully tested and completed. Including addind data into the DB
- Admin(s) can now Create Courses and Robots with thier uploaded media contents. 

## [v2.0.0] - 2024-05-31

### Added
- Added `createAdmin` method in `Admin` model for creating new admin records. (File: `src/Models/Admin.php`)
- Added `createSession` method in `AdminSession` model for managing admin sessions. (File: `src/Models/AdminSession.php`)
- Added `AdminAuthController` and `AdminJwtVerifier` for admin authentication and JWT verification. (Files: `src/Controllers/Admins/AdminAuthController.php`, `src/Controllers/Admins/AdminJwtVerifier.php`)
- Added `AdminCategoryController`, `AdminCoursesController`, and `AdminUsersController` for managing categories, courses, and users. (Files: `src/Controllers/Admins/AdminCategoryController.php`, `src/Controllers/Admins/AdminCoursesController.php`, `src/Controllers/Admins/AdminUsersController.php`)
- Added `AdminPaymentController` and `AdminRobotsController` for handling payments and robots. (Files: `src/Controllers/Admins/AdminPaymentController.php`, `src/Controllers/Admins/AdminRobotsController.php`)
- Added `Categories` and `Courses` models for managing categories and courses data. (Files: `src/Models/Categories.php`, `src/Models/Courses.php`)
- Added `Payments` and `Robots` models for handling payment-related and robot-related operations. (Files: `src/Models/Payments.php`, `src/Models/Robots.php`)
- Added detailed documentation for admin functionalities in `Admin.md`, `Authentication.md`, `Payments.md`, `UserManagement.md`, `Users.md`, `Categories.md`, and `Courses.md`. (Files: `docs/Admin.md`, `docs/Authentication.md`, `docs/Payments.md`, `docs/UserManagement.md`, `docs/Users.md`, `docs/Categories.md`, `docs/Courses.md`)
- Added `CHANGELOG.md` file to track version history and changes. (File: `CHANGELOG.md`)
- Added HTML file `createNewCourse.html` for creating a new course. (File: `createNewCourse.html`)

### Modified
- Modified `Admin` model to include methods for updating admin information. (File: `src/Models/Admin.php`)
- Modified `AdminSession` model to support session management operations. (File: `src/Models/AdminSession.php`)
- Modified `AdminAuthController` to handle admin login and authentication. (File: `src/Controllers/Admins/AdminAuthController.php`)
- Modified `AdminCategoryController` to support CRUD operations for categories. (File: `src/Controllers/Admins/AdminCategoryController.php`)
- Modified `AdminCoursesController` to handle course-related operations. (File: `src/Controllers/Admins/AdminCoursesController.php`)
- Modified `AdminUsersController` to manage user data and permissions. (File: `src/Controllers/Admins/AdminUsersController.php`)
- Modified `AdminPaymentController` to integrate payment handling functionality. (File: `src/Controllers/Admins/AdminPaymentController.php`)
- Modified `AdminRobotsController` to include robot management capabilities. (File: `src/Controllers/Admins/AdminRobotsController.php`)
- Modified `User` model to update user information and permissions. (File: `src/Models/User.php`)
- Modified `bootstrap.php` to include necessary configurations and dependencies. (File: `src/bootstrap.php`)
- Modified routing logic in `routes.php` to handle new admin endpoints. (File: `src/routes.php`)

### Deleted
- Removed `Category` model as category management is now handled by `Categories` model. (File: `src/Models/Category.php`)
- Removed obsolete files and logs related to previous implementations. (Files: `docs/Robots.md`, `flutterwave-php-2024-05-28.log`, `flutterwave-php-2024-05-30.log`)
- Cleaned up unused code and dependencies. (Files: `src/log/flutterwave.log`)

### Untracked
- Untracked log files for payment and robot processing. (Files: `flutterwave-php-2024-05-31.log`)
- Untracked HTML file for creating a new course. (File: `createNewCourse.html`)
- Untracked controllers and models for managing categories, courses, payments, and robots. (Files: `src/Controllers/Admins/AdminPaymentController.php`, `src/Controllers/Admins/AdminRobotsController.php`, `src/Models/Categories.php`, `src/Models/Courses.php`, `src/Models/Payments.php`, `src/Models/Robots.php`)


## [v1.0.20] - 2024-05-28

#### Added
- Added the `role` attribute to the Admin Login Response

#### Fixed
- Attempted to fix the `refreshToken`for regular users


## [v1.0.19] - 2024-05-28
#### Added
- Added the `generateRefreshToken` to be `square` of the initial value set in the `.env` file

#### Modified
- Fixed the `refresh token` expkiring fast or not even being valid at all

## [v1.0.18] - 2024-05-27
- Fixed the duplication error of duplications of errors using route `/user/details?email=someone@domain.com`

## [v1.0.17] - 2024-05-27
- Fixed the error of var dumping twice.
## [v1.0.16] - 2024-05-27
#### Added
- Added a new route `/admin/admin/update` to be able to update other admins info.

#### Modified
- Modified the `login`method in the `AuthController` for the users so that testing for expired tokens will be fully tested.

#### Removed
- Removed/changed the route to create a new admin, from `/admin/users/create` to `/admin/admin/create`. `NOTE: ` The payload still remain the same. No diffrence.


## [v1.0.14] - 2024-05-25
- Updated DB file

## [v1.0.13] - 2024-05-25
- Made chhanges to the Admin and also set the jwt token timer in `AuthController` and `AdminAuthController` in thier login forms. `NOTE:` the duration passed there is in seconds

## [v1.0.12] - 2024-05-24

### Added
- New documentation for Admin functionalities (`docs/Admin.md`)
- New email templates for admin notifications (`src/Assets/email/templates/email_new_admin_admin_message.html`, `src/Assets/email/templates/email_new_admin_message.html`)
- New Admin authentication and JWT verification controllers (`src/Controllers/Admins/AdminAuthController.php`, `src/Controllers/Admins/AdminJwtVerifier.php`)
- New Admin and AdminSession models (`src/Models/Admin.php`, `src/Models/AdminSession.php`)

### Changed
- Updated environment configuration (`.env`)
- Updated README with new Admin API routes
- Refined user models (`src/Models/User.php`, `src/Models/UserSession.php`)
- Updated application bootstrap and settings (`src/bootstrap.php`, `src/settings.php`)
- Modified routes to include new Admin routes (`src/routes.php`)



## [1.0.9] - 2024-05-23
- Added

## [1.0.8] - 2024-05-23
- Payment useing Flutterwave is working perfectly working now remainging to vewrify transactions and add to the database

## [1.0.7] - 2024-05-23
- Fininshed and confirming implementation of payment using paystack.

## [1.0.6] - 2024-05-23
- Updated the routes and added email for information updated.
- Modified the SQL code

## [1.0.5] - 2024-05-23

- Finished the payment using paystack and created the checkout UI

## [1.0.4] - 2024-05-22

### Added
- Implemented begining pahse of payment using paystack

### Fixed
- Fixed he CORS servrt error. Will continue with payment 


## [1.0.3] - 2024-05-22
### Added
- Helper: `JsonResponder` for generating JSON responses.
- Controller: `PasswordResetController` for handling password reset functionality.
- API route: `POST /password/request-reset` to request a password reset.
- API route: `GET /password/verify-token` to verify the password reset token.
- API route: `POST /password/reset` to reset the password.

## [1.0.2] - 2024-05-22
### Added
- API route: `GET /user/details` to retrieve user details based on email.
- API route: `POST /user/details/update` to update user details.
- API route: `POST /auth/refresh` to refresh JWT tokens.
- Method: `getUser` in `AuthController` to fetch user details.
- Method: `updateUser` in `AuthController` to update user details.
- Method: `refresh` in `AuthController` to refresh JWT tokens.

### Changed
- Dynamically fetch user details via query parameters instead of URL path parameters.
- Improved error handling and code comments in `AuthController`.
- Fixed nested ternary expressions in error handling.

## [1.0.0] - 2024-05-21
### Added
- Initial project setup.
- Environment variables configuration.
- API routes for authentication (login, signup, details, confirm-token).
- `JwtMiddleware` for JWT token generation and decoding.
- Models: `User` and `UserSession` for database interaction.
- Helpers: `URLEncode` and `RandomStringGenerator`.
- Class: `EmailSender` for sending HTML emails.

### Changed
- Updated README.md with environment variables, API routes, and controller details.
- Improved error handling and code comments in `AuthController`.

### Fixed
- Fixed token decoding issue in `decodeToken` function.

## [0.1.0] - 2024-05-19
### Added
- Initial project skeleton.
- Basic folder structure.
- Slim Framework setup.
- Composer dependencies.
