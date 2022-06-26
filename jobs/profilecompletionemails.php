<?
// Headers
// https://stackoverflow.com/a/17098221
include_once '../config/globals/header.php';

require __DIR__ . "/../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();
class InnerEmailing
{
    const INCOMPLETE_PROFILE = "INCOMPLETE_PROFILE";
    const NO_FARM_RECORDS = "NO_FARM_RECORDS";
}

// if they're both (incomplete profile, and no farm records, send a different, message? ... no.)
function _getEmailTemplateHTML($full_or_first_name, $emailtype, $cta_link = "https://farmers.growagric.com")
{
    try {
        $email_template = file_get_contents(__DIR__ . "/../assets/email.template.html");

        $incomplete_profile_text = "It has been over a week since you signed up on GrowAgric and are yet to complete your profile. Please complete your profile so you can access financing and learning materials for your farms, record keeping and view your farm performance overtime.";

        $no_farm_records_text = "You are yet to add any farm records for your farms. Please add records so you can view your farm performance, get insights, and apply for finance.";

        $incomplete_profile_cta_text = "Login to complete your profile";
        $no_farm_records_cta_text = "Login to add farm records";

        if ($emailtype == InnerEmailing::INCOMPLETE_PROFILE) { // we should be checking if the string we want to replace exists
            $emailbody = str_replace("{body}", $incomplete_profile_text, $email_template);
            $emailbody = str_replace("{fullname}", $full_or_first_name, $emailbody);

            $emailbody = str_replace("{cta}", $incomplete_profile_cta_text, $emailbody);
            
            $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
        } else if ($emailtype == InnerEmailing::NO_FARM_RECORDS) { // we should be checking if the string we want to replace exists
            $emailbody = str_replace("{body}", $no_farm_records_text, $email_template);
            $emailbody = str_replace("{fullname}", $full_or_first_name, $emailbody);

            $emailbody = str_replace("{cta}", $no_farm_records_cta_text, $emailbody);
            
            $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
        }

        return $emailbody;
    } catch (\Throwable $err) {
        file_put_contents('php://stderr', print_r("\n\n" . 'composing email body error::::::' . "\n", TRUE));
        file_put_contents('php://stderr', print_r($err, TRUE));
        return ""; //false;
    }

    
}

