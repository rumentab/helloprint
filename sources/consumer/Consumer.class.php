<?php

/**
 * Class Consumer
 *
 * Processes requests from the producer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "vendor/autoload.php";

class Consumer
{

    const ALLOWED_ACTIONS = [
        'resetPassword', 'login', 'logout'
    ];

    /**
     * @var array Contains configuration data for the APP
     * @see config.php
     */
    private $config;

    /**
     * @var int The status of the result.
     */
    private $status_code;

    /**
     * @var string The message that will be returned to the webserver API
     */
    private $message;

    /**
     * @var mysqli
     */
    private $db;

    /**
     * @var PHPMailer
     */
    private $mailer;

    /**
     * Consumer constructor.
     * @param array $config_data
     * @param string $auth_token
     */
    public function __construct(Array $config_data = [], string $auth_token = NULL)
    {
        if (empty($config_data))
        {
            $this->prepareResult(404, "Service temporary unavailable!");
        }

        $this->config = $config_data;

        if (empty($auth_token) || !$this->authenticate($auth_token))
        {
            $this->prepareResult(401, "Authorization failed!");
        }

        $this->db = $this->dbConnect();

        try{
            $this->mailer = $this->initMailer();
        }
        catch (Exception $e)
        {
            $this->mailer = NULL;
        }
    }

    /**
     * Check API authentication
     *
     * @param string $auth_key
     * @return bool TRUE if provided key matches the key in config file, otherwise FALSE
     */
    private function authenticate(string $auth_key = ""): bool
    {
        return $auth_key === $this->config['auth_key']['producer'];
    }

    /**
     * @return void|mysqli
     */
    private function dbConnect()
    {
        $db = new mysqli();

        try
        {
            $db->connect("db", $this->config['db']['username'], $this->config['db']['password'], $this->config['db']['db_name'], $this->config['db']['port']);

            if ($db->connect_errno > 0)
            {
                throw new Exception("Cannot connect to DB! Error: {$db->connect_error}");
            }

            return $db;
        }
        catch (Exception $e)
        {
            $this->prepareResult(410,  "Service temporary unavailable");

            $this->log($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @return null|PHPMailer
     */
    private function initMailer() : PHPMailer
    {
        try
        {
            $mail = new PHPMailer();

            $mail->isSMTP();
            $mail->Host = $this->config['smtp']['host'];
            $mail->SMTPAuth = $this->config['smtp']['SMTPAuth'];
            $mail->Username = $this->config['smtp']['username'];
            $mail->Password = $this->config['smtp']['password'];
            $mail->SMTPSecure = $this->config['smtp']['SMTPSecure'];
            $mail->Port = $this->config['smtp']['port'];
            $mail->setFrom($this->config['smtp']['username'], "Helloprint Mailer");

            return $mail;
        }
        catch (Exception $e)
        {
            $this->log($e->getCode(), $e->getMessage());

            throw new Exception($e->getMessage());
        }
    }

    /**
     * Main function of the APP. Processes webserver API requests and consumenr APP responses
     *
     * @param string $action
     * @param array $data
     */
    public function processRequest(string $action = NULL, Array $data = []): void
    {
        if (array_search($action, self::ALLOWED_ACTIONS) === FALSE || !method_exists($this, $action))
        {
            $this->prepareResult(405, "Requested action not allowed!");
        }
        elseif ($action === "resetPassword" && is_null($this->mailer))
        {
            $this->prepareResult(410, "Service temporary unavailable!");
        }
        $this->$action($data);
    }

    /**
     * Reset password action
     *
     * @param array $data
     * @return null|string
     */
    private function resetPassword(Array $data): void
    {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        {
            $this->prepareResult(400, "Please enter a valid email address");
        }
        $user = $this->getUserByMail($data['email']);

        if (is_null($user))
        {
            $this->prepareResult(404, "This email not found. Try another one...");
        }
        try
        {
            $this->mailer->addAddress($user->email);
            $this->mailer->Subject = "Forgotten password request received";
            $this->mailer->Body = "We received a forgotten password request from you.\n\n" .
                "Your username is: " . $user->username . "\n" .
                "Your password is: " . $user->password . "\n\n" .
                "You can log into Helloprint by clicking on the following link:\n" .
                $this->config['urls']['webserver'] . "\n";
            if ($this->mailer->send())
            {
                $this->prepareResult(202, "An email with your password was sent to <strong>{$user->email}</strong>");
            }
            else
            {
                throw new \Exception("Email sending failed");
            }
        }
        catch(\Exception $e)
        {
            $this->log($e->getCode(), $e->getMessage());

            $this->prepareResult(410, "Service temporary unavailable!");
        }
    }

    /**
     * Login action
     *
     * @param array $data
     * @return null|string
     */
    private function login(Array $data): void
    {
        if (empty($data['username']) || empty($data['password']))
        {
            $this->prepareResult(400, "Please enter a username and/or password");
        }
        $user = $this->getUser($data['username'], $data['password']);

        if (is_null($user))
        {
            $this->prepareResult(404, "Wrong username and/or password");
        }
        $this->prepareResult(200, $user->username);
    }

    /**
     * @param string $email
     * @return null|stdClass
     */
    private function getUserByMail($email): ?stdClass
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $email = $this->db->escape_string($email);
        }
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0)
        {
            return $result->fetch_object();
        }
        else
        {
            return NULL;
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @return null|stdClass
     */
    private function getUser($username, $password): ?stdClass
    {
        $username = $this->db->escape_string($username);

        $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0)
        {
            $user = $result->fetch_object();

            if ($user->password === $password)
            {
                return $user;
            }
            else
            {
                return NULL;
            }
        }
        else
        {
            return NULL;
        }
    }

    /**
     * Prepares result variables and calls destructor
     *
     * @param int $status_code
     * @param string|NULL $message
     */
    private function prepareResult(int $status_code = 404, string $message = NULL): void
    {
        $this->status_code = $status_code;

        $this->message = $message;

        exit;

    }

    /**
     * @param $code
     * @param $message
     */
    private function log($code, $message) : void
    {
        if (!file_exist(__DIR__ . "/log") || !is_dir(__DIR__ . "/log"))
        {
            mkdir(__DIR__ . "/log", 0775);
        }

        $file = fopen("log/consumer.log", "a+");

        $row = date("Y-m-d H:i:s") . "\t" . sprintf("%' 5s", $code) . "\t" . $message . "\n";

        fwrite($file, $row);

        fclose($file);
    }

    /**
     * Prints the result of the APP operations
     */
    public function __destruct()
    {
        if ($this->db instanceof mysqli)
        {
            $this->db->close();
        }
        $response = [
            'status' => $this->status_code,
            'message' => $this->message
        ];

        header("HTTP/1.1 {$response['status']}");
        header("Content-type: application/json; charset: UTF-8");
        die(json_encode($response));

    }
}
