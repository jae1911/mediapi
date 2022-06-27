<?php
    $pagetitle = 'Register';

    require('includes/header.php');

    if (isset($_POST['submit'])) {
        $err = [];

        if (!isset($_POST['username']) || !isset($_POST['password']))
            $err[] = 'Please input a valid username or password<br/>';
        else if (empty($_POST['username']) || empty($_POST['password']))
            $err[] = 'Please input an username or password<br/>';


        if (empty($err)) {
            // Continue login sequence
            $username = $_POST['username'];
            $password = $_POST['password'];

            $loginApi = new LoginApi();
            $regres = $loginApi->userAction($username, $password, "register");
            if(!$regres[0]) {
                $res = $loginApi->userAction($username, $password, "login");

                if($res[0]) {
                    $_SESSION['token'] = $res[1];
                }
            } else {
                $res = $loginApi->userAction($username, $password, "login");
                if($res[0]) {
                    $_SESSION['token'] = $res[1];
                }
            }
        }
    }
?>

<div class="medium">
    <h1>Mediapi</h1>
    <hr />
    <p>Register or login to your Mediapi account to use the database.</p>
    <p><a href="index.php">Return to index.</a></p>

    <?php
    if(isset($err)) {
        foreach($err as $e) {
            print("<p>" . $e . "</p>");
        }
    }
    ?>

    <?php if (!$loggedin) { ?>
    <form action="user.php" method="post">
        <p>Your username: <input type="text" name="username"/></p>
        <p>Your password: <input type="password" name="password"/></p>
        <p><input type="submit" name="submit" /></p>
    </form>
    <?php } else { ?>
        <script type="text/javascript">window.location = "index.php";</script>
    <?php } ?>
</div>

<?php
    require('includes/footer.php');
?>