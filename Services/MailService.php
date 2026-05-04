<?php

    // Import the PHPMailer class
    use PHPMailer\PHPMailer\PHPMailer;

    // Import the PHPMailer Exception class
    use PHPMailer\PHPMailer\Exception;

    // Define the MailService class
    class MailService{
    // Send an email with a recipient, subject, and HTML body
    public function send($to, $subject, $htmlBody){
        // Create a new PHPMailer instance and enable exceptions
        $mail = new PHPMailer(true);
        // Start error handling block
        try {
            // Configure PHPMailer to use SMTP
            $mail->isSMTP();
            // Set the SMTP server host from environment variables
            $mail->Host = $_ENV['MAIL_HOST'];
            // Enable SMTP authentication
            $mail->SMTPAuth = true;
            // Set the SMTP username from environment variables
            $mail->Username = $_ENV['MAIL_USERNAME'];
            // Set the SMTP password from environment variables
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            // Set the SMTP encryption type from environment variables
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
            // Set the SMTP port from environment variables and convert it to integer
            $mail->Port = (int) $_ENV['MAIL_PORT'];
            // Set the email character encoding to UTF-8
            $mail->CharSet = 'UTF-8';
            // Set the sender email address and sender name
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            // Add the recipient email address
            $mail->addAddress($to);
            // Set the email format to HTML
            $mail->isHTML(true);
            // Set the email subject
            $mail->Subject = $subject;
            // Set the HTML email body
            $mail->Body = $htmlBody;
            // Set the plain text version of the email by removing HTML tags
            $mail->AltBody = strip_tags($htmlBody);
            // Send the email and return true if successful
            return $mail->send();
        } catch (Exception $e) {
            // Log the email sending error
            error_log('Mail Error: ' . $mail->ErrorInfo);
            // Return false if email sending fails
            return false;
        }
    }
}