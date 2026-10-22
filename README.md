# URL Tree

A desktop app that organizes saved URLs into a tree of folders based on SEO
keywords. Paste a URL, and the app scrapes its title, description, and
keywords, then suggests where it belongs. You confirm or choose another
folder, and the tree learns from each placement.

## Stack

- Laravel 13 (PHP 8.4+) with SQLite
- Alpine.js + Tailwind (Blade views)
- Electron shell that runs the Laravel server locally

## Development

1. `composer install` and `npm install` in the project root
2. `php artisan migrate`
3. `npm run build`
4. `cd electron-shell && npm install && npm start`

Electron starts `php artisan serve` for you, so don't run it separately.
