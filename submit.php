<?php
// Server-side validation + (optional) Postgres storage + JSON response.
// Values are returned raw; the browser escapes them when rendering.
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set("Asia/Kolkata");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "errors" => ["form" => "Invalid request method."]]);
    exit;
}

function clean($v) { return trim((string)$v); }

$fullname  = clean($_POST["fullname"] ?? "");
$email     = clean($_POST["email"] ?? "");
$phone     = clean($_POST["phone"] ?? "");
$dob       = clean($_POST["dob"] ?? "");
$gender    = clean($_POST["gender"] ?? "");
$course    = clean($_POST["course"] ?? "");
$address   = clean($_POST["address"] ?? "");
$allowed   = ["Web Development", "AI / ML", "Embedded Systems", "Cybersecurity"];
$interests = array_values(array_intersect((array)($_POST["interests"] ?? []), $allowed));

$errors = [];
if (mb_strlen($fullname) < 3)                       $errors["fullname"] = "Enter your full name.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors["email"]    = "Enter a valid email.";
if (!preg_match('/^[6-9][0-9]{9}$/', $phone))       $errors["phone"]    = "Enter a valid 10-digit mobile number.";
$d = DateTime::createFromFormat("Y-m-d", $dob);
if (!$d || $d->format("Y-m-d") !== $dob)            $errors["dob"]      = "Enter a valid date of birth.";
if (!in_array($gender, ["Female", "Male", "Other"], true)) $errors["gender"] = "Select a gender.";
if ($course === "")                                 $errors["course"]   = "Select a course.";
if (mb_strlen($address) < 10)                       $errors["address"]  = "Enter a complete address.";

if ($errors) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

$id = null;
try {
    $pdo = db();
    if ($pdo) {
        $stmt = $pdo->prepare(
            "INSERT INTO registrations (fullname, email, phone, dob, gender, course, interests, address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([$fullname, strtolower($email), $phone, $dob, $gender, $course, implode(", ", $interests), $address]);
        $id = (int)$stmt->fetchColumn();
    }
} catch (PDOException $e) {
    if ($e->getCode() === "23505") { // unique violation on email
        echo json_encode(["success" => false, "errors" => ["email" => "This email is already registered."]]);
    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "errors" => ["form" => "Server error. Please try again."]]);
    }
    exit;
}

$reg_id = "REG" . date("ymd") . ($id !== null ? str_pad((string)$id, 4, "0", STR_PAD_LEFT) : rand(1000, 9999));

echo json_encode([
    "success" => true,
    "data" => [
        "reg_id"       => $reg_id,
        "fullname"     => $fullname,
        "email"        => $email,
        "phone"        => $phone,
        "dob"          => $d->format("d M Y"),
        "gender"       => $gender,
        "course"       => $course,
        "interests"    => $interests,
        "address"      => $address,
        "submitted_at" => date("d M Y, h:i A"),
    ],
]);
