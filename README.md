# FitTrack - Fitness and Meal Tracking Application

FitTrack is a comprehensive web application designed to help users track their fitness progress and nutritional intake. It provides a platform for users to follow personalized workout and meal plans, monitor their performance, and achieve their health and fitness goals. The application is built with a modern tech stack, featuring a Laravel backend and a React frontend with Inertia.js.

## Key Features

- **User Authentication:** Secure user registration and login system.
- **Dashboard:** A personalized dashboard that provides an overview of the user's weekly progress, current streak, active plans, and recent activities.
- **Workout Plans:** Users can be assigned workout plans with specific exercises for each day of the week. They can mark workouts as complete to track their progress.
- **Meal Plans:** Users can be assigned meal plans with detailed nutritional information. They can track their daily food intake by marking meals as consumed.
- **Admin Panel:** A powerful admin panel built with Filament for managing users, workout plans, meal plans, workouts, and meals.
- **Data Scraping:** A command-line interface for scraping workout and meal data from JSON files to populate the database.
- **Responsive Design:** A mobile-friendly design that works seamlessly across all devices.

## Tech Stack

- **Backend:** Laravel
- **Frontend:** React, Inertia.js, TypeScript, Tailwind CSS
- **Database:** MySQL (or any other Laravel-supported database)
- **Admin Panel:** Filament

## Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js and npm
- A database server (e.g., MySQL, PostgreSQL)

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/your-username/fittrack.git
   cd fittrack
   ```

2. **Install PHP dependencies:**

   ```bash
   composer install
   ```

3. **Install JavaScript dependencies:**

   ```bash
   npm install
   ```

4. **Create a copy of the `.env.example` file and name it `.env`:**

   ```bash
   cp .env.example .env
   ```

5. **Generate an application key:**

   ```bash
   php artisan key:generate
   ```

6. **Configure your database credentials in the `.env` file:**

   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fittrack
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Run the database migrations:**

   ```bash
   php artisan migrate
   ```

8. **Seed the database with initial data:**

   ```bash
   php artisan db:seed
   ```

9. **Scrape workout and meal data:**

   ```bash
   php artisan app:scrape-workouts
   php artisan app:scrape-meals
   ```

10. **Build the frontend assets:**

    ```bash
    npm run dev
    ```

11. **Start the development server:**

    ```bash
    php artisan serve
    ```

You can now access the application at `http://localhost:8000`.

### Admin Access

- **URL:** `http://localhost:8000/admin`
- **Email:** `admin@admin.com`
- **Password:** `admin`

### User Access

- **URL:** `http://localhost:8000`
- **Email:** `user@user.com`
- **Password:** `user`

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue to discuss your ideas.

## License

This project is open-source and available under the [MIT License](LICENSE).
