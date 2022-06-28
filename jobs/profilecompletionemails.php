<?
// Headers
header('Content-Type: application/json; charset=utf-8');
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


function _getDb()
{
    // Database parameters
    $database_host = NULL;
    $database_port = NULL; // initialize for local
    $database_name = NULL;
    $database_username = NULL;
    $database_password = NULL;
    $database_connection = NULL;

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


    try {
        $database_connection = new PDO(
            'mysql:host=' . $database_host . ';dbname=' . $database_name . ';port=' . $database_port,
            $database_username,
            $database_password
        );

        $database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        file_put_contents('php://stderr', print_r('db Connection successful in job execution' . "\n", TRUE));
    } catch (PDOException $e) {
        file_put_contents('php://stderr', print_r('db Connection Error in job execution:' . $e->getMessage() . "\n", TRUE));
    } catch (Throwable $th) {
        file_put_contents('php://stderr', print_r('Another db Connection Error in job execution:' . $th->getMessage() . "\n", TRUE));
        throw $th;
    }

    return $database_connection;

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

function _saveFarmerEmailReminder($farmerid, $emailtype)
{
    file_put_contents('php://stderr', print_r("\n\n" . 'saving that we sent a farmer a no farm records reminder' . "\n", TRUE));

    $query = 'INSERT INTO `email_reminders`
        SET
        email_type = :_email_type,
        farmerid = :_farmerid
    ';

    $database_connection = _getDb();

    $stmt = $database_connection->prepare($query);

    // Ensure safe data
    $fid = htmlspecialchars(strip_tags($farmerid));
    $et = htmlspecialchars(strip_tags($emailtype));

    // Bind parameters to prepared stmt
    $stmt->bindParam(':_email_type', $et);
    $stmt->bindParam(':_farmerid', $fid);

    $r = $stmt->execute();

    if ($r) {
        return $database_connection->lastInsertId();
        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
    } else {
        return false;
    }
}


try {

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $output_info = array(
            "time" => date("Y/m/d h:i:sa e"),
            "errors" => false,
            "type" => "INCOMPLETE_PROFILE",
            "error_message" => "",
            "farmers" => array()
        );



        $incomplete_profile_query = '
            SELECT farmers.id AS "farmerid", DATEDIFF(CURRENT_TIMESTAMP(), `farmers`.`timejoined`) AS "_timejoined"
            ,`farmers`.`timejoined`
            , `farmers`.`email`, `farmers`.`firstname`, `farmers`.`lastname` 
            FROM `farmers` 
            LEFT JOIN email_reminders
            ON farmers.id = email_reminders.farmerid
            
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
                SELECT email_reminders.farmerid FROM email_reminders
                )
            -- WHERE "_timejoined" > 200
            -- put % of completion
            -- include fields they are yet to fill out
            
            
        ';

        $database_connection = _getDb();

        // Prepare statement
        $query_statement = $database_connection->prepare($incomplete_profile_query);

        // Execute query statement
        $query_statement->execute();

        $farmers_with_incomplete_profiles = $query_statement->fetchAll(PDO::FETCH_ASSOC);

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
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
            $mail->addAddress(getenv("TEST_EMAIL"), $farmers_with_incomplete_profiles[$i]['firstname']);     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            $mail->addReplyTo(getenv("OUR_EMAIL"), 'GrowAgric Inc');
            // $mail->addCC('cc@example.com');
            $mail->addBCC(getenv("GROW_AGRIC_DEV_EMAIL"));

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            // -- how do we know what subject to set, and set it dynamically??
            $mail->Subject = 'Complete Your Profile';
            $mail->Body = _getEmailTemplateHTML($farmers_with_incomplete_profiles[$i]['firstname'], InnerEmailing::INCOMPLETE_PROFILE); // 'This is the HTML message body <b>in bold!</b>';
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            // not put farmer emails
            file_put_contents('php://stderr', print_r('sending email for ' . $farmers_with_incomplete_profiles[$i]['firstname'] . " with email " . $farmers_with_incomplete_profiles[$i]['email'] . "\n", TRUE));

            if ($i < 1 && $mail->send()) { // send only one email for now
                // save into the db that it has been sent
                _saveFarmerEmailReminder($farmers_with_incomplete_profiles[$i]['farmerid'], "INCOMPLETE_PROFILE");

                $output_info["farmers"][$farmers_with_incomplete_profiles[$i]['email']] = "SENT";
                file_put_contents('php://stderr', print_r('SEnt THe MaiL ' . "\n", TRUE));
            } else {
                $output_info["farmers"][$farmers_with_incomplete_profiles[$i]['email']] = "NOT_SENT";
                file_put_contents('php://stderr', print_r('did not SEnd THe MaiL ' . "\n", TRUE));
            }

            file_put_contents('php://stderr', print_r("\n\n\n" . "\n\n\n", TRUE));

        }

        echo json_encode($output_info);

    } else {
        $output_info["errors"] = true;
        $output_info["error_message"] = 'wrong http method used';
        echo json_encode($output_info);
    }
} catch (\Throwable $err) {

    file_put_contents('php://stderr', print_r('Error while trying to run job: ' . $err->getMessage() . "\n", TRUE));

    // echo 'an error occuried';
    // echo "the error that occured: $err";

    $output_info["errors"] = true;
    $output_info["error_message"] = $err->getMessage();
    echo json_encode($output_info);
}


/**
 * run the profile,
 * update the db when we send email.
 * 
 * do same for farmers without records
 * and when getting for farmers without records (they mustn't be farmers without complete profiles -- even if they completed their profiles after a week)
 */