try {

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Database parameters
        $database_host;
        $database_port; // initialize for local
        $database_name;
        $database_username;
        $database_password;
        $database_connection;

        // must be public [& not __construct, cause it'll return a Database data type not PDO], else we can't call it elsewhere

        # should this block be in a constructor method or sth?
        if (getenv("CURR_ENV") == "production") {
            file_put_contents('php://stderr', print_r('Using prod database connection' . "\n", TRUE));
            $database_host = getenv("GROW_AGRIC_HOST_NAME");
            $database_name = getenv("GROW_AGRIC_DATABASE_NAME_PROD"); // eventually dynamically set to prod/test
            $database_username = getenv("GROW_AGRIC_DATABASE_USER_NAME");
            $database_password = getenv("GROW_AGRIC_DATABASE_PASSWORD");
            $database_port = '3306'; // re-assign if in prod
        } else {
            file_put_contents('php://stderr', print_r('Using local database connection' . "\n", TRUE));
            $database_host = getenv("GROW_AGRIC_HOST_NAME_LOCAL"); // getenv("GROW_AGRIC_HOST_NAME");
            $database_name = getenv("GROW_AGRIC_DATABASE_NAME_TEST"); // eventually dynamically set to prod/test
            $database_username = getenv("GROW_AGRIC_DATABASE_USER_NAME_TEST");
            $database_password = getenv("GROW_AGRIC_DATABASE_PASSWORD_TEST");
            $database_port = '8889';
        }


        $database_connection = null;
        try {
            $database_connection = new PDO(
                'mysql:host=' . $database_host . ';dbname=' . $database_name . ';port=' . $database_port,
                $database_username,
                $database_password
            );

            $database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            file_put_contents('php://stderr', print_r('Connection successful' . "\n", TRUE));
        } catch (PDOException $e) {
            file_put_contents('php://stderr', print_r('Connection Error:' . $e->getMessage() . "\n", TRUE));
        } catch (Throwable $th) {
            file_put_contents('php://stderr', print_r('Another Connection Error:' . $th->getMessage() . "\n", TRUE));
            throw $th;
        }








        $query = '
            SELECT farmers.id AS "farmerid", DATEDIFF(CURRENT_TIMESTAMP(), `farmers`.`timejoined`) AS "_timejoined"
            ,`farmers`.`timejoined`
            , `farmers`.`email`, `farmers`.`firstname`, `farmers`.`lastname` 
            FROM `farmers` 
            LEFT JOIN profile_completion_email_reminders
            ON farmers.id = profile_completion_email_reminders.farmerid
            
            WHERE 
            
            (`farmers`.`firstname` IS NULL OR `farmers`.`firstname` = "" OR `farmers`.`firstname` = " ")
            OR
            (`farmers`.`lastname` IS NULL OR `farmers`.`lastname` = "" OR `farmers`.`lastname` = " ")
            OR
            (`farmers`.`phonenumber` IS NULL OR `farmers`.`phonenumber` = "" OR `farmers`.`phonenumber` = " ")
            OR
            (`farmers`.`age` IS NULL OR `farmers`.`age` = "" OR `farmers`.`age` = " ")
            OR
            (`farmers`.`maritalstatus` IS NULL OR `farmers`.`maritalstatus` = "" OR `farmers`.`maritalstatus` = " ")
            OR
            (`farmers`.`yearsofexperience` IS NULL OR `farmers`.`yearsofexperience` = "" OR `farmers`.`yearsofexperience` = " ")
            OR
            (`farmers`.`highesteducationallevel` IS NULL OR `farmers`.`highesteducationallevel` = "" OR `farmers`.`highesteducationallevel` = " ")
            
            
            
            AND DATEDIFF(CURRENT_TIMESTAMP(), `farmers`.`timejoined`) > 7

            AND farmers.id NOT IN (
                SELECT profile_completion_email_reminders.farmerid FROM profile_completion_email_reminders
                )
            -- WHERE "_timejoined" > 200
            -- put % of completion
            -- include fields they are yet to fill out
            
            
        ';

        // Prepare statement
        $query_statement = $database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        $farmers_with_incomplete_profiles = $query_statement->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($farmers_with_incomplete_profiles); $i++) {
            # send mail
            //Server settings
            // uncomment to see email report/output
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable or disable verbose debug output
            $mail->Debugoutput = function ($str, $level) {
                file_put_contents('php://stderr', print_r("\n\n" . $str . "\n", TRUE));
            };
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = getenv("OUR_EMAIL_REGION");   //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = getenv("OUR_EMAIL");                     //SMTP username
            $mail->Password   = getenv("OUR_EMAIL_PASSWORD");            //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = getenv("OUR_EMAIL_PORT");               //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(getenv("OUR_EMAIL"), 'Mailer');
            $mail->addAddress($farmers_with_incomplete_profiles[$i]['email'], $farmers_with_incomplete_profiles[$i]['firstname']);     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            $mail->addReplyTo(getenv("OUR_EMAIL"), 'GrowAgric Inc');
            // $mail->addCC('cc@example.com');
            $mail->addBCC(getenv("GROW_AGRIC_DEV_EMAIL"));

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Complete Your Profile';
            $mail->Body = _getEmailTemplateHTML($farmers_with_incomplete_profiles[$i]['firstname'], InnerEmailing::INCOMPLETE_PROFILE, $cta_link); // 'This is the HTML message body <b>in bold!</b>';
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            echo $farmers_with_incomplete_profiles[$i]['firstname'] ;
            // if ($mail->send()) {
            //     file_put_contents('php://stderr', print_r('SEnt THe MaiL ' . "\n", TRUE));
            // } else {
            //     file_put_contents('php://stderr', print_r('did not SEnd THe MaiL ' . "\n", TRUE));
            // }
        }

        echo 'done';

    } else {
        echo 'wrong http method';
    }
} catch (\Throwable $err) {
    echo 'an error occuried';
    echo "the error that occured: $err";
}
