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

	// une classe de varaible pour manipuler plusieurs vraible d'une fonction 
	class My_Var
	{
		public $result;
		public $rows;
		public $file_name;
		public $file_tmp_name;
		public $file_size;
		public $file_type;
		public $file_path;
	}

	// cette function elle affiche le formulaire 	
	function displayform()
	{
		echo "
			<form style=' margin-top:0rem' align='center' method='post' enctype='multipart/form-data'>
				<input style='font-size: 20px;' type='file' name='file'>
				<input type='submit' value='Envoyer' class='box-button'>
			</form>";
	}

	// cette function elle récupèrer la donnée et le mettre dans un dossier ainsi dans la BDD
	function issetFile($Myvariable)
	{
		if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
			$iSok = array("txt" => "text/plain", "pdf" => "application/pdf", "html" => "text/html");
			$Myvariable->file_name = $_FILES['file']['name'];
			$Myvariable->file_tmp_name = $_FILES["file"]["tmp_name"];
			$Myvariable->file_size = $_FILES['file']['size'];
			$Myvariable->file_type = $_FILES['file']['type'];
			$Myvariable->file_path = "../Files/" . $_FILES['file']['name'];
			// Vérifie l'extension du fichier
			$extension = pathinfo($Myvariable->file_name, PATHINFO_EXTENSION);
			if (!array_key_exists($extension, $iSok)) {
				echo "<div style= 'margin-top:0.7rem; color:#A71113' align='center'> 
						<strong>Erreur : Veuillez sélectionner un format de fichier valide! </strong>		
					</div>";
			}
			$sizemax = 50 * 1024 * 1024;
			if ($Myvariable->file_size > $sizemax) {
				echo "<div style= 'margin-top:0.7rem; color:#A71113' align='center'> 
						<strong>Erreur: La taille du fichier ne doit pas dépassée $sizemax !</strong>
					</div>";
			}
			// Vérifie le type du fichier
			$dir = "Autre";
			if ($Myvariable->file_type === 'application/pdf') {
				$dir = 'FilesPdf';
			} elseif ($Myvariable->file_type === 'text/html') {
				$dir = 'FilesHtml';
			} elseif ($Myvariable->file_type === 'text/plain') {
				$dir = 'FilesTxt';
			}
			if (in_array($Myvariable->file_type, $iSok)) {
				// Vérifie si le fichier existe avant de le télécharger.
				if (file_exists("../Files/$dir/" . $_FILES["file"]["name"])) {
					echo "<div style= 'margin-top:0.7rem; color:#A71113' align='center'> 
							<strong>Ce fichier ", $_FILES["file"]["name"] . " existe déjà !</strong> 	
						</div>";
					header("refresh: 5");
				} else {
					move_uploaded_file($Myvariable->file_tmp_name, "../Files/$dir/" . $Myvariable->file_name);
					$conn =  configMysql();
					main($conn);
				}
			}
		}
	}

	// main 
	function _main_()
	{

		$Myvariable = new My_Var();
		displayform();
		issetFile($Myvariable);
	}
	_main_();
	?>
</body>

</html>