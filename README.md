# Glitter Issue Tracker

## Project Overview

- Internal issue tracker for a small team.
- Laravel 13 monolith with Livewire 4 for server-driven UI.
- Authentication-gated workspace with a public landing page at `/`.
- Not API-first.
- Not a multi-tenant SaaS product.
- Not split into microservices.

## Architecture

- Monolith structure:
  - `app/Livewire`: page-level UI and orchestration.
  - `app/Services`: business mutations and cross-cutting workflows.
  - `app/Models`: Eloquent domain models.
  - `routes/web.php`: web routes and small auth handlers.
  - `resources/views`: Blade templates and Livewire views.
  - `database/migrations`: schema and framework tables.

- Livewire role:
  - Livewire components handle rendering, validation, UI state, and browser interaction.
  - Full-page components are used for authenticated pages such as dashboard, issue workspace, email verification, and registration.

- Service layer:
  - `IssueService`: synchronous issue create/update/delete behavior.
  - `CommentService`: synchronous comment create/update/delete behavior and dispatch of the single async side effect.
  - `EmailVerificationService`: pre-registration email verification workflow.

- No API layer:
  - No REST API.
  - No GraphQL.
  - No SPA client.

## Core Features

- Authentication:
  - login
  - logout
  - password reset
  - email verification before registration

- Issue tracking:
  - create, update, delete issues
  - assign ownership
  - status and priority tracking
  - workspace filtering and search

- Comments:
  - create, update, delete comments
  - issue discussion stays attached to work items

- Dashboard:
  - aggregate issue metrics
  - recent issue visibility

## Runtime Model

- Session-based authentication using Laravel's web guard.
- Server-rendered pages with Livewire updates, not client-side routing.
- No SPA hydration model.
- Database-backed queue configured for background work.
- Queue usage is intentionally minimal.

## Async Policy

- Core mutations stay synchronous:
  - issue creation
  - issue updates
  - issue deletion
  - comment creation
  - comment updates and deletion

- Async is limited to non-critical side effects.
- Current async job:
  - `CommentCreatedJob`

- Current async behavior:
  - dispatched after comment creation
  - logs the comment creation event
  - does not contain business-critical logic

## Session & UX Model

- Redirect intent is preserved with Laravel's intended redirect flow.
- Protected-page access while unauthenticated redirects to `/login`, then back to the requested page after login.
- Session-expiration recovery is navigation-only:
  - the user re-authenticates
  - the destination page reloads fresh
  - no form restoration
  - no Livewire state restoration

- UX signal:
  - login page shows a one-time session-expired flash message when auth middleware sends the user back to login

## Frontend

- Blade for static pages and shared layout fragments.
- Livewire for interactive application pages.
- Tailwind CSS for utility styling.
- Token-based theming with three modes:
  - light
  - dark
  - dim

## Design System

- Styling is controlled by CSS variables in `resources/css/app.css`.
- Surface hierarchy:
  - `surface-0`: page background
  - `surface-1`: base container
  - `surface-2`: elevated card
  - `surface-3`: emphasis block

- Theme behavior:
  - themes differ by tone, contrast, and depth
  - layout does not change per theme
  - tokens control surfaces, text, borders, elevation, and accent usage

## Local Development

- Install dependencies:

```bash
composer install
npm install
```

- Initialize the app:

```bash
php artisan key:generate
php artisan migrate
```

- Start frontend development build:

```bash
npm run dev
```

- If you also want the Laravel app running locally:

```bash
php artisan serve
```

## Build & Test

```bash
npm run build
./vendor/bin/pint
php artisan test
```

## Security Notes

- `.env` is expected to remain local and must not be committed.
- Credentials in development or staging must not be reused in production.
- Authentication is session-based; there is no token API surface.
- Registration is gated behind email verification.
- Password reset uses Laravel's native password broker.
- Session recovery does not restore unsaved user input by design.

## Deployment Notes

- No Docker setup is included.
- No CI pipeline is included in this repository.
- No infrastructure-as-code is included.
- Deployment is expected to be handled manually or by external infrastructure outside this repo.
- Required runtime concerns:
  - web server / PHP process manager
  - writable Laravel storage paths
  - configured database
  - configured SMTP
  - queue worker for database-backed jobs

## Project Status

- Production-ready within a deliberately small scope.
- Stable authentication and recovery UX.
- Stable Livewire monolith with minimal async behavior.
- Feature scope is intentionally narrow: internal issue tracking, not a generalized work management platform.
