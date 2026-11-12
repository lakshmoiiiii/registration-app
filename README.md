# Online Registration Form

HTML + CSS + JavaScript + jQuery (AJAX) + PHP + Postgres. The form validates in the browser (jQuery) and again on the server (PHP), saves to Postgres, and shows a styled, printable confirmation.

## Files
| File | Purpose |
|---|---|
| index.html | The form |
| style.css | Styling (also print styles) |
| script.js | jQuery validation, AJAX call, renders the result card |
| submit.php | Server-side validation, saves to the database, returns JSON |
| db.php | Postgres connection; creates the table automatically |
| admin.php | Password-protected list of all registrations |
| Dockerfile | Lets Render run PHP + the Postgres driver |

## Run locally
Needs PHP 8+. Without a database the form still works but nothing is saved:

    php -S localhost:8000

To save locally, set DATABASE_URL first, e.g.

    export DATABASE_URL="postgres://user:password@localhost:5432/regdb"
    export ADMIN_PASSWORD="choose-a-password"
    php -S localhost:8000

## Deploy: GitHub + Render

### 1. Push to GitHub
    git init
    git add .
    git commit -m "Registration form"
    git branch -M main
    git remote add origin https://github.com/<your-username>/registration-app.git
    git push -u origin main

(Create the empty repo first on github.com > New repository. Do not tick "Add README".)

### 2. Create the Postgres database on Render
1. Render dashboard > New > PostgreSQL.
2. Name: registration-db, Instance type: Free, Create Database.
3. When it is ready, open it and copy the **Internal Database URL**.

### 3. Create the web service
1. New > Web Service > pick your registration-app repo.
2. Runtime: **Docker**, Branch: main, Instance type: Free.
3. Under Environment Variables add:
   - DATABASE_URL = the Internal Database URL you copied
   - ADMIN_PASSWORD = a password of your choice
4. Create Web Service. The first build takes 2-5 minutes.
5. Visit https://<your-service>.onrender.com for the form, and /admin.php (username: admin) to see saved registrations.

### 4. Updating later
Edit the code, then `git add . && git commit -m "update" && git push`. Render redeploys automatically.

### Notes
- The table is created automatically on the first submission.
- Free web instances sleep after ~15 min idle; the first visit afterwards takes 30-60 s.
- Render's free Postgres database expires after 30 days; upgrade or recreate it if you need it longer.
- If Render reports "no open port detected", add the environment variable PORT=80.
- Keep the Internal URL for the web service (same region). Use the External URL only for connecting from your own computer.
