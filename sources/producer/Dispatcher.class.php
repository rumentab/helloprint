<?php

/**
 * Class Dispatcher
 *
 * Processes requests from the webserver
 */
class Dispatcher
{
    /**
     * Define the function name that the JS file in the webserver API should execute with the result of the consumer APP
     */
    const JSONP_FUNCTION = "parseResult(%s)";

    const ALLOWED_ACTIONS = [
        'resetPassword', 'login', 'logout'
    ];

    /**
     * @var array Contains configuration data for the APP
     * @see config.php
     */
    private $config;

    /**
     * @var bool TRUE if ajax request
     */
    private $is_ajax;

    /**
     * @var int The status of the result.
     */
    private $status_code;

    /**
     * @var string The message that will be returned to the webserver API
     */
    private $message;

    /**
     * Dispatcher constructor.
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

        $this->is_ajax = $_REQUEST['callback'];
    }

    /**
     * Check API authentication
     *
     * @param string $auth_key
     * @return bool TRUE if provided key matches the key in config file, otherwise FALSE
     */
    private function authenticate(string $auth_key = ""): bool
    {
        return $auth_key === $this->config['auth_key']['webserver'];
    }

    /**
     * @param string $type
     * @param array $params
     * @return null|Array
     */
    private function sendMessage(string $type, Array $params): ?Array
    {
        $url = $this->config['address']['consumer'];

        $r = curl_init();
        curl_setopt($r, CURLOPT_URL, $url);
        curl_setopt($r, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($r, CURLOPT_TIMEOUT, 30);
        curl_setopt($r, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($r, CURLOPT_POST, TRUE);
        curl_setopt($r, CURLOPT_POSTFIELDS, http_build_query($params));

        $response  = curl_exec($r);

        if ($response === FALSE)
        {
            $error = [
                'message' => curl_error($r)
            ];
        }
        else {
            $error = NULL;

            $response = json_decode($response, TRUE);
        }
        $response['status'] = (int) curl_getinfo($r, CURLINFO_HTTP_CODE);

        curl_close($r);

        return ($error) ? $error : $response;
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
        $result = $this->sendMessage(
            'resetPassword',
            [
                'auth_key' => $this->config['auth_key']['consumer'],
                'action' => "resetPassword",
                'payload' => ['email' => $data['email']]
            ]
        );
        $this->prepareResult($result['status'], $result['message']);
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
        $result = $this->sendMessage(
            'login',
            [
                'auth_key' => $this->config['auth_key']['consumer'],
                'payload' => [
                    'username' => $data['username'],
                    'password' => $data['password']
                ],
                'action' => "login"
            ]
        );
        $this->prepareResult($result['status'], $result['message']);
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
     * Prints the result of the APP operations
     */
    public function __destruct()
    {
        $response = [
            'status' => $this->status_code,
            'message' => $this->message
        ];

        if ($this->is_ajax)
        {
            printf(self::JSONP_FUNCTION, json_encode($response));
        }
        else
        {
            header("HTTP/1.1 {$response['status']}");
            header("Content-type: application/json; charset: UTF-8");
            die(json_encode($response));
        }

    }
}
