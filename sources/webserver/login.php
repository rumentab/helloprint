<?php

session_start();

function create_alert($type, $message)
{
    $allowedTypes = ['danger', 'success', 'warning', 'info', 'primary', 'secondary'];

    $icon = [
        'danger' => "fa-times-circle",
        'success' => "fa-check-circle-o",
        'warning' => "fa-exclamation-triangle",
        'info' => "fa-info-circle",
        'primary' => "",
        'secondary' => ""
    ];

    if (array_search($type, $allowedTypes) === FALSE)
    {
        $type = "secondary";
    }

    $alert = '<div class="alert alert-' . $type . ' alert-dismissible position-absolute mt-2 fade show" role="alert">' .
            '<div class="d-table-cell align-middle px-2">' .
            '<span class="fa fa-3x ' . $icon[$type] . ' text-' . $type . '"></span>' .
            '</div>' .
            '<div class="d-table-cell align-middle px-2">' .
            '<p class="mb-0">' . $message . '</p>' .
            '</div>' .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' .
            '<span aria-hidden="true">&times;</span>' .
            '</button>' .
            '</div>';

    return $alert;

}

if (!empty($_POST))
{
    $config = file_get_contents("config.json");
    $config = json_decode($config, TRUE);

    $params = [
        'authToken' => $config['authToken'],
        'payload' => [
            'username' => filter_input(INPUT_POST, 'username'),
            'password' => filter_input(INPUT_POST, 'password')
        ],
        'action' => 'login'
    ];

    $crl = curl_init($config['producer'] . 'index.php');
    curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($crl, CURLOPT_TIMEOUT, 30);
    curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($crl, CURLOPT_POST, TRUE);
    curl_setopt($crl, CURLOPT_POSTFIELDS, http_build_query($params));
    $response  = curl_exec($crl);

    if (200 === (int) curl_getinfo($crl, CURLINFO_HTTP_CODE) && !empty($response))
    {
        $alert = NULL;

        curl_close($crl);

        $response = json_decode($response, TRUE);

        $_SESSION['loggedin'] = TRUE;

        $_SESSION['user'] = $response['message'];

        header("Location: index.php");
        die();
    }
    else
    {
        $error = curl_error($crl);

        if (empty($error))
        {
            $response  = (!empty($response)) ? json_decode($response, TRUE) : ['message' => "Undefined error!"];

            $error = (!empty($response['message'])) ? $response['message'] : "Undefined error!";
        }

        $alert = create_alert("danger", $error);

        curl_close($crl);

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Login | HelloPrint</title>

    <link rel="stylesheet" href="vendor/twitter/bootstrap/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="vendor/components/font-awesome/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="assets/css/common.css"/>

    <link rel="icon" href="images/favicon.ico" size="16x16"/>

    <script src="vendor/components/jquery/jquery.min.js"></script>
    <script src="vendor/twitter/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/common.js"></script>
</head>
<body>
<?php echo $alert; ?>
<div class="container">
    <h1 class="d-flex justify-content-center text-primary mt-3">
        <img src="images/logo.png" alt="HelloPrint Logo">&nbsp;Login
    </h1>
    <h2 class="d-flex justify-content-center">Welcome to HelloPrint</h2>
    <div class="row">
        <form id="login_form" method="post" onsubmit="javascript: return false;"
              class="col-xs-12 col-lg-4 mx-auto border border-primary rounded my-3 my-lg-5">
            <div class="form-group">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-user"></i></div>
                        </div>
                        <input type="text" class="form-control" id="username" name="username">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-key"></i></div>
                        </div>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </div>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-success" id="submit">Submit</button>
            </div>
            <div class="form-group text-center">
                <button type="button" class="btn btn-primary" id="forgot_pass" data-toggle="modal"
                        data-target="#email_ask">
                    <span class="fa-stack fa-lg">
                        <i class="fa fa-key fa-stack-1x"></i>
                        <i class="fa fa-ban fa-stack-2x text-danger"></i>
                    </span>
                    Forgot my password
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="email_ask" tabindex="-1" role="dialog" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Password recovery</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label for="email">Please enter a valid email:</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="my.email@example.com"/>
                </div>
                <small class="row text-danger px-3" id="error-hint"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="request_pass">Send me a new password</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>