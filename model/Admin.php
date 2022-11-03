<?php

//Load Composer's autoloader
require __DIR__ . "/../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701
file_put_contents('php://stderr', "Hitting Admin.php" . "\n" . "\n", FILE_APPEND | LOCK_EX);

include_once 'Farmer.php';
include_once __DIR__ . '/../config/Database.php';

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include_once __DIR__ . '/../utilities/ICustom.php';

// Headers

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "../.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

class Admin
{
    // DB stuff
    public $database_connection;

    public $table = 'admins';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    public function generateRandomString()
    {
        $random_string = "0" . rand(1, 64) . rand(0, 94) . rand(0, 9) . rand(0, 49) . rand(0, 29) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . "-" . (new DateTime())->getTimestamp();
        return $random_string;
    }


    public function sendMessage($_message, $_time_sent, $_from, $_to, $farmerid, $subject)
    {
        try {
            file_put_contents('php://stderr', print_r("\n\n" . 'recording a message' . "\n", TRUE));

            $query = 'INSERT INTO `messages`
                SET
                _from = :_from,
                the_message = :_the_message,
                farmerid = :_farmerid,
                _to = :_to,
                subject = :_subject,
                time_sent = :_time_sent
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $m = htmlspecialchars(strip_tags($_message));
            $f = htmlspecialchars(strip_tags($_from));
            $t = htmlspecialchars(strip_tags($_to));
            $fi = htmlspecialchars(strip_tags($farmerid));
            $s = htmlspecialchars(strip_tags($subject));

            $date1 = new DateTime($_time_sent); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));


            // Bind parameters to prepared stmt
            $stmt->bindParam(':_from', $f);
            $stmt->bindParam(':_the_message', $m);
            $stmt->bindParam(':_to', $t);
            $stmt->bindParam(':_time_sent', $d);
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_subject', $s);

            $r = $stmt->execute();

