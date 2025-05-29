<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    if (!empty($name) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $file = 'messages.txt';
        $current_messages = file_exists($file) ? file_get_contents($file) : '';
        $new_message = "--------------------\n";
        $new_message .= "Nama: " . $name . "\n";
        $new_message .= "Email: " . $email . "\n";
        $new_message .= "Pesan: " . $message . "\n";
        $new_message .= "Tanggal: " . date("Y-m-d H:i:s") . "\n";
        $new_message .= "--------------------\n\n";

        file_put_contents($file, $new_message, FILE_APPEND);

        header("Location: index.php?status=success_contact#contact-us");
        exit();
    } else {

        header("Location: index.php?status=error_contact#contact-us");
        exit();
    }
} else {
    
    header("Location: index.php");
    exit();
}
?>