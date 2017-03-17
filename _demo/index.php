<html>
<head>
    <title>MCAuth - Authenticate & Link Users to their Minecraft account</title>
    <link id="favicon" rel="shortcut icon" type="image/png" href="/favicon.png"/>

    <meta name="description" content="MCAuth allows players to link their Minecraft account to websites, apps, etc.">
    <meta name="keywords" content="minecraft,authentication,link,login,register">
    <meta name="author" content="inventivetalent">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Minecraft Authentication">
    <meta property="og:image" content="">
    <meta property="og:description" content="MCAuth allows players to link their Minecraft account to websites, apps, etc.">

    <meta property="twitter:title" content="Minecraft Authentication">
    <meta property="twitter:image" content="">
    <meta property="twitter:description" content="MCAuth allows players to link their Minecraft account to websites, apps, etc.">
    <meta property="twitter:creator" content="@Inventivtalent">
    <meta property="twitter:card" content="summary">

    <meta name="robots" content="index, follow">

    <link href="https://mcauth.ga/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
</head>

<style>
    input {
        width: 25%;
    }

    .panel {
        box-shadow: none !important;
    }
</style>
<body>
<div class="container-fluid">
    <?php

    session_start();

    if (isset($_GET["id"]) && isset($_GET["request_id"]) && isset($_GET["code"])) {

        $post = array(
            "id" => $_GET["id"],
            "request_id" => $_GET["request_id"],
            "request_secret" => $_SESSION["requestSecret"],
            "code" => $_GET["code"]
        );

        $ch = curl_init("http://api.mcauth.ga/auth/status/" . $_GET["id"]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            echo curl_error($ch);
        }
        $json = json_decode($result, true);
        curl_close($ch);

        ?>
        Status result:
        <pre>
    <?php
    echo $result;
    ?>
    </pre>
        <?php

    } else if (isset($_POST["submit"])) {
        $post = array(
            "request_id" => $_POST["request_id"],
            "request_secret" => $_POST["request_secret"],
            "request_callback" => $_POST["request_callback"],
            "request_ip" => getIp(),
            "username" => $_POST["username"]
        );

        $ch = curl_init("http://api.mcauth.ga/auth/start");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            echo curl_error($ch);
        }
        $json = json_decode($result, true);
        curl_close($ch);

        $redirectUrl = "https://api.mcauth.ga/auth/authorize/" . $json["id"] . "?request_id=" . $_POST["request_id"] . "&username=" . $_POST["username"] . "&style=" . $_POST["style"];

        ?>
        Start result:
        <pre>
    <?php
    echo $result;
    ?>
    </pre>
        <br/>
        <a class="btn btn-primary" href="<?php echo $redirectUrl; ?>" target="_parent">Continue authentication on mcauth.ga &nbsp;<i class="fa fa-arrow-right" aria-hidden="true"></i></a>

        <?php
    } else {
    $requestSecret = hash("sha256", microtime(true) . rand() . $_SERVER["HTTP_REMOTE_IP"] . $_SERVER["HTTP_REFERER"]);
    $_SESSION["requestSecret"] = $requestSecret;

    ?>

    <form action="." method="post">
        <fieldset>
            <legend>Public Request Fields</legend>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
            </div>
            <br/>
            <div class="form-group">
                <label for="style">Style</label>
                <select class="form-control" name="style" id="style">
                    <option value="default">Default</option>
                    <option value="simple">Simple</option>
                </select>
            </div>
        </fieldset>

        <br/>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <button type="button" class="btn btn-default btn-xs spoiler-trigger" data-toggle="collapse">Toggle Hidden Fields</button>
                </div>
                <div class="panel-collapse collapse out">
                    <fieldset>
                        <legend>Private Request Fields</legend>
                        <div style="font-size: 16px;">
                            <strong>Please note that the request secret should NEVER be visible to the user!</strong>
                            <br/>
                            All of these fields are only visible for demonstration purposes, so you can check the returned values after the authentication.
                        </div>
                        <br/>
                        <div class="form-group">
                            <label for="identifier">request_id</label>
                            <input type="text" name="request_id" class="form-control" readonly value="<?php echo hash("sha1", microtime(true) . rand()); ?>">
                        </div>
                        <div class="form-group">
                            <label for="secret">request_secret</label>
                            <input type="text" name="request_secret" class="form-control" readonly value="<?php echo $requestSecret; ?>">
                        </div>
                        <br/>
                        <div class="form-group">
                            <label for="url">request_callback</label>
                            <input type="url" name="request_callback" class="form-control" readonly value="https://mcauth.ga/_demo/return.php">
                        </div>
                        <div class="form-group">
                            <label for="ip">request_ip</label>
                            <input type="text" name="request_ip" class="form-control" readonly value="<?php echo getIp(); ?>"
                            <small class="form-text"><strong>Note: </strong>MCAuth relies on the requesting IP to be the same for every step, including the connection to the Minecraft server</small>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <br/>
        <br/>
        <input type="hidden" name="submit" value="true">
        <button class="btn btn-primary" type="submit">Submit</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script>
    $(".spoiler-trigger").click(function () {
        $(this).parent().next().collapse('toggle');
    });
</script>
</body>
</html>
<?php
}
function getIp()
{
    $keys = array('HTTP_CF_CONNECTING_IP', 'X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR');
    foreach ($keys as $key) {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }

    return $_SERVER['REMOTE_ADDR'];
}

