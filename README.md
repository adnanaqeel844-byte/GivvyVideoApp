Bilkul! Yahaan ek comprehensive `README.md` file hai jo aapke `Givvy Videos Backend` (Laravel PHP) project ko describe karegi, jismein setup instructions, API endpoints, aur other important details shamil hain.

---

# Givvy Videos Backend

This repository contains the backend API for a "Givvy Videos" like application, built using the Laravel PHP Framework. This backend manages user accounts, video data, earning logic, referral systems, and withdrawal requests. The mobile application (frontend, e.g., Android in Kotlin) will interact with these APIs to function.

## Table of Contents

-   [Features](#features)
-   [Prerequisites](#prerequisites)
-   [Installation](#installation)
-   [Environment Configuration](#environment-configuration)
-   [Database Setup](#database-setup)
-   [Running the Server](#running-the-server)
-   [API Endpoints](#api-endpoints)
    -   [Authentication](#authentication)
    -   [User Management](#user-management)
    -   [Videos & Earnings](#videos--earnings)
    -   [Withdrawals](#withdrawals)
-   [Future Improvements / Considerations](#future-improvements--considerations)
-   [Security Notes](#security-notes)
-   [Contributing](#contributing)
-   [License](#license)

## Features

-   **User Authentication:** Register, Login, Logout using Laravel Sanctum for API token management.
-   **Video Management:** API to fetch a list of videos and individual video details.
-   **Earning System:** Reward users for watching videos and ads.
-   **Referral System:** Track user referrals and award bonuses to referrers and referred users.
-   **Wallet Management:** Users have a balance that increases with earnings.
-   **Withdrawal System:** Users can request withdrawals to various platforms (PayPal, Coinbase, etc.), which an admin would later process.
-   **Database:** Structured database schema for users, videos, earnings, and withdrawals.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

-   **PHP >= 8.1:** Laravel 10 requires PHP 8.1 or higher.
-   **Composer:** PHP dependency manager.
    -   [Download Composer](https://getcomposer.org/download/)
-   **Node.js & npm (Optional, for frontend asset compilation if using Laravel Mix):** Not strictly required for the backend API itself, but useful for full Laravel projects.
-   **A Database Server:** MySQL (recommended), PostgreSQL, SQLite, or SQL Server.
-   **Web Server:** Apache or Nginx (for production deployment). For local development, Laravel's built-in server is sufficient.

## Installation

1.  **Clone the repository (or create a new Laravel project):**
    If you're starting fresh, use Composer to create a new Laravel project:
    ```bash
    composer create-project laravel/laravel givvy_videos_backend
    cd givvy_videos_backend
    ```
    If you cloned an existing project, navigate into its directory:
    ```bash
    cd givvy_videos_backend
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Install Laravel Sanctum (for API Authentication):**
    ```bash
    composer require laravel/sanctum
    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
    php artisan migrate
    ```

## Environment Configuration

1.  **Copy the Environment File:**
    ```bash
    cp .env.example .env
    ```

2.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

3.  **Edit `.env` File:**
    Open the newly created `.env` file and configure your database and other application settings.

    ```dotenv
    APP_NAME="Givvy Videos Backend"
    APP_ENV=local
    APP_KEY=base64:YOUR_GENERATED_KEY_HERE
    APP_DEBUG=true
    APP_URL=http://localhost:8000 # Or your actual domain/IP if deploying

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=givvy_videos_db # Choose a database name
    DB_USERNAME=root          # Your database username
    DB_PASSWORD=              # Your database password
    ```
    Make sure your database server is running and the specified database (`givvy_videos_db` in the example) exists.

## Database Setup

1.  **Run Migrations:**
    This will create the necessary tables (`users`, `videos`, `earnings`, `withdrawals`) in your database.
    ```bash
    php artisan migrate
    ```

2.  **Seed the Database (Optional):**
    You can create fake data for testing using seeders. (If you've created `DatabaseSeeder.php` with seed logic).
    ```bash
    php artisan db:seed
    ```
    *Example: Create `DatabaseSeeder.php` if you want to add initial videos for testing:*
    ```bash
    # Create seeder file
    php artisan make:seeder VideoSeeder

    # Edit database/seeders/VideoSeeder.php
    <?php
    namespace Database\Seeders;
    use Illuminate\Database\Seeder;
    use App\Models\Video;

    class VideoSeeder extends Seeder
    {
        public function run(): void
        {
            Video::create([
                'title' => 'Sample Video 1',
                'thumbnail_url' => 'https://example.com/thumb1.jpg',
                'video_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4',
                'category' => 'Entertainment',
                'genre' => 'Pop',
                'reward_amount' => 0.005,
                'duration_seconds' => 60,
            ]);
            Video::create([
                'title' => 'Sample Video 2',
                'thumbnail_url' => 'https://example.com/thumb2.jpg',
                'video_url' => 'https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/1080/Big_Buck_Bunny_1080_10s_1MB.mp4',
                'category' => 'Education',
                'genre' => 'Documentary',
                'reward_amount' => 0.010,
                'duration_seconds' => 120,
            ]);
        }
    }
    ```
    Then, modify `database/seeders/DatabaseSeeder.php` to call `VideoSeeder`:
    ```php
    <?php
    namespace Database\Seeders;
    use Illuminate\Database\Seeder;

    class DatabaseSeeder extends Seeder
    {
        public function run(): void
        {
            $this->call(VideoSeeder::class);
        }
    }
    ```
    Finally, run `php artisan db:seed` to add these videos.

## Running the Server

To run the backend locally using Laravel's built-in development server:

```bash
php artisan serve
```

This will typically start the server at `http://127.0.0.1:8000`. You can now access your API endpoints at this base URL.

## API Endpoints

All API endpoints are prefixed with `/api`. For example, a request to login would be `http://127.0.0.1:8000/api/login`.

**Authentication is handled by Laravel Sanctum.** For authenticated routes, you need to send an `Authorization` header with a Bearer token (obtained from the login endpoint): `Authorization: Bearer YOUR_AUTH_TOKEN`.

### 1. Authentication

-   **`POST /api/register`**
    -   **Description:** Register a new user.
    -   **Request Body:** `name`, `email`, `password`, `password_confirmation`, `referral_code_applied` (optional).
    -   **Response:** User data and authentication token.
-   **`POST /api/login`**
    -   **Description:** Log in an existing user.
    -   **Request Body:** `email`, `password`.
    -   **Response:** User data and authentication token.
-   **`POST /api/logout`** (Authenticated)
    -   **Description:** Log out the authenticated user by revoking their current token.
    -   **Response:** Success message.

### 2. User Management (Authenticated)

-   **`GET /api/user/profile`**
    -   **Description:** Get the authenticated user's profile and current balance.
    -   **Response:** User object.
-   **`GET /api/user/earnings`**
    -   **Description:** Get the authenticated user's earning history.
    -   **Response:** Array of earning records.
-   **`GET /api/user/withdrawal-history`**
    -   **Description:** Get the authenticated user's withdrawal request history.
    -   **Response:** Array of withdrawal records.

### 3. Videos & Earnings (Authenticated)

-   **`GET /api/videos`**
    -   **Description:** Get a list of all available videos.
    -   **Response:** Array of video objects.
-   **`GET /api/videos/{video}`**
    -   **Description:** Get details of a specific video by its ID.
    -   **Response:** Single video object.
-   **`POST /api/videos/{video}/watch`**
    -   **Description:** Record that the user watched a video and award earnings. Includes basic anti-spam logic.
    -   **Response:** Success message and new user balance.
-   **`POST /api/ads/watch`**
    -   **Description:** Record that the user watched a rewarded ad and award earnings. Includes basic anti-spam logic.
    -   **Response:** Success message and new user balance.

### 4. Withdrawals (Authenticated)

-   **`POST /api/user/withdrawal`**
    -   **Description:** Submit a withdrawal request for the authenticated user. Checks for sufficient balance and minimum withdrawal amount.
    -   **Request Body:** `amount` (numeric), `method` (string, e.g., "PayPal", "Coinbase"), `account_details` (string, e.g., PayPal email).
    -   **Response:** Success message, withdrawal record, and new user balance.

## Future Improvements / Considerations

-   **Admin Panel:** A dedicated interface for administrators to manage users, videos, approve/reject withdrawal requests, and view analytics.
-   **Robust Anti-Fraud System:** More advanced checks for video/ad watches (e.g., duration validation, IP/device fingerprinting, anomaly detection).
-   **Server-Side Ad Validation:** Implement callbacks/webhooks from ad networks (like AdMob) to verify ad watches on the server, rather than relying solely on client-side reports.
-   **YouTube API Integration:** Dynamically fetch video details and stream URLs from YouTube if the app focuses on YouTube content.
-   **Queue System:** Use Laravel Queues for background tasks like processing withdrawals, sending notifications, or heavy data processing to improve API response times.
-   **Notifications:** Implement push notifications for earnings, withdrawal status, etc.
-   **Caching:** Cache frequently accessed data (e.g., video lists) to improve performance.
-   **Testing:** Comprehensive unit and feature tests for all API endpoints and business logic.
-   **Deployment Strategy:** Production-ready deployment setup with Nginx/Apache, supervising queues, monitoring, etc.

## Security Notes

-   **Input Validation:** All incoming requests are validated to prevent common vulnerabilities.
-   **Password Hashing:** Passwords are securely hashed using `bcrypt`.
-   **API Tokens:** Laravel Sanctum provides secure, stateful API authentication for SPAs and mobile applications.
-   **Environment Variables:** Sensitive information is stored in the `.env` file and kept out of version control.
-   **CSRF Protection:** Not strictly needed for pure API endpoints (which don't use sessions for auth), but standard Laravel web routes include it. Sanctum handles token-based auth.

## Contributing

Contributions are welcome! Please follow these steps:
1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/your-feature-name`).
3.  Make your changes.
4.  Commit your changes (`git commit -m 'Add new feature'`).
5.  Push to the branch (`git push origin feature/your-feature-name`).
6.  Create a Pull Request.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This project, as a derivative, generally follows the same.

---
