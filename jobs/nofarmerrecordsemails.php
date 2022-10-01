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

        $no_farm_records_text = "You are yet to add any farm records for your farms. Please add records so you can view your farm performance, get insights, and apply for finance.";
        $no_farm_records_cta_text = "Login to add farm records";

        if ($emailtype == InnerEmailing::NO_FARM_RECORDS) { // we should be checking if the string we want to replace exists
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
            "type" => "NO_FARM_RECORDS",
            "error_message" => "",
            "farmers" => array()
        );

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
            file_put_contents('php://stderr', print_r('db Connection successful in job execution' . "\n", TRUE));
        } catch (PDOException $e) {
            file_put_contents('php://stderr', print_r('db Connection Error in job execution:' . $e->getMessage() . "\n", TRUE));
        } catch (Throwable $th) {
            file_put_contents('php://stderr', print_r('Another db Connection Error in job execution:' . $th->getMessage() . "\n", TRUE));
            throw $th;
        }



        $no_records_query = 'SELECT f.id AS "farmerid", f.firstname, f.lastname, f.email
            , COALESCE(records_mortalities.input_records_mortalities_count, 0) AS records_mortalities_count
            , COALESCE(records_medicines.input_records_medicines_count, 0) AS records_medicines_count
            , COALESCE(records_labour.input_records_labour_count, 0) AS records_labour_count
            , COALESCE(records_income_expenses.input_records_income_expenses_count, 0) AS records_income_expenses_count
            , COALESCE(records_diseases.input_records_diseases_count, 0) AS records_diseases_count
            
            , COALESCE(records_brooding.input_records_brooding_count, 0) AS records_brooding_count
            , COALESCE(records_feeds.inputs_records_feeds_count, 0) AS records_feeds_count
            , COALESCE(records_chicken.inputs_records_chicken_count, 0) AS records_chicken_count
            
            FROM farmers f
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "input_records_mortalities_count"
            FROM input_records_mortalities
            GROUP BY input_records_mortalities.farmerid
            
            
            ) records_mortalities
            ON records_mortalities.farmerid = f.id
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "input_records_medicines_count"
            FROM
            input_records_medicines
            GROUP BY input_records_medicines.farmerid
            
            ) records_medicines
            ON records_medicines.farmerid = f.id
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "input_records_labour_count"
            FROM
            input_records_labour
            GROUP BY input_records_labour.farmerid
            
            ) records_labour
            ON records_labour.farmerid = f.id
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "input_records_income_expenses_count"
            FROM
            input_records_income_expenses
            GROUP BY input_records_income_expenses.farmerid
            
            ) records_income_expenses
            ON records_income_expenses.farmerid = f.id
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "input_records_diseases_count"
            FROM
            input_records_diseases
            GROUP BY input_records_diseases.farmerid
            
            ) records_diseases
            ON records_diseases.farmerid = f.id
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "input_records_brooding_count"
            FROM
            input_records_brooding
            GROUP BY input_records_brooding.farmerid
            
            ) records_brooding
            ON records_brooding.farmerid = f.id
            
            LEFT JOIN 
            (SELECT farmerid, COUNT(*) AS "inputs_records_feeds_count"
            FROM
            inputs_records_feeds
            GROUP BY inputs_records_feeds.farmerid
            
            ) records_feeds
            ON records_feeds.farmerid = f.id
            
            LEFT JOIN 
            (
                SELECT farmerid, COUNT(*) AS "inputs_records_chicken_count"
                FROM
                inputs_records_chicken
                GROUP BY inputs_records_chicken.farmerid
            
            ) records_chicken
            ON records_chicken.farmerid = f.id
            
            
            WHERE DATEDIFF(CURRENT_TIMESTAMP(), f.`timejoined`) > 7
            
            AND f.id NOT IN (
            
                SELECT farmers.id 
                FROM `farmers` 
                LEFT JOIN email_reminders
                ON farmers.id = email_reminders.farmerid
                
                WHERE 

                DATEDIFF(CURRENT_TIMESTAMP(), `farmers`.`timejoined`) > 7
                
                AND 

                (

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
                    
                )
                
                
                AND email_reminders.farmerid IS NULL
                
                -- WHERE "_timejoined" > 200 -- DATEDIFF(CURRENT_TIMESTAMP(), `farmers`.`timejoined`)
                -- put % of completion
                -- include fields they are yet to fill out
            
            )
            
            AND (
                records_mortalities.input_records_mortalities_count IS NULL
                AND records_medicines.input_records_medicines_count IS NULL
                AND records_labour.input_records_labour_count IS NULL
                AND records_income_expenses.input_records_income_expenses_count IS NULL 
                AND records_diseases.input_records_diseases_count IS NULL
                AND records_brooding.input_records_brooding_count IS NULL
                AND records_feeds.inputs_records_feeds_count IS NULL
                AND records_chicken.inputs_records_chicken_count IS NULL
            )

            AND DATEDIFF(CURRENT_TIMESTAMP(), f.`timejoined`) > 7

            AND f.id NOT IN (
                SELECT email_reminders.farmerid FROM email_reminders WHERE email_reminders.email_type = "NO_FARM_RECORDS"
            )
        ';

        // Prepare statement
        $query_statement = $database_connection->prepare($no_records_query);

        // Execute query statement
        $query_statement->execute();

        $farmers_with_no_records = $query_statement->fetchAll(PDO::FETCH_ASSOC);

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
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

        // $mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo(getenv("OUR_EMAIL"), 'GrowAgric Inc');
        // $mail->addCC('cc@example.com');
        $mail->addBCC(getenv("GROW_AGRIC_DEV_EMAIL"));
      
        //Recipients
        $mail->setFrom(getenv("OUR_EMAIL"), 'Mailer');
        //Content
        $mail->isHTML(true); //Set email format to HTML
        // -- how do we know what subject to set, and set it dynamically??
        $mail->Subject = 'Add Farm Records';
    
        for ($i = 0; $i < count($farmers_with_no_records); $i++) {
            # send mail
            
            try {
                $mail->addAddress($farmers_with_no_records[$i]['email'], $farmers_with_no_records[$i]['firstname']);     //Add a recipient [in test mode, send to getenv("TEST_EMAIL")]
            } catch (\Throwable $th) {
                file_put_contents('php://stderr', print_r('Invalid address skipped (in nofarmerrecoredsemails.php): ' . $farmers_with_incomplete_profiles[$i]['email'] . "\n", TRUE));
                continue;
            }
            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

         
            $mail->Body = _getEmailTemplateHTML($farmers_with_no_records[$i]['firstname'], InnerEmailing::NO_FARM_RECORDS); // 'This is the HTML message body <b>in bold!</b>';
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            // not put farmer emails
            file_put_contents('php://stderr', print_r('sending email for ' . $farmers_with_no_records[$i]['firstname'] . " with email " . $farmers_with_no_records[$i]['email'] . "\n", TRUE));

            try {
                if (getenv("CURR_ENV") == "production" && $mail->send()) { // 
                    // save into the db that it has been sent
                    _saveFarmerEmailReminder($farmers_with_no_records[$i]['farmerid'], "NO_FARM_RECORDS");
    
                    $output_info["farmers"][$farmers_with_no_records[$i]['email']] = "SENT";
                    file_put_contents('php://stderr', print_r('SEnt THe MaiL ' . "\n", TRUE));
                } else {
                    $output_info["farmers"][$farmers_with_no_records[$i]['email']] = "NOT_SENT";
                    file_put_contents('php://stderr', print_r('did not SEnd THe MaiL ' . "\n", TRUE));
                }
            } catch (\Throwable $th) {
                //Reset the connection to abort sending this message
                //The loop will continue trying to send to the rest of the list
                file_put_contents('php://stderr', print_r('Mailer Error (' . htmlspecialchars($farmers_with_no_records[$i]['email']) . ') ' . $mail->ErrorInfo . "\n", TRUE));
                $mail->getSMTPInstance()->reset();
            }

            file_put_contents('php://stderr', print_r("\n\n\n", TRUE));

            //Clear all addresses and attachments for the next iteration
            $mail->clearAddresses();
            $mail->clearAttachments();

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
