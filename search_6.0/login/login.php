<!DOCTYPE html>
<html lang="fr">

<head>
    <title> Login </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin.css" />
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
</head>

<body>
    <?php
    include('../database/config.php');
    function debug_to_console($data, $context = 'Debug in Console')
    {
        // Mise en mémoire tampon pour résoudre les problèmes des frameworks, comme header() dans ce et pas un retour solide.
        ob_start();
        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);
        echo $output;
    }
    $conn = configMysql();
    session_start();
    if (isset($_POST['username'], $_POST['password'])) {
        $username = stripslashes($_REQUEST['username']);
        $password = stripslashes($_REQUEST['password']);
        $query = "SELECT * FROM `Login_Admin` WHERE username= '" . $username . "' and password='" . hash('sha256', $password) . "'";
        $rows = $conn->prepare($query);
        if ($rows->execute()) {
            $row = $rows->fetchAll();
            debug_to_console($row);
            if (!empty($row)) {
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
                header("Location: ../admin/admin.php");
                die();
            } else {
                $message = "Le nom d'utilisateur ou le mot de passe est incorrect.";
            }
        } else {
            $message = "Le nom d'utilisateur ou le mot de passe est incorrect.";
        }
    }
    ?>
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="../index.php"><i class='fas fa-reply-all' style='font-size:42px;color:white'></i></a>
    </nav>
    <form class="box" action="" method="POST">
        <h1 class="box-login">LogIn</h1>
        <input type="text" class="box-input" name="username" placeholder="Nom d'utilisateur *">
        <input type="password" class="box-input" name="password" placeholder="Mot de passe *">
        <input type="submit" value="Connexion " name="submit" class="box-button-login">
        <!--<p class="box-register"> <a href="singup.php"><strong>Je M'inscris</strong></a></p>-->
        <?php if (!empty($message)) { ?>
            <p class="errorMessage"><?php echo $message; ?></p>
        <?php } ?>
    </form>
</body>

</html>