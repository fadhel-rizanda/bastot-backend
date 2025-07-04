Setting up **Laravel Octane** with **Swoole** on **Ubuntu** or **WSL** significantly boosts your Laravel application's
performance. Here's a clear guide to get you started.

-----

## Installation Steps

1. **Install Laravel Octane:**
   First, bring Octane into your Laravel project.

   ```bash
   composer require laravel/octane
   php artisan octane:install
   ```

2. **Install Swoole (Manual Build):**
   If you encounter issues with PECL, building Swoole manually is a robust alternative.

   ```bash
   git clone https://github.com/swoole/swoole-src.git
   cd swoole-src
   phpize
   ./configure
   make
   sudo make install
   ```

3. **Enable Swoole Extension:**
   Activate the newly installed Swoole PHP extension.

   ```bash
   echo "extension=swoole.so" | sudo tee /etc/php/8.4/cli/conf.d/20-swoole.ini
   ```

   **Note:** Make sure to replace `8.4` with your specific PHP version (e.g., `8.2`, `8.3`).

4. **Verify Swoole Activation:**
   Confirm that the Swoole extension is now active.

   ```bash
   php -m | grep swoole
   ```

5. **Start Laravel Octane:**
   Launch your Laravel application using Octane with the Swoole server.

   ```bash
   php artisan octane:start --server=swoole --host=127.0.0.1 --port=8000
   ```

-----

## Troubleshooting Common Errors

Here are solutions for common issues you might face during setup:

* **Port already in use:**
  If port `8000` is occupied, find the process using it and terminate it.

  ```bash
  sudo lsof -i :8000
  sudo kill -9 <PID>
  ```

  Replace `<PID>` with the Process ID you found.

* **Class "DOMDocument" not found:**
  Install the necessary PHP XML extension.

  ```bash
  sudo apt install php-xml
  ```

  You might need to specify your PHP version, e.g., `sudo apt install php8.4-xml`.

* **could not find driver (sqlite):**
  Install the PHP SQLite3 extension.

  ```bash
  sudo apt install php-sqlite3
  ```

  Similarly, you might need `sudo apt install php8.4-sqlite3` for your specific PHP version.

-----

## Optimization (Optional)

For enhanced performance, consider caching your Laravel configurations, routes, and views.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

-----

## Development Mode

To enable debugging and local development features, set these environment variables in your `.env` file:

```dotenv
APP_ENV=local
APP_DEBUG=true
```

-----

## Important Notes

* **CORS Preflight Requests (204 OPTIONS):**
  If you see `204 OPTIONS /api/all/users`, don't worry—it's not an error. The `OPTIONS` request is a **preflight request
  ** for **Cross-Origin Resource Sharing (CORS)**. The `204 No Content` response indicates the server successfully
  processed this check and grants permission for the actual request to proceed.

* **Starting Octane:**
  To kick off your Octane server:

  ```bash
  php artisan octane:start --server=swoole --host=127.0.0.1 --port=8000
  ```

* **Refreshing Octane/Cache:**
  After making code or configuration changes, it's good practice to reload Octane and refresh your caches:

  ```bash
  php artisan octane:reload
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  
  php artisan octane:reload
  php artisan config:clear
  php artisan route:clear
  php artisan cache:clear
  
  php artisan octane:stop
  php artisan octane:start
  ```

  `php artisan octane:reload` is key for Octane to pick up new code without restarting the server entirely.

* **Run the worker:**
  ```bash
  php artisan queue:work
  ```
  
* **Run the redis:**
  ```bash
  sudo service redis-server start
  redis-cli info
  sudo service redis-server stop
  ```

