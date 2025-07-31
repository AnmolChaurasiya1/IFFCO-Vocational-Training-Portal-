<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login first.";
    exit;
}

$user_id = $_SESSION['user_id'];
$lat = isset($_POST['latitude']) ? $_POST['latitude'] : null;
$lon = isset($_POST['longitude']) ? $_POST['longitude'] : null;

$fixed_lat = 25.55;
$fixed_lon = 82.1;
$allowed_distance = 0.1; // km
$allowed_start = strtotime("10:30:00");
$allowed_end = strtotime("11:59:00");
$current_time = time();

function getDistance($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    return $dist * 60 * 1.1515 * 1.609344; // in km
}

$distance = getDistance($lat, $lon, $fixed_lat, $fixed_lon);

if ($distance > $allowed_distance) {
    $status = 'invalid location';
    echo "❌ Attendance failed: You are not at the authorized location.";
} elseif ($current_time < $allowed_start || $current_time > $allowed_end) {
    $status = 'late';
    echo "❌ Attendance failed: You are not within the allowed time.";
} else {
    $status = 'present';
    echo "✅ Attendance marked successfully!";
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO attendance (user_id, latitude, longitude, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("idds", $user_id, $lat, $lon, $status);
$stmt->execute();
$stmt->close();
$conn->close();
?>
