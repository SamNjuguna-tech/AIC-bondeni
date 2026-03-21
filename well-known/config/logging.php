<?php
// Set custom error log file
$logFile = __DIR__ . '/../logs/app.log';
$logDir = dirname($logFile);

try {
    // Ensure logs directory exists with proper permissions
    if (!file_exists($logDir)) {
        if (!mkdir($logDir, 0777, true)) {
            // If we can't create the logs directory, fall back to system temp directory
            $logFile = sys_get_temp_dir() . '/app.log';
        }
    }

    // Try to make the log file writable if it exists
    if (file_exists($logFile) && !is_writable($logFile)) {
        chmod($logFile, 0666);
    }

    // Configure error logging
    ini_set('log_errors', 1);
    ini_set('error_log', $logFile);
    error_reporting(E_ALL);

    // Function to log with timestamp
    function app_log($message, $level = 'INFO') {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp] [$level] $message";
            error_log($logMessage);
        } catch (Exception $e) {
            // If logging fails, write to PHP's default error log
            error_log("Logging failed: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    // If anything fails during setup, log to PHP's default error log
    error_log("Logging setup failed: " . $e->getMessage());
}
?>
