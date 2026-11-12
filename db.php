<?php
// Postgres connection helper. Reads DATABASE_URL (Render provides it).
// Returns null when DATABASE_URL is not set, so the app still runs locally without a DB.

function db(): ?PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $url = getenv("DATABASE_URL");
    if (!$url) return null;

    $p = parse_url($url);
    $dsn = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s;sslmode=prefer",
        $p["host"], $p["port"] ?? 5432, ltrim($p["path"], "/")
    );

    $pdo = new PDO($dsn, urldecode($p["user"]), urldecode($p["pass"] ?? ""), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registrations (
            id SERIAL PRIMARY KEY,
            fullname TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL,
            dob DATE NOT NULL,
            gender TEXT NOT NULL,
            course TEXT NOT NULL,
            interests TEXT NOT NULL DEFAULT '',
            address TEXT NOT NULL,
            created_at TIMESTAMPTZ NOT NULL DEFAULT now()
        )
    ");
    return $pdo;
}
