<?php
function configMysql()
{
    $SERVER_NAME_01 = "localhost";
    $USERNAME_02 = 'PhpAdmin';
    $PASSWORD_03 = 'HTML_FILES';
    $DB_NAME_04 = 'HTML_FILES';
    try {
        //On établit la connexion
        return new PDO("mysql:host=$SERVER_NAME_01;dbname=$DB_NAME_04", $USERNAME_02, $PASSWORD_03);
        //On vérifie la connexion
    } catch (PDOException $e) {
        echo "Error connexion PDO, $e\n";
        die();
    }
}
