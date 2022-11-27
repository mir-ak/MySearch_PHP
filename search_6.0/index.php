<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- icone -->
    <link rel="icon" type="image/png" href="http://4.bp.blogspot.com/-OcDQ6Z9ojlQ/VD1KnwJjFOI/AAAAAAAAAgs/cu_pKN6bpL8/s1600/magnifier.png" />
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <!-- style css --->
    <link rel="stylesheet" href="styles/filetree.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="styles/styles.css" />

    <title> MySearch </title>
    <meta charset="utf-8">
    <script>
        function myFunction(index) {
            var id = document.getElementById("myDIV-" + index);
            if (id.style.display === "none") {
                id.style.display = "block";
            } else {
                id.style.display = "none";
            }
        }
    </script>
</head>

<body>
    <?php
    require('./database/config.php');
    require('./indexation/indexation.php');
    require('./database/saveData_in_dataBase.php');
    require('./nuage/nuage.php');
    $conn =  configMysql();

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

    function reverse_word($word)
    {
        $correct = split_word($word);
        asort($correct);
        return implode('', $correct);
    }

    function get_join_data_value($conn, $word)
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
        } else
            return null;
    }

    function get_data_correction($conn, $correct)
    {
        $insert_data = get_join_words_documents(
            "*",
            "join_documents_and_words",
            "INNER JOIN documents on join_documents_and_words.id_document = documents.id_document INNER JOIN words on join_documents_and_words.id_word = words.id_word",
            "WHERE word_order LIKE \"%$correct%\" ORDER BY join_documents_and_words.weight DESC;"
        );
        $rows = $conn->prepare($insert_data);
        if ($rows->execute()) {
            $row = $rows->fetchAll();
            if (!empty($row)) {
                return get_data($row);
            }
        } else
            return null;
    }

    function display_invalid_word($word)
    { ?>
        <div style="font-size:18px; color:  #ffffff; width: 600px;">
            Aucun document ne correspond aux termes de recherche spécifiés à <?php echo '( ' . $word . ' )' ?>
        </div>

        <?php
    }

    function display_word_correct($word_correct, $correct)
    {
        $word_value = [];
        foreach ($word_correct as $index => $value) {
            $word_value[] = $value[8];
        }
        if (!empty($word_correct) && $correct != '') { ?>
            <div style="font-size:18px; color:  #ffffff; width: 600px;">
                Essayez avec l'orthographe
                <?php foreach ($word_value as $value) { ?>
                    <a href="MySearch?words=<?= $value ?>">
                        <?php echo $value ?>
                    </a>&nbsp;
                <?php } ?>
            </div>
    <?php
        }
    }

    ?>
    <nav class="navbar navbar-expand-lg navbar-light">
        &nbsp;
        <a class="w3-xxxlarge" href="index.php"><i class="fa fa-home"></i></a>
        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
            <div class="box_"></div>
            <a class="w3-xxlarge" href="./login/login.php"><i class="fas fa-user-lock"></i></a>&nbsp;&nbsp;
        </div>
    </nav>
    <form class="boxFrom" action="MySearch" method="GET">
        <div class="typing-demo">
            <h1 class="box-login">MySearch</h1>
        </div>
        <input type="text" class="box-input" name="words" placeholder="Search " value="<?= isset($_GET['words']) ? stripslashes($_REQUEST['words']) : "" ?>">
        <input class="button-submit" type="submit" value=" ">

        <?php
        if (isset($_GET['words'])) {
            $word = stripslashes($_REQUEST['words']);
            $mySearch =  get_join_data_value($conn, $word);
            $correct = reverse_word($word);
            $word_correct =  get_data_correction($conn, $correct);
            empty($mySearch) ? display_word_correct($word_correct, $correct) : null;
            empty($mySearch) && empty($word_correct) ? display_invalid_word($word) : null;
            if (!empty($mySearch) && $word != '') foreach ($mySearch as $index => $value) { ?>
                <div style="font-size:18px; color:  #1878C7; width: 600px;">
                    <strong><a href="<?= $value['path'] ?>" target="_blank"><?php echo $value['title']; ?></a>
                        &nbsp;
                        <button onclick="myFunction(<?= $index ?>)" type="button" value="<?= $index ?>" class="btn btn-labeled btn-info btn-circle btn-lg">
                            <i style="position: relative; right: 0px; bottom:5px" class="fa fa-cloud"></i>
                        </button>
                    </strong>
                    <p style="font-size:18px; color: #ffffff;"><?= $value['description'] ?></p>
                    <div class="quote-wrapper" id="myDIV-<?= $index ?>" style="display: none">
                        <?php echo genererNuage(search_with_genererNuage($conn, $value["id_document"])) ?>
                    </div>
                </div>
        <?php }
        } ?>

    </form>

</body>

</html>