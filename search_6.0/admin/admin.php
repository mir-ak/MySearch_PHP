<?php
// Initialiser la session
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
  header("Location: ../login/login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
  <!-- w3s -->
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <!-- jquery -->
  <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
  <!-- css -->
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="../styles/filetree.css">

  <title>Admin</title>

  <script type="text/javascript">
    $(document).ready(function() {

      $('#container').html('<h4 style="align-items: center"> Arborescence des fichiers </h4> <ul class="filetree start"><li class="wait">' + 'Loading Tree...' + '<li></ul>');

      getfilelist($('#container'), '../Files');

      function getfilelist(cont, root) {

        $(cont).addClass('wait');

        $.post('afficheDocs.php', {
          dir: root
        }, function(data) {

          $(cont).find('.start').html('');
          $(cont).removeClass('wait').append(data);
          if ('Files' == root)
            $(cont).find('UL:hidden').show();
          else
            $(cont).find('UL:hidden').slideDown({
              duration: 500,
              easing: null
            });

        });
      }

      $('#container').on('click', 'LI A', function() {
        var entry = $(this).parent();

        if (entry.hasClass('folder')) {
          if (entry.hasClass('collapsed')) {

            entry.find('UL').remove();
            getfilelist(entry, escape($(this).attr('rel')));
            entry.removeClass('collapsed').addClass('expanded');
          } else {

            entry.find('UL').slideUp({
              duration: 500,
              easing: null
            });
            entry.removeClass('expanded').addClass('collapsed');
          }
        }
        return false;
      });

    });
  </script>
</head>

<body style=" background: #1c242d;">
  <nav class="navbar navbar-expand-lg navbar-light">
    &nbsp;
    <a class="w3-xxxlarge" href="../index.php"> <i class="fa fa-home"></i> </a>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
      <div class="box_"></div>
      <a class="w3-xxlarge" href="../admin/admin.php"><i class="fas fa-user-lock"></i> </a>&nbsp;&nbsp;
    </div>
  </nav>
  <div class="box">
    <?php
    require('../upload/upload.php');
    ?>
  </div>
  <div id="container"> </div>
  <div id="selected_file"></div>
</body>

</html>

<?php

// une classe de varaible pour manipuler plusieurs c'est vraible d'une fonction 
class MyVar
{
  public $result;
  public $rows;
  public $nb_page;
  public $limit_nbpage;
}

//cette fonction elle permettre de supprime un fichier dans la base de données
function delete($conn)
{
  if (isset($_GET['id_document'])) {
    $Id = $_GET['id_document'];
    $query_path = "SELECT path FROM documents WHERE id_document = $Id ";
    $rows = $conn->prepare($query_path);
    if ($rows->execute()) {
      $row = $rows->fetchAll();
      if (file_exists($row[0][0]))
        unlink($row[0][0]);
      $query_delete_join = "DELETE FROM join_documents_and_words WHERE id_document = $Id ";
      $delete_join = $conn->prepare($query_delete_join);
      $delete_join->execute();
      $query_delete_documents = "DELETE FROM documents WHERE id_document = $Id";
      $delete_documents =  $conn->prepare($query_delete_documents);
      $delete_documents->execute();
    }
    $_GET = NULL;
    header("Location: ./admin.php");
  }
}

// cette fonction elle permettre d'avoir la base de données
function getDatabase($conn, $Myvariable)
{
  //On select
  $query = 'SELECT * FROM documents';
  $rows = $conn->prepare($query);
  if ($rows->execute()) {
    $Myvariable->rows = $rows->fetchAll();
    if (empty($Myvariable->rows)) {
      debug_to_console('Myvariable->rows is empty');
    }
  }
}

// cette fonction elle permettre d'avoir les page 
function getPage()
{
  if (!isset($_GET['page'])) {
    $page = 1;
  } else {
    $page = $_GET['page'];
  }
  return $page;
}

// cette fonction elle permettre d'avoir la base de données avec les limite
function getDatabaseWithLimit($conn, $Myvariable, $Nb_Pas)
{
  $query_2 = " SELECT * FROM documents ORDER BY id_document DESC LIMIT " . $Myvariable->limit_nbpage . ',' . $Nb_Pas;
  $rows = $conn->prepare($query_2);
  if ($rows->execute()) {
    $Myvariable->rows = $rows->fetchAll();
    if (empty($Myvariable->rows)) {
      debug_to_console('Myvariable->rows is empty');
    }
  }
}

// cette fonction elle permettre d'afficher la base de données 
function dispalyDatabas($Myvariable)
{ ?>
  <div class="portfolioContent">
    <?php foreach ($Myvariable->rows as $index => $value) { ?>
      <div class="project">
        <h3 style="margin-top: 80px; font-size: 1.5rem;"><a href="<?= $value['path'] ?>" target="_blank"> <?php echo $value['title']; ?></a></h3>
        <h4 style="margin-top: 200px; font-size: 1.3rem;"><?php echo $value['description']; ?></h4>
        <h4 style="margin-top: 420px; font-size: 1.3rem;"><?php echo 'mots totale : ' . $value['totale_words'] . ' ==> mots retenus : ' . number_format(($value['retained_words'] * 100) / $value['totale_words'], 2, '.', ',') . '%' ?></h4>
        <a style="margin-top : 250px ; color: red; font-size: 2.2rem;" href="admin.php?id_document=<?php echo $value['id_document']; ?>" class="fa fa-trash"></a>
      </div>
    <?php } ?>
  </div>
<?php
}

// cette fonction elle permettre d'afficher les pages 
function DisplayPages($Myvariable, $page)
{ ?>
  <ul class="pagination">
    <?php if ($page > 1) { ?>
      <li class="prev">
        <a href="admin.php?page=<?= $page - 1 ?>" class="page-link">Précédente</a>
      </li>
    <?php
    }
    for ($pages = 1; $pages <= $Myvariable->nb_page; $pages++) { ?>
      <li class="page">
        <a href="admin.php?page=<?= $pages ?>" class="page-link"><?= $pages ?></a>
      </li>
    <?php } ?>

    <?php
    if ($page < $Myvariable->nb_page) { ?>
      <li class="next">
        <a href="admin.php?page=<?= $page + 1  ?>" class="page-link">Suivante</a>
      </li>
    <?php }  ?>
  </ul>
<?php
}

// main 
function main_admin()
{
  $Myvariable = new MyVar();
  $Nb_Pas = 6;
  $conn = configMysql();
  getDatabase($conn, $Myvariable);
  delete($conn);
  $page = getPage();
  $Myvariable->limit_nbpage = ($page - 1) * $Nb_Pas;

  $Myvariable->nb_page = ceil(count($Myvariable->rows) / $Nb_Pas);
  getDatabaseWithLimit($conn, $Myvariable, $Nb_Pas);

  dispalyDatabas($Myvariable);
  DisplayPages($Myvariable, $page);
}
main_admin();
?>