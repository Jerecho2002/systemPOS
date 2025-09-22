<?php
// Database credentials
$host = "localhost";
$username = "root"; // default XAMPP username
$password = "";     // default XAMPP has no password
$database = "computer_store";

// File name
$backupFile = 'backup_' . $database . '_' . date("Y-m-d_H-i-s") . '.sql';

// Path to mysqldump (adjust this based on your XAMPP install)
$mysqldump = 'C:\xampp\mysql\bin\mysqldump.exe';

// Full command
$command = "\"$mysqldump\" --user=$username --password=$password $database > \"$backupFile\"";

// Execute command
system($command, $result);

if ($result === 0) {
    echo "✅ Backup successful! File saved as: $backupFile";
} else {
    echo "❌ Backup failed. Please check your settings.";
}
?>
