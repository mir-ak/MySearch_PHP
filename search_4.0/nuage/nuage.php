<?php

//Liste de mots/poids
//$mots['développement']=5;

//Fonction pour générer le cloud à partir des données fournies
function genererNuage($data = array(), $minFontSize = 16, $maxFontSize = 41)
{
    $tab_colors = initialiser_colors();
    $minimumCount = min(array_values($data));
    $maximumCount = max(array_values($data));
    $spread = $maximumCount - $minimumCount;
    $cloudTags = array();

    $spread == 0 && $spread = 1;
    //Mélanger un tableau de manière aléatoire
    srand((float)microtime() * 1000);

    $mots = array_keys($data);
    shuffle($mots);

    foreach ($mots as $tag) {

        $count = $data[$tag];
        //La couleur aléatoire
        $color = $tab_colors[$count];
        $size = $minFontSize + ($count - $minimumCount) * ($maxFontSize - $minFontSize) / $spread;
        if ($count > 0) $cloudTags[] = '<a style="font-size: ' .
            floor($size) .
            'px' . '; text-decoration: none' .
            '; color:' . $color .
            '; " title="Rechercher le $tag ' .
            $tag . '" href="MySearch?words=' . $tag .
            '">' . $tag . '</a>';
    }
    return join("\n", $cloudTags) . "\n";
}

function initialiser_colors()
{
    $tab_colors = [];
    for ($i = 0; $i < 100;) {
        $coul = RandomCouleur();
        if (!in_array($coul, $tab_colors)) {
            $tab_colors[$i] = '#' . RandomCouleur();
            $i++;
        };
    }
    return $tab_colors;
}

function RandomCouleur()
{
    $color = dechex(mt_rand(0, 1000000));
    $color = str_pad($color, 6, '0');
    return $color;
}






function search_with_genererNuage($conn, $id)
{
    $words_and_weights = [];
    if (!empty($id)) {
        $query = "SELECT * FROM join_documents_and_words WHERE join_documents_and_words.id_document = \"$id\";";
        $result = $conn->prepare($query);
        if ($result->execute()) {
            $join_document_words = $result->fetchAll();
            $words_and_weights =  get_words_weights($conn, $join_document_words);
        } else {
            debug_to_console($query);
        }
    }
    return $words_and_weights;
}

function convert_array_words_to_strnig($doc)
{
    $tab = [];
    foreach ($doc as $index => $value) {
        array_push($tab, strval($value["id_word"]));
    }
    return implode(',', $tab);
}

function get_words_weights($conn, $join_documents_and_words)
{
    $id_word = convert_array_words_to_strnig($join_documents_and_words);
    $words_and_weights = [];
    $query = "SELECT * FROM words WHERE id_word IN ($id_word)";
    $result = $conn->prepare($query);
    if ($result->execute()) {
        $words = $result->fetchAll();
        foreach ($words as $index => $value) {
            $words_and_weights[$value["word"]] = $join_documents_and_words[array_search($value["id_word"], array_column($join_documents_and_words, 'id_word'))]["weight"];
        }
    } else {
        debug_to_console($query);
    }
    return $words_and_weights;
}
