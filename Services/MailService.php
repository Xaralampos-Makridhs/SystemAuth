<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

     class MailService{
         public function send($to,$subject,$htmlBody){
             $mail=new PHPMailer(true);

             try{
                 $mail->isSMTP();

                 $mail->Host=$_ENV['MAIL_HOST'];
                 $mail->SMTPAuth=true;
                 $mail->Username=$_ENV['MAIL_USERNAME'];
                 $mail->Password=$_ENV['MAIL_PASSWORD'];
                 $mail->SMTPSecure=$_ENV['MAIL_ENCRYPTION'];
                 $mail->Port=(int) $_ENV['MAIL_PORT'];

                 $mail->setFrom($_ENV['MAIL_FROM'],$_ENV['MAIL_FROM_NAME']);
                 $mail->addAddress($to);

                 $mail->isHTML(true);
                 $mail->Subject=$subject;
                 $mail->Body=$htmlBody;
                 $mail->AltBody=strip_tags($htmlBody);

                 return $mail->send();
             }catch (Exception $e){
                 error_log('Mail log:').$e->getMessage();
                 return false;
             }
         }

     }