            if ($r) {

                file_put_contents('php://stderr', print_r('created THe MessAGE ' . "\n", TRUE));
                $_result = $this->getSingleChatMessage($this->database_connection->lastInsertId());
                $_row = $_result->fetch(PDO::FETCH_ASSOC);

                return $_row;
            } else {
                file_put_contents('php://stderr', print_r('DID created THe MessAGE ' . "\n", TRUE));
                return false;
            }
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->sendMessage error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmerMessages($_farmeremail)
    {
        try {

            // we should be escaping farmerid

            $query = 'SELECT subject, messages.* FROM `messages` WHERE `_from` = :farmeremail OR `_to` = :farmeremail';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $fe = htmlspecialchars(strip_tags($_farmeremail));

            // Execute query statement
            $query_statement->bindParam(':farmeremail', $fe);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarmerMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllAdminMessages()
    {
        try {

            $query = 'SELECT * FROM `messages` -- RIGHT JOIN `farmers` ON `farmers`.`email` = `messages`.`_from`';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAllAdminMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmersWithMessagesV2()
    {
        try {
            $query = "SELECT farmers.id, farmers.email, farmers.firstname, farmers.lastname, messages.*
            FROM `messages` 
            LEFT JOIN farmers
            ON messages.farmerid = farmers.id
            
            WHERE 
            (
                farmers.email IN (SELECT DISTINCT messages._from FROM messages WHERE messages._from NOT LIKE '%@growagric.com')
                OR
                farmers.email IN (SELECT DISTINCT messages._to FROM messages WHERE messages._to NOT LIKE '%@growagric.com')
            )
            ORDER BY messages.time_sent ASC
            ";

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarmersWithMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmersWithMessages()
    {
        try {
            // $query = 'SELECT DISTINCT `_from` FROM `messages`';
            $query = "SELECT DISTINCT farmers.id, farmers.email, farmers.firstname, farmers.lastname 
            -- , messages._from, messages._to 
                        FROM `messages` ,farmers
                        WHERE farmers.email IN (SELECT DISTINCT messages._from FROM messages WHERE messages._from NOT LIKE '%@growagric.com')
                        OR
                        farmers.email IN (SELECT DISTINCT messages._to FROM messages WHERE messages._to NOT LIKE '%@growagric.com')
            ";

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarmersWithMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmersWithoutMessages()
    {
        try {
            $query = "SELECT DISTINCT farmers.id, farmers.email, farmers.firstname, farmers.lastname 
            -- , messages._from, messages._to 
                        FROM `messages` ,farmers
                        WHERE farmers.email NOT IN (SELECT DISTINCT messages._from FROM messages WHERE messages._from NOT LIKE '%@growagric.com')
                        AND
                        farmers.email NOT IN (SELECT DISTINCT messages._to FROM messages WHERE messages._to NOT LIKE '%@growagric.com')";

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarmersWithoutMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getEmailTemplateHTML($firstname, $emailtype, $farmerid, $farmeremail, $cta_link = "https://farmers.growagric.com", $invitedby = NULL, $lastname = NULL, $fullname = NULL, $date_of_finance_application = NULL)
    {
        /**
         * include their emails for easy logins?
         * verify their emails? after sign ups
         */
        try {
            $email_template = file_get_contents(__DIR__ . "/../assets/email.template.html");

            // remove newlines, and space
            // causing damaged html message
            // $email_template = preg_replace(array("/\n/", "/\s/"), '', $email_template); // would the email be too long?

            $signup_text = "Thank you for signing up with GrowAgric. Join farmers across Kenya in accessing finance, learning materials, and record keeping for your farm.";

            // work on $invitation_text
            $invitation_text = "Hi {farmerfriendname}, {fullname} is inviting you to join GrowAgric. GrowAgric provides farmers like you with working capital, insureance, traning, record managemnt tools for your farm, and connection to bulk buyers.";

            $password_reset_text = "Click the <b>Reset password</b> link below to reset your password.";

            $finance_application_status_update_text = "There is an update to the finance application you made on {dateoffinanceapplication}. Please log in and navigate to 'Register for Finance' to see your application status under 'Finance Registration History'.";

            $new_admin_message_update_text = "You've been sent a new message. Please log in and navigate to 'Messages' to see the message.";


            $finance_application_submission_text = "
                <p>
                    Thanks for applying for finance (on {dateoffinanceapplication}) with GrowAgric Inc.
                </p>
                <p>
                    A member of our team will provide you with an update on your application shortly.
                </p>
                <p>In the interim, you can:</p>
                <ul class='offered-actions'>
                    <li>
                    <a target='_blank' href='https://farmers.growagric.com/dashboard/learning'>Access training materials</a>
                    </li>
                    <li>
                    <a target='_blank' href='https://farmers.growagric.com/dashboard/records'>
                        Add\Update your farm records
                    </a>
                    </li>
                    <li>
                    <a target='_blank' href='https://farmers.growagric.com/dashboard'>
                        Go to your Dashboard
                    </a>
                    </li>
                </ul>
            ";

            $login_cta_text = "Login";
            $signup_cta_text = "Verify email";
            $invite_cta_text = "Sign up now";

            $reset_password_cta = "Reset password";

            if ($emailtype == Emailing::SIGNUP) {
                $emailbody = str_replace("{body}", $signup_text, $email_template);
                $emailbody = str_replace("{fullname}", $fullname ? $fullname : $firstname, $emailbody);
                $emailbody = str_replace("{cta}", $login_cta_text, $emailbody);

                $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
            } else if ($emailtype == Emailing::INVITE) {
                $emailbody = str_replace("{body}", $invitation_text, $email_template);
                $emailbody = str_replace("{fullname}", $fullname ? $fullname : $firstname, $emailbody);

                // must be after replacing $invitation_text, cause {farmerfriendname} is in $invitation_text
                $emailbody = str_replace("{farmerfriendname}", $invitedby, $emailbody);
                $emailbody = str_replace("{cta}", $invite_cta_text, $emailbody);

                $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
            } else if ($emailtype == Emailing::PASSWORD_RESET) {
                $emailbody = str_replace("{body}", $password_reset_text, $email_template);
                $emailbody = str_replace("{fullname}", $fullname ? $fullname : $firstname, $emailbody);

                $emailbody = str_replace("{cta}", $reset_password_cta, $emailbody);

                // -- get reset password link
                // Instantiate Database to get a connection
                $database_connection = new Database();
                $a_database_connection = $database_connection->connect();
                // Instantiate farmer object
                $farmer = new Farmer($a_database_connection);
                // create reset password request, then send reset password email
                $requestid = $farmer->createNewFarmerPasswordResetRequest($farmerid);

                // get hash of request id, hash(HASHING_ALGORITHM, 4), if hash is ...
                // email/hash


                // ger cta link, in this case, password reset link
                $cta_link = getenv("FARMERS_PROD_BASE_URL") . "/" . "password-reset" . "/" . $farmeremail . "/" . hash(hash_algos()[29], $requestid);
                file_put_contents('php://stderr', print_r("\n\n" . 'creating a new password reset link======' . $cta_link . "\n", TRUE));
                $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
            } else if ($emailtype == Emailing::FINANCE_APPLICATION_UPDATE) {
                $emailbody = str_replace("{body}", $finance_application_status_update_text, $email_template);
                $emailbody = str_replace("{fullname}", $fullname ? $fullname : $firstname, $emailbody);

                $emailbody = str_replace("{dateoffinanceapplication}", $date_of_finance_application, $emailbody);

                $emailbody = str_replace("{cta}", $login_cta_text, $emailbody);

                $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
            } else if ($emailtype == Emailing::FINANCE_APPLICATION_SUBMISSION) { // we should be checking if the string we want to replace exists
                $emailbody = str_replace("{body}", $finance_application_submission_text, $email_template);
                $emailbody = str_replace("{fullname}", $fullname ? $fullname : $firstname, $emailbody);

                $emailbody = str_replace("{dateoffinanceapplication}", $date_of_finance_application, $emailbody); // should we include the date they applied???

                $emailbody = str_replace("{cta}", "Or " . $login_cta_text, $emailbody);

                $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
            } else if ($emailtype == Emailing::NEW_MESSAGE_UPDATE) { // we should be checking if the string we want to replace exists
                $emailbody = str_replace("{body}", $new_admin_message_update_text, $email_template);
                $emailbody = str_replace("{fullname}", $fullname ? $fullname : $firstname, $emailbody);

                $emailbody = str_replace("{cta}", $login_cta_text, $emailbody);

                $emailbody = str_replace("{cta_link}", $cta_link, $emailbody);
                file_put_contents('php://stderr', print_r("\n\n" . 'created new message update template++++' . "\n", TRUE));
            }


            // replace in email template string

            return $emailbody;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r("\n\n" . 'composing email body error::::::' . "\n", TRUE));
            file_put_contents('php://stderr', print_r($err, TRUE));
            return false;
        }
    }

    /**
     * needs refactoring : either do separate methods for emailing different scenarios: or do if checks to make sure the correct data is provided for each scenarios
     */
    public function sendMail($firstname, $emailtype, $sendtoemail, $invitedby = NULL, $lastname = NULL, $fullname = NULL, $date_of_finance_application = NULL, $cta_link = "https://farmers.growagric.com", $farmerid = NULL)
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            file_put_contents('php://stderr', print_r("\n\n" . 'actually sending email' . "\n", TRUE));


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
            $mail->addAddress($sendtoemail, $fullname ? $fullname : $firstname);     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            $mail->addReplyTo(getenv("OUR_EMAIL"), 'GrowAgric Inc');
            // $mail->addCC('cc@example.com');
            $mail->addBCC(getenv("GROW_AGRIC_DEV_EMAIL")); // and | or we save every email we send out

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $emailtype == Emailing::SIGNUP ? 'Welcome!' : ($emailtype == Emailing::INVITE ? 'GrowAgric Invitation' : ($emailtype == Emailing::PASSWORD_RESET ? 'Password Reset' : ($emailtype == Emailing::FINANCE_APPLICATION_UPDATE ? 'Finance Application Update' : 'Hello!!')));
            $mail->Body = $this->getEmailTemplateHTML(trim($firstname), $emailtype, $farmerid, $sendtoemail, $cta_link, $invitedby, $lastname, $fullname, $date_of_finance_application); // 'This is the HTML message body <b>in bold!</b>';
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if ($mail->send()) {

                return true;
                file_put_contents('php://stderr', print_r('SEnt THe MaiL ' . "\n", TRUE));
            } else {
                return false;

                file_put_contents('php://stderr', print_r('did not SEnd THe MaiL ' . "\n", TRUE));
            }
        } catch (\Throwable $err) {
            //throw $err;

            file_put_contents('php://stderr', print_r("Admin.php->sendMail error: Message could not be sent. Mailer Error: {$mail->ErrorInfo}", TRUE));
            return false;
        }
    }

    public function getAllWaitingList()
    {
        try {
            $query = 'SELECT * FROM `waiting_list`';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllWaitingList error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    /**
     * fetch all info from this query ???
     * Or maybe just fetch more
     */
    public function getReviewInfo()
    {
        try {

            $query = 'SELECT 

                (SELECT COUNT(*) FROM farmers) AS no_farmers,
                -- should we utlize existing queries? ... this is similar to getAllFinanceApplications() method query         
                (
                    SELECT COUNT(*) 
                    FROM finance_applications

                    RIGHT JOIN -- not LEFT, cause we want only fin applications that got their status inserted by the triggers
                    finance_application_statuses 
                    ON 
                    finance_applications.id = finance_application_statuses.finance_application_id

                    LEFT JOIN
                    farms
                    ON
                    finance_applications.farmid = farms.id

                    WHERE finance_applications.farmerid IS NOT NULL 
                    AND finance_applications.farmid IS NOT NULL
                    AND farms.deleted = false
                ) AS no_finance_applications,
                
                (SELECT COUNT(*) FROM farms WHERE deleted = false) AS no_farms,
                
                (SELECT COUNT(*) FROM waiting_list) AS no_waiting_list';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getReviewInfo error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAdminByEmail(string $_email)
    {
        try {
            // Create query
            $query = 'SELECT * FROM ' . $this->table . '
                WHERE
                email = :_email
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $e = htmlspecialchars(strip_tags($_email));

            // Execute query statement
            $query_statement->bindParam(':_email', $e);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAdminByEmail error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getSingleChatMessage($_id)
    {
        try {
            // Create query // we need to specify every column we need, this just selects everything from both table
            $query = '
            SELECT farmers.email, farmers.firstname, farmers.lastname, messages.*
            FROM `messages` 
            LEFT JOIN farmers
            ON messages.farmerid = farmers.id
            WHERE messages.`id` = :id
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $i = htmlspecialchars(strip_tags($_id));

            // Execute query statement
            $query_statement->bindParam(':id', $i);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getSingleChatMessage error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function allTotalRecordsOfFarmers()
    {
        $allTotalPerTable = "SELECT COUNT(*) as 'sum', input_records_mortalities.farmerid, 'mortalities' as name FROM `input_records_mortalities` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum1, input_records_medicines.farmerid, 'medicines' as name FROM `input_records_medicines` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum2, input_records_labour.farmerid, 'labour' as name  FROM `input_records_labour` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum3, input_records_income_expenses.farmerid, 'income and expenses' as name  FROM `input_records_income_expenses` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum4, inputs_records_chicken.farmerid, 'chicken' as name  FROM `inputs_records_chicken` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum5, input_records_diseases.farmerid, 'diseases' as name FROM `input_records_diseases` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum6, input_records_brooding.farmerid, 'brooding' as name FROM `input_records_brooding` GROUP BY farmerid
        UNION
        SELECT COUNT(*) as sum7, inputs_records_feeds.farmerid, 'feeds' as name FROM `inputs_records_feeds` GROUP BY farmerid
                    ";


        $stmt = $this->database_connection->prepare($allTotalPerTable);

        $r = $stmt->execute();

        return $stmt;
    }

    public function getAllFinanceApplications()
    {
        try {
            $query = 'SELECT finance_applications.*, finance_application_statuses.status, finance_application_statuses.reason, farmers.`firstname`, farmers.`lastname`, farmers.`middlename`, 
            farmers.`email`, farmers.`phonenumber`, farmers.`timejoined`, farmers.`highesteducationallevel`, 
            farmers.`maritalstatus`, farmers.`age`, farmers.`yearsofexperience`

            ,farms.farmcountylocation
            ,farms.farmsubcountylocation
            ,farms.farmwardlocation
            ,farms.yearsfarming
            ,farms.numberofemployees
            ,farms.haveinsurance
            ,farms.insurer
            ,farms.farmeditems
            ,farms.otherfarmeditems
            ,farms.challengesfaced
            ,farms.otherchallengesfaced
            
            
            FROM finance_applications 
            RIGHT JOIN -- not LEFT, cause we want only fin applications that got their status inserted by the triggers
            finance_application_statuses 
            ON 
            finance_applications.id = finance_application_statuses.finance_application_id
            
            LEFT JOIN
            farmers
            ON
            farmers.id = finance_applications.farmerid
            
            LEFT JOIN
            farms
            ON
            finance_applications.farmid = farms.id
            
            WHERE finance_application_statuses.finance_application_id IS NOT NULL
            AND finance_applications.farmerid IS NOT NULL
            -- and for farms that have not been deleted
            
            AND farms.deleted = false
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFinanceApplications error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarms()
    {
        try {
            // Create query
            // $query = 'SELECT * FROM `farms`
            // -- show only farms that have not been deleted
            // WHERE farms.deleted = false
            // ';

            $query = "SELECT COUNT(farm_chicken_houses.farmid) AS 'no_of_chickenhouses', `farms`.* FROM `farms`

            LEFT JOIN farm_chicken_houses
            ON farms.id = farm_chicken_houses.farmid
            
            WHERE farms.deleted = false
            
            GROUP BY farms.id";

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarms error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmers()
    {
        try {
            // Create query -- do not pick "Chuks Nwa" in prod
            $query = 'SELECT *, NULL as `password` FROM `farmers` '
                // . ( getenv("CURR_ENV") == "production" ? "WHERE id != 3" : "") // not a good enough fix

            ;

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarmers error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getModuleByID($id)
    {
        try {
            // Create query
            $query = 'SELECT * FROM learning_modules
                WHERE
                id = :_id
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $i = htmlspecialchars(strip_tags($id));

            // Execute query statement
            $query_statement->bindParam(':_id', $i);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getModuleByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getCourseByID($id)
    {
        try {
            // Create query
            $query = 'SELECT * FROM learning_courses
                WHERE
                id = :_id
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $i = htmlspecialchars(strip_tags($id));

            // Execute query statement
            $query_statement->bindParam(':_id', $i);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getCourseByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addNewModule($name, $description,)
    {
        $query = 'INSERT INTO learning_modules
            SET
            name = :name,
            description = :description
        ';

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $n = htmlspecialchars(strip_tags($name));
        $d = htmlspecialchars(strip_tags($description));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':name', $n);
        $stmt->bindParam(':description', $d);

        $r = $stmt->execute();

        if ($r) {
            return $this->database_connection->lastInsertId();
            // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
        } else {
            return false;
        }
    }

    public function editExistingCourse($name, $description, $courseid, $url = NULL, $mediatype = NULL)
    {
        try {
            // Create query // if there is url, then there is mediatype
            $query = 'UPDATE learning_courses 
                SET 
                description = :description,
                name = :name 
                '
                .
                ($mediatype ? ',mediatype = :mediatype' : '')
                .
                ($url ? ',url = :url' : '')
                .
                '
                WHERE
                id = :id
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $n = htmlspecialchars(strip_tags($name));
            $desc = htmlspecialchars(strip_tags($description));
            $_id = htmlspecialchars(strip_tags($courseid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':name', $n);
            $stmt->bindParam(':description', $desc);
            $stmt->bindParam(':id', $_id);
            if ($mediatype) {
                $mt = htmlspecialchars(strip_tags($mediatype));
                $stmt->bindParam(':mediatype', $mt);
            }
            if ($url) {
                $u = htmlspecialchars(strip_tags($url));
                $stmt->bindParam(':url', $u);
            }

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed course module update query' . "\n", TRUE));
                return true;
            } else {
                file_put_contents('php://stderr', print_r('Failed to Execute course module update query' . "\n", TRUE));
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Admin.php->editExistingCourse error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function editExistingModule($name, $description, $moduleid)
    {
        try {
            // Create query
            $query = 'UPDATE learning_modules 
                SET 
                description = :description,
                name = :name
                WHERE
                id = :id
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $n = htmlspecialchars(strip_tags($name));
            $desc = htmlspecialchars(strip_tags($description));
            $_id = htmlspecialchars(strip_tags($moduleid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':name', $n);
            $stmt->bindParam(':description', $desc);
            $stmt->bindParam(':id', $_id);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed learning module update query' . "\n", TRUE));
                return true;
            } else {
                file_put_contents('php://stderr', print_r('Failed to Execute learning module update query' . "\n", TRUE));
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Admin.php->editExistingModule error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addNewCourse($mediatype, $name, $description, $url, $moduleid)
    {
        try {
            $query = 'INSERT INTO learning_courses
                SET
                name = :name,
                mediatype = :mediatype,
                description = :description,
                url = :url,
                moduleid = :moduleid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $n = htmlspecialchars(strip_tags($name));
            $desc = htmlspecialchars(strip_tags($description));
            $mt = htmlspecialchars(strip_tags($mediatype));
            $u = htmlspecialchars(strip_tags($url));
            $mid = htmlspecialchars(strip_tags($moduleid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':name', $n);
            $stmt->bindParam(':description', $desc);
            $stmt->bindParam(':mediatype', $mt);
            $stmt->bindParam(':url', $u);
            $stmt->bindParam(':moduleid', $mid);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllModules error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getAllModules()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `learning_modules`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllModules error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getAllCourses()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `learning_courses`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllCourses error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getAllFieldAgents()
    {
        try {
            $query = 'SELECT *, "" AS password
            

            FROM `fieldagents`

            WHERE (fieldagents.email NOT LIKE "%test%" OR fieldagents.email NOT LIKE "%testuser%")
            
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFieldAgents error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getAllFieldAgentFarmVisits()
    {
        try {
            // todo: prefix every farmer details as farmer*, same with fieldagent ... but make sure it's in sync with front end
            $query = 'SELECT

            -- fieldagents_farm_visits details
            `fieldagents_farm_visits`.`id` AS "farmvisitid"
            ,`fieldagents_farm_visits`.`fieldagentid`,`fieldagents_farm_visits`.`farmerid`,`fieldagents_farm_visits`.`farmid`
            ,`fieldagents_farm_visits`.`farmvisittype`
            ,`fieldagents_farm_visits`.`didchickengainnecessaryrequiredweight`,`fieldagents_farm_visits`.`numberofdeadchickensincelastvisit`
            ,`fieldagents_farm_visits`.`totalmortalitytodate`
            ,`fieldagents_farm_visits`.`additionalobservations`,`fieldagents_farm_visits`.`advicegiventofarmer`
            ,`fieldagents_farm_visits`.`dateofnextvisit`
            ,`fieldagents_farm_visits`.`numberofchickenthatcanfitthecurrentchickenhouse`,`fieldagents_farm_visits`.`numberoffinancedchicken`
            ,`fieldagents_farm_visits`.`farmernumberofchildren`,`fieldagents_farm_visits`.`farmernumberofchildrenlessthan18`
            ,`fieldagents_farm_visits`.`isfarmingontrack`,`fieldagents_farm_visits`.`farmernumberofoccupants`,`fieldagents_farm_visits`.`numberofpeopleworkingonfarm`
            
            ,`fieldagents_farm_visits`.`farmermobiledevicetype`,`fieldagents_farm_visits`.`numberofchickenaddedbysupplierondelivery`,`fieldagents_farm_visits`.`numberofdeadchicksondayofdelivery`
            
            ,`fieldagents_farm_visits`.`nameofinsurer`,`fieldagents_farm_visits`.`datefarmercanstartfarmingwithus`,`fieldagents_farm_visits`.`otherfarmedanimals`
            
            ,`fieldagents_farm_visits`.`opinionofhowmanychickenweshouldfinancefarmerfor`,`fieldagents_farm_visits`.`howmuchfinancingisthefarmerseeking`
            ,`fieldagents_farm_visits`.`farmerhousebuildingmaterial`,`fieldagents_farm_visits`.`dateofvisit`,`fieldagents_farm_visits`.`doesfarmerhavepreviousfarmingrecords`
            ,`fieldagents_farm_visits`.`farmerchickenhousebuildingmaterial`,`fieldagents_farm_visits`.`doesfarmerhaveexistinginsurance`
            
            ,`fieldagents_farm_visits`.`seenevidenceofexistinginsurance`,`fieldagents_farm_visits`.`hasfarmerobtainedstampedvetreportwithvetregistrationnumber`
            ,`fieldagents_farm_visits`.`takencopiesoffarmeridsordocumentsandphonenumber`
            
            ,`fieldagents_farm_visits`.`doesfarmerkeeplayers`,`fieldagents_farm_visits`.`seenproofthatfarmerhasbuyers`
            ,`fieldagents_farm_visits`.`takencopiesorphotosoffarmerpreviousfarmingrecords`,`fieldagents_farm_visits`.`didfarmerfillcicinsuranceformcorrectly`,`fieldagents_farm_visits`.`givenfarmerthecicinsuranceformtofill`
            ,`fieldagents_farm_visits`.`farmerpobox`,`fieldagents_farm_visits`.`farmerproofofbuyersfileinput`,`fieldagents_farm_visits`.`farmerpincertfileinput`
            ,`fieldagents_farm_visits`.`farmeridfileinput`,`fieldagents_farm_visits`.`farmerexistinginsurancefileinput`
            ,`fieldagents_farm_visits`.`farmerpreviousfarmingrecordsfileinput`,`fieldagents_farm_visits`.`otheranimalkeptinfarm`,`fieldagents_farm_visits`.`date_of_entry`
            

            -- farmers details
            ,`farmers`.`firstname` AS "farmerfirstname",`farmers`.`lastname` AS "farmerlastname"
            ,`farmers`.`middlename` AS "farmermiddlename",`farmers`.`email` AS "farmeremail",`farmers`.`phonenumber`
            ,`farmers`.`timejoined`,`farmers`.`highesteducationallevel`
            ,`farmers`.`maritalstatus`,`farmers`.`age`,`farmers`.`yearsofexperience`


            -- farms details
            ,`farms`.`farmcountylocation`,`farms`.`farmsubcountylocation`,`farms`.`farmwardlocation`
            ,`farms`.`yearsfarming`,`farms`.`numberofemployees`,`farms`.`haveinsurance`
            ,`farms`.`insurer`,`farms`.`id`,`farms`.`farmeditems`
            ,`farms`.`otherfarmeditems`,`farms`.`challengesfaced`
            ,`farms`.`otherchallengesfaced`,`farms`.`multiplechickenhouses`

            -- fieldagents
            ,`fieldagents`.`firstname` AS "fieldagentfirstname",`fieldagents`.`lastname` AS "fieldagentlastname",
            `fieldagents`.`lastlogintime`,`fieldagents`.`email` AS "fieldagentemail"
            ,`fieldagents`.`assignedsubcounties`
            

            FROM `fieldagents_farm_visits`
            
            LEFT JOIN fieldagents
            ON `fieldagents_farm_visits`.`fieldagentid` = fieldagents.id
            
            LEFT JOIN farmers
            ON fieldagents_farm_visits.farmerid = farmers.id
            
            LEFT JOIN farms
            ON fieldagents_farm_visits.farmid = farms.id
            
            WHERE farms.`deleted` = false

            AND (fieldagents.email NOT LIKE "%test%" OR fieldagents.email NOT LIKE "%testuser%")

            ORDER BY `fieldagents_farm_visits`.`dateofvisit` DESC
            ';


            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFieldAgentFarmVisits error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getIncompleteFarmerProfiles()
    {
        $query = '
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
    }

    public function getFarmersWithoutRecords()
    {
        $willNotUseQuery = 'SELECT farmers.id, farmers.firstname, farmers.lastname, farmers.email
            , COUNT(input_records_mortalities.farmerid) AS "input_records_mortalities_count"
            , COUNT(input_records_medicines.farmerid) AS "input_records_medicines_count"
            , COUNT(input_records_labour.farmerid) AS "input_records_labour_count"
            , COUNT(input_records_income_expenses.farmerid) AS "input_records_income_expenses_count"
            , COUNT(input_records_diseases.farmerid) AS "input_records_diseases_count"
            , COUNT(input_records_brooding.farmerid) AS "input_records_brooding_count"
            , COUNT(inputs_records_feeds.farmerid) AS "inputs_records_feeds_count"
            , COUNT(inputs_records_chicken.farmerid) AS "inputs_records_chicken_count"
            
            FROM farmers
            
            LEFT JOIN input_records_mortalities
            ON input_records_mortalities.farmerid = farmers.id
            
            LEFT JOIN input_records_medicines
            ON input_records_medicines.farmerid = farmers.id
            
            LEFT JOIN input_records_labour
            ON input_records_labour.farmerid = farmers.id
            
            LEFT JOIN input_records_income_expenses
            ON input_records_income_expenses.farmerid = farmers.id
            
            LEFT JOIN input_records_diseases
            ON input_records_diseases.farmerid = farmers.id
            
            LEFT JOIN input_records_brooding
            ON input_records_brooding.farmerid = farmers.id
            
            LEFT JOIN inputs_records_feeds
            ON inputs_records_feeds.farmerid = farmers.id
            
            LEFT JOIN inputs_records_chicken
            ON inputs_records_chicken.farmerid = farmers.id
            
            GROUP BY farmers.id
        ';


        // from https://stackoverflow.com/a/12464135/9259701
        $query = 'SELECT f.id, f.firstname, f.lastname, f.email
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
            (SELECT farmerid, COUNT(*) AS "inputs_records_chicken_count"
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
    }
}
