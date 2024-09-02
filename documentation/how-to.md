# Project Documentation

## Setup Project
1. Clone the repository:
    ```sh
    git clone https://github.com/aditya5660/trutrip-be trutrip-be
    ```
2. Navigate to the project directory:
    ```sh
    cd trutrip-be
    ```
3. Install dependencies:
    ```sh
    composer install
    ```
4. Copy Environment Variable
    ```sh
    cp .env.example .env
    ```

    Then Setting Your Environment / Database

5. Generate App Key
    ```sh
    php artisan key:generate
    ```
5. Migrate Database & Data
    ```sh
    php artisan migrate:fresh --seed
    ```

## Run Project
1. Start the development server:
    ```sh
    php artisan serve
    ```
2. Open your browser and navigate to `http://localhost:8000` or `http://127.0.0.1:8000`.

## Run Test
1. Run Artisan Test
    ```sh
    php artisan test
    ```
