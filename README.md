# Real-Time Live Polling Platform

## Working URL
- Local: `http://localhost:8000/login`
- Share URL (temporary): `https://juice-months-wisconsin-algorithm.trycloudflare.com/login`

## Demo Credentials
- Email: `admin@livepoll.com`
- Password: `password`

## Features Implemented
- Login-only access for polling pages.
- Active poll listing with AJAX-based poll detail loading.
- One active vote allowed per `poll + IP`.
- Real-time results update without page refresh (1-second polling).
- Admin poll creation with status (`Active/Inactive`) and multiple options.
- Admin IP release to unlock re-voting.
- Vote audit history preserved (released + re-voted records visible).

## Tech Stack
- Laravel (routing, auth/session, MVC, views)
- Core PHP logic in controllers for vote restriction and moderation
- Blade + Bootstrap + jQuery + AJAX
- SQLite/MySQL-compatible migrations (currently configured with SQLite)

## Quick Setup
1. Install dependencies:
   - `composer install`
2. Generate app key:
   - `php artisan key:generate`
3. Run migrations:
   - `php artisan migrate`
4. Start app:
   - `php artisan serve`

## Test Steps
1. Login using demo credentials.
2. Open Dashboard and vote on a poll.
3. Try voting again from same IP (should be blocked).
4. Open Admin Panel and click **Release IP**.
5. Vote again from Dashboard (should be allowed now).
6. Open **View History** in Admin to verify both previous and new votes.
