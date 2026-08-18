# Openminds

Openminds is a community-driven self-learning platform where learners can create notes, ask and answer questions, practice exercises, and track their progress in one place.

## Core Features

- **Notes module**: create, edit, tag, pin, share, and browse notes
- **Topics & tags**: organize notes and discover related content
- **Q&A module**: ask questions, post answers, vote, and manage content
- **Exercises module**: create, review, publish, attempt, and score exercises
- **Dashboard & analytics**: points, activity summaries, and learning insights
- **Announcements & notifications**: in-app communication for users/admins
- **Role-aware flows**: student/mentor/expert/admin oriented capabilities

## Tech Stack

- **Backend**: PHP (custom MVC style architecture)
- **Database**: MySQL (schema at `/home/runner/work/Openminds/Openminds/schema/openminds.sql`)
- **Frontend**: PHP views + vanilla JavaScript modules under `public/assets/js`
- **Server routing**: Apache rewrite to `public/index.php` via `.htaccess`

## Project Structure

```text
app/
  controllers/   # Request handlers (notes, questions, exercises, profile, auth, etc.)
  models/        # Data access and domain models
  services/      # Business workflows (auth, profile, analysis, notifications...)
  views/         # UI templates (PHP view files)
  core/          # Framework primitives (router, controller base, db, config)
public/
  index.php      # Entry point
  assets/js/     # Frontend logic per module/view
schema/
  openminds.sql  # Database schema, views, function, seed-style data
```

## Architecture Notes

- Uses a custom `App` router with route registration through `App::get()` and `App::post()`.
- Supports:
  - explicit route patterns (including parameterized URIs like `{id}`)
  - fallback controller/method routing for backward compatibility
- `Controller` base class handles:
  - view rendering
  - route guards (`login_guard`, `admin_guard`, `expert_guard`)
  - JSON request/response helpers
- `Model` + `Database` traits provide reusable query and CRUD helpers via PDO.

## Database Highlights

The SQL schema includes modules and support tables for:

- users, roles, experts, otp/account flows
- notes, note tags, note shares, topics
- questions, answers, voting
- exercises, exercise questions/answers, attempts, exercise voting
- announcements, notifications, events, analytics summary views

It also defines SQL views and a `CalculateUserPoints` function used for contribution/points logic.

## Setup (Local Development)

1. **Clone the repository** and place it in your web server workspace.
2. **Create a MySQL database** (e.g., `openminds`).
3. **Import schema** from:
   - `/home/runner/work/Openminds/Openminds/schema/openminds.sql`
4. **Configure app settings** in:
   - `/home/runner/work/Openminds/Openminds/app/core/config.php`
   - set `DBNAME`, `DBHOST`, `DBUSER`, `DBPASS`, and `ROOT` for your environment
5. **Serve the `public/` directory** with Apache (mod_rewrite enabled).
6. Open the configured base URL and use register/login flows.

## Routing Reference (Examples)

- `notes/*` for notes and notes API endpoints
- `topics/*` for topic management and filters
- `question/*` for question and answer operations
- `exercises/*` for exercise UI and API flows
- `analysis/*` and `api/dashboard/*` for analytics/dashboard data

## Notes for Contributors

- Keep business logic in services/models and leave controllers thin where possible.
- Add new endpoints through `app/core/routes.php`.
- Follow existing module naming conventions across:
  - `app/controllers`
  - `app/models`
  - `public/assets/js/views`
