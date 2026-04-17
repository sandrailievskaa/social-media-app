# Mini Social Media Platform

Mini social media platform built with **Laravel 12**, **Livewire (Volt)**, **Tailwind CSS**, and **MySQL**, following a DDD-ish structure under `app/Domain/*`.

## Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL

### Install

```bash
git clone <your-repo-url>
cd social-app
composer install
cp .env.example .env
php artisan key:generate
```

### Configure `.env`

Set your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_app
DB_USERNAME=...
DB_PASSWORD=...
```

### Migrate + seed

```bash
php artisan migrate:fresh --seed
```

### Build assets + run

```bash
npm install
npm run build
php artisan serve
```

Open the app at `http://localhost:8000`.

## Demo login

- Email: `demo@example.com`
- Password: `password`

## Implemented features

- **Auth** (Laravel Breeze / Blade)
- **Feed** (Volt)
  - Followed-users feed with discover fallback
  - Reactions (toggle logic)
- **Posts**
  - Create post (Volt) with multi-file uploads (image/video validation)
  - Post show (Volt) with comments
- **Comments**
  - Nested replies (one level deep)
  - Edit/delete your own comments
  - Reactions on comments
- **Profiles**
  - Profile show (Volt) with follow/unfollow
  - Profile edit (Volt) with avatar/cover upload previews
- **Seeders**
  - Realistic users, profiles, follows, posts w/ media URLs, comments + replies, reactions
- **Feature tests** (Pest)
  - Reactions, comments, feed behavior, media validation, follows

## AI assistance

This project was built with help from **Cursor (GPT-5)**, following the rules and architecture defined in `social-media-AGENT.md`.
