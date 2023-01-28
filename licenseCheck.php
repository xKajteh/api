<?php
include 'config.php';

date_default_timezone_set('Europe/Warsaw');

$conn = mysqli_connect($config['database_host'], $config['database_user'], $config['database_password'], $config['database_name']) or die("Brak połączenia z bazą danych");

if (!isset($_GET['key'])) {
    http_response_code(400);
    echo json_encode(array("error" => "Nie podano klucza licencji"));
    exit();
}

$licenseKey = $_GET['key'];
$current_date = date('Y-m-d H:i');

$query = "SELECT * FROM license WHERE licenseKey = '$licenseKey'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(array("valid" => false, "error" => "Nieprawidłowy klucz licencji"));
    exit();
}

$row = mysqli_fetch_assoc($result);

if ($current_date > $row['expiration_date']) {
    echo json_encode(array("valid" => false, "error" => "Licencja wygasła"));
    $query = "DELETE FROM license WHERE licenseKey = '$licenseKey'";
    mysqli_query($conn, $query);
    exit();
}

echo json_encode(
    array(
        "valid" => true,
        "key" => $licenseKey,
        "expiration_date" => $row['expiration_date'],
        "email" => $row['email'],
    )
);

mysqli_close($conn);
?>