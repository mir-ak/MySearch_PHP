<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- <link rel="icon" type="image/png" sizes="68x68" href="image/research.png" /> -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <title> MySearch </title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style/style.css" />

</head>

<body>
    <?php
    require('./database/config.php');
    require('./indexation/indexation.php');
    require('./database/saveData_in_dataBase.php');
    $conn =  configMysql();
    $mySearch = [];
    main($conn);

    function get_join_words_documents($select, $table, $joins, $where)
    {
        return "SELECT $select FROM $table $joins $where";
    }

    function get_data($row)
    {
        $doc = [];
        foreach ($row as $index => $value) {
            $doc[$value["id_document"]] = $value;
        }
        return $doc;
    }

    function get_join_data_value($word, $conn)
    {

        $insert_data = get_join_words_documents(
            "*",
            "join_documents_and_words",
            "INNER JOIN documents on join_documents_and_words.id_document = documents.id_document INNER JOIN words on join_documents_and_words.id_word = words.id_word",
            "WHERE words.word LIKE \"$word%\" ORDER BY join_documents_and_words.weight DESC;"
        );
        $rows = $conn->prepare($insert_data);
        if ($rows->execute()) {
            $row = $rows->fetchAll();
            if (!empty($row)) {
                return get_data($row);
            }
        }
        return [];
    }

    session_start();
    if (isset($_POST['words'])) {
        $word = stripslashes($_REQUEST['words']);
        $mySearch =  get_join_data_value($word, $conn);
    }
    ?>
    <form class="box" action="" method="post">
        <div class="typing-demo">
            <h1 class="box-login">MySearch</h1>
        </div>
        <input type="text" class="box-input" name="words" placeholder="Search ">
        <input class="button-submit" type="submit" value=" " name="submit">
        <div>
            <?php if (!empty($mySearch)) foreach ($mySearch as $index => $value) { ?>
                <li style="font-size:18px; color:  #ffffff; width: 600px;">
                    <?php debug_to_console($value["path"]); ?>
                    <strong><a href="../<?php echo $value["path"]; ?>" target=" _blank"> <?= $value['title'] ?></a>
                    </strong>
                    <p style="font-size:18px; color: #ffffff;"><?= $value['description'] ?></p>
                </li>
            <?php }; ?>
            </ul>

        </div>

    </form>
</body>

</html>