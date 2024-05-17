<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/PHPMailerAutoload.php'; // Ensure this path is correct or use Composer's autoload

// Configuration
$sendToEmail = 'contact@throughequity.com';
$sendToName = 'Through Equity';
$subject = 'Contact Form Detail';
$fields = array('name' => 'Name', 'surname' => 'Surname', 'phone' => 'Phone', 'email' => 'Email', 'message' => 'Message', 'department' => 'Department');
$okMessage = 'We have received your inquiry. Stay tuned, we’ll get back to you very soon.';
$errorMessage = 'There was an error while submitting the form. Please try again later';

error_reporting(E_ALL & ~E_NOTICE);

try {
    if(count($_POST) == 0) throw new Exception('Form is empty');
    
    $emailTextHtml = "<table>";
    
    $fromEmail = '';
    $fromName = '';

    foreach ($_POST as $key => $value) {
        $value = htmlspecialchars($value);
        if (isset($fields[$key])) {
            $emailTextHtml .= "<tr><th>{$fields[$key]}</th><td>$value</td></tr>";
        }

        if ($key == 'email') {
            $fromEmail = $value;
        }
        if ($key == 'name') {
            $fromName = $value;
        }
    }
    $emailTextHtml .= "</table>";

    if (empty($fromEmail) || empty($fromName)) {
        throw new Exception('Sender email or name is missing.');
    }
    
    $mail = new PHPMailer(true);
    
    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($sendToEmail, $sendToName);
    $mail->addReplyTo($fromEmail);
    
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = $subject;
    $mail->msgHTML($emailTextHtml);
    
    if(!$mail->send()) {
        throw new Exception('I could not send the email.' . $mail->ErrorInfo);
    }
    
    $responseArray = array('type' => 'success', 'message' => $okMessage);
} catch (Exception $e) {
    $responseArray = array('type' => 'danger', 'message' => $e->getMessage());
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);
    header('Content-Type: application/json');
    echo $encoded;
} else {
    echo $responseArray['message'];
}
?>
