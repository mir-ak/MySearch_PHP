<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
	<link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

	<?php

	require('../indexation/indexation.php');
	require('../vendor/autoload.php');
	require('../database/saveData_in_dataBase.php');
	require('../database/config.php');

	// cette function elle affiche le formulaire 	
	function displayform()
	{
		echo "
			<form style=' margin-top:0rem' align='center' method='post' enctype='multipart/form-data'>
				<input 
					style='font-size: 20px;' type='file' name='files[]' id='files' 
					multiple directory='' webkitdirectory ='' mozdirectory='' 
				'>
				<input type='submit' value='Envoyer' class='box-button' name='Folder'>
			</form>";
	}

	// cette function elle récupèrer la donnée et le mettre dans un dossier ainsi dans la BDD
	function issetFile()
	{
		if (isset($_POST['Folder'])) {
			if (!is_dir('../Files')) {
				mkdir('../Files');
				chdir('../Files');
				mkdir('./FilesHtml');
				mkdir('./FilesPdf');
				mkdir('./FilesTxt');
			}
			foreach ($_FILES['files']['name'] as $i => $name) {
				$iSok = array("txt" => "text/plain", "pdf" => "application/pdf", "html" => "text/html", "htm" => "text/html");
				$extension = pathinfo($name, PATHINFO_EXTENSION);
				if (!array_key_exists($extension, $iSok)) {
					echo "<div style= 'margin-top:0.7rem; color:#A71113' align='center'> 
						<strong>Erreur : Veuillez sélectionner un format de fichier valide! </strong>		
					</div>";
				}
				$dir = "Autre";
				if ($_FILES['files']['type'][$i] === 'application/pdf') {
					$dir = 'FilesPdf';
				} elseif ($_FILES['files']['type'][$i] === 'text/html') {
					$dir = 'FilesHtml';
				} elseif ($_FILES['files']['type'][$i] === 'text/plain') {
					$dir = 'FilesTxt';
				}
				if (file_exists("../Files/$dir/" . $name)) {
					echo "<div style= 'margin-top:0.7rem; color:#A71113' align='center'> 
							<strong>Ce fichier ", $name . " existe déjà !</strong> 	
						</div>";
					//header("refresh: 5");
				} else {
					move_uploaded_file($_FILES["files"]["tmp_name"][$i], "../Files/$dir/" . $name);
					$conn =  configMysql();
					main($conn);
				}
			}
		}
	}

	// main 
	function _main_()
	{
		displayform();
		issetFile();
	}
	_main_();
	?>
</body>

</html>