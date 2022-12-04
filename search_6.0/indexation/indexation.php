<?php
$removeAccents = [
    ["&agrave;", "&acirc;", "&eacute;", "&egrave;", "&ecirc;", "&icirc;", "&iuml;", "&oelig;", "&ugrave;", "&ucirc;", "&ccedil;", "&Agrave;", "&Acirc;", "&Eacute;", "&Egrave;", "&Ecirc;", "&Icirc;", "&Iuml;", "&OElig;", "&Ugrave;", "&Ucirc;", "&Ccedil;"], ["à", "â", "é", "è", "ê", "î", "ï", "œ", "ù", "û", "ç", "À", "Â", "È", "É", "Ê", "Î", "Ï", "Œ", "Ù", "Û", "Ç",]
];

function debug_to_console($data, $context = 'Debug in Console')
{
    // Mise en mémoire tampon pour résoudre les problèmes des frameworks, comme header() dans ce et pas un retour solide.
    ob_start();
    $output  = 'console.info(\'' . $context . ':\');';
    $output .= 'console.log(' . json_encode($data) . ');';
    $output  = sprintf('<script>%s</script>', $output);
    echo $output;
}

/*
 * Cette méthode renvoie la liste des chemins des fichiers html contenus dans un répertoire $main_directory
 */
function get_files_paths($name)
{
    $files = [];
    if ($dir = opendir($name)) {
        while ($file = readdir($dir)) {
            $full_path = $name . "/" . $file;
            if (is_directory($file, $full_path)) {
                $files = array_merge($files, get_files_paths($full_path));
            }
            if (is_html_file_and_txt($full_path)) {
                $files[] = $full_path;
            }
        }
        closedir($dir);
    }
    return $files;
}

function is_html_file_and_txt($file)
{
    $info = pathinfo($file);
    return array_key_exists("extension", $info) && ($info["extension"] == "html" || $info["extension"] == "htm" || $info["extension"] == "txt" || $info["extension"] == "pdf");
}

function is_directory($file, $full_path)
{
    return $file != "." && $file != ".." && is_dir($full_path);
}

/*
 * Cette méthode index multiple fichiers html
 */
function index_multiple_files($path_directory, $array_empty_words, $séparateurs)
{
    $file_list = get_files_paths($path_directory);

    $files = [];
    foreach ($file_list as $index => $file_path) {
        $files[] = index_file($file_path, $array_empty_words, $séparateurs);
    }
    return $files;
}

/*
 * Cette méthode crée un objet document
 */
function convert_array_to_doc($title, $path, $weights, $description, $keywords, $totale_words, $retained_words)
{
    $document = (object)[];
    $document->title = $title;
    $document->path = $path;
    $document->word_and_weight = $weights;
    $document->description = $description;
    $document->keywords = $keywords;
    $document->totale_words = $totale_words;
    $document->retained_words = $retained_words;

    return $document;
}

/*
 * Cette méthode index un fichier html
 */
function index_file($file_path, $array_empty_words, $separator)
{
    # lire le fichier html
    $word = file_get_contents($file_path);
    $word = str_replace("&nbsp;", '', $word);
    $word = str_replace("&ucirc;", 'u', $word);
    $word = str_replace("&eacute;", 'e', $word);
    $word = str_replace("&ecirc;", 'ê', $word);
    $word = str_replace("&ccedil;", 'ç', $word);
    $word = str_replace("&laquo;", '«', $word);
    $word = str_replace("&raquo;", '»', $word);
    $word = str_replace("&egrave;", 'è', $word);
    $word = str_replace("&agrave;", 'à', $word);
    $word = str_replace("&icirc;", 'î', $word);
    $word = str_replace("&iuml;", 'ï', $word);
    $word = str_replace("&oelig;", 'œ', $word);
    $word = str_replace("&ugrave;", 'ù', $word);
    $word = str_replace("&ucirc;", 'û', $word);
    $word = str_replace("&gt;", '>', $word);
    $word = str_replace("&copy;", ' © ', $word);
    $word = str_replace("\t", '', $word);
    $word = utf8_decode($word);
    $word = strtolower($word);

    $info = pathinfo($file_path);
    if ($info["extension"] == "html" || $info["extension"] == "htm") {
        # tokéniser le fichier html
        $doc = explode_html_file($file_path, $word, $separator, $array_empty_words);
        $doc->title = get_title($word);
        $doc->description = get_metas_description_and_keywords($file_path, 'description');
        $doc->keywords = get_metas_description_and_keywords($file_path, 'keywords');
    } elseif ($info["extension"] == "txt") {
        $doc = explode_txt_file($word, $separator, $array_empty_words);
    } elseif ($info["extension"] == "pdf") {
        $doc = explode_pdf_file($file_path, $array_empty_words);
    }

    return convert_array_to_doc(
        $doc->title,
        $file_path,
        $doc->occurrences,
        $doc->description,
        $doc->keywords,
        $doc->totale_words,
        $doc->retained_words
    );
}

/*
 * Cette méthode qui analyse un fichier txt
 * retourner un tableau avec les mots et leur nombre d’occurrences
 */
function explode_txt_file($text, $separator, $array_empty_words)
{
    $doc = (object)[];
    $text = utf8_encode($text);
    $title = explode(' ', $text, 6);
    unset($title[5]);
    $title_ = implode(' ', $title);
    $array_word = explode_text_file($separator, $array_empty_words, $text);
    $occurrences_word = array_count_values($array_word[0]);
    debug_to_console($occurrences_word);
    $doc->title = $title_;
    $doc->description = '';
    $doc->occurrences = $occurrences_word;
    $doc->keywords = '';
    $doc->totale_words = count($array_word[1]);
    $doc->retained_words = count($array_word[0]);
    return $doc;
}

/*
 * Cette méthode qui analyse un fichier PDF
 * retourner un tableau avec les mots et leur nombre d’occurrences
 */
function explode_pdf_file($path_file, $array_empty_words)
{
    $doc = (object)[];
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($path_file);
    $text = $pdf->getText();
    #title
    $title = explode(' ',  $text, 7);
    unset($title[6]);
    $title_ = implode(' ', $title);
    $new_string = preg_replace('/[\"=-_|+^}{:;,!?.<>()«»0123456789&#\n]/', ' ', $text);
    $new_string = str_replace("’", " ", $new_string);
    $new_string = str_replace("'", " ", $new_string);
    $new_string = str_replace("•", "", $new_string);
    $new_string = str_replace("◦", "", $new_string);
    $new_string = str_replace("\u001", " f", $new_string);

    $array_words = explode(" ", $new_string);
    debug_to_console($array_words);
    $tab_words = [];
    foreach ($array_words as $key => $value) {
        if (strlen($value) >= 3 && !in_array($value, $array_empty_words)) {
            $tab_words[] = $value;
        }
    }
    $occurrences_word = array_count_values($tab_words);
    debug_to_console($occurrences_word);
    $doc->title = $title_;
    $doc->description = '';
    $doc->occurrences = $occurrences_word;
    $doc->keywords = '';
    $doc->totale_words = count($array_words);
    $doc->retained_words = count($tab_words);

    return $doc;
}

/*
 * Cette méthode qui analyse un fichier html
 * retourner un tableau avec les mots et leur nombre d’occurrences
 */
function explode_html_file($path_file, $text, $separator, $array_empty_words)
{
    $doc = (object)[];
    //<head>
    $text = utf8_encode($text);
    $head = get_html_head($text, $path_file);
    $array_word_head = explode_text_file($separator, $array_empty_words, $head);
    $occurrences_head_word = array_count_values($array_word_head[0]);
    // <body>
    $body = get_html_body($text);
    $tab_word_body = explode_text_file($separator, $array_empty_words, $body);
    $occurrences_body_word = array_count_values($tab_word_body[0]);
    // count occurrences
    $doc->occurrences = sum_weight_head_and_body($occurrences_head_word, $occurrences_body_word, 1.5);
    $doc->totale_words = count($array_word_head[1]) + count($tab_word_body[1]);
    $doc->retained_words = count($array_word_head[0]) + count($tab_word_body[0]);
    return $doc;
}


/*
 * Cette méthode qui calcule la somme des occurrences cote head et body
 * retourner un tableau de nombre d’occurrence
 */
function sum_weight_head_and_body($occurrences_head_word, $occurrences_body_word, $coef)
{

    $sum_weight_word = [];
    if ($occurrences_head_word != null)
        foreach ($occurrences_head_word as $word => $value) {
            $sum_weight_word[$word] = $coef * $value;
        }

    foreach ($occurrences_body_word as $word => $value) {
        if (array_key_exists($word, $sum_weight_word) == true)
            $sum_weight_word[$word] += $value;
        else
            $sum_weight_word[$word] = $value;
    }
    return $sum_weight_word;
}

/*
 * Cette méthode prend un texte normal et renvoie un tableau de tous les mots
 * nous analysons avec des séparateurs et supprimons les mots vides
 */
function explode_text_file($separator, $array_empty_words, $text)
{
    $text = strtolower($text);
    $tab_words = [];
    $word_tok = strtok($text, $separator);
    $countWord[] = $word_tok;
    if (strlen($word_tok) >= 3 && !in_array($word_tok, $array_empty_words)) {
        $tab_words[] = $word_tok;
    }

    while ($word_tok) {
        $countWord[] = $word_tok;
        $word_tok = strtok($separator);
        if (strlen($word_tok) >= 3 && !in_array($word_tok, $array_empty_words)) {
            $tab_words[] = $word_tok;
        }
    }
    return [$tab_words, $countWord];
}


/*
 * Cette méthode permet de recueillir des titres et des méta-informations dans le head
 */
function get_html_head($text, $path_file)
{
    $title = get_title($text);
    $metas = get_meta_tags($path_file);
    $description = array_key_exists('description', $metas) ? $metas['description'] : '';
    $keywords =  array_key_exists('keywords', $metas) ? $metas['keywords'] : '';

    return html_entity_decode(utf8_encode(implode(' ', [
        $title,
        $description,
        $keywords

    ])));
}

/*
 * Cette méthode rassemble tous les éléments nécessaires du <body>
 * return html_entity_decode()
 */
function get_html_body($text)
{
    $text_body = get_tag_html($text, 'body');
    $text_body = strip_scripts($text_body);
    $text_body = strip_tags($text_body);
    return html_entity_decode($text_body);
}

/*
 * Cette méthode permet d'obtenir une balise html
 */
function get_tag_html($text, $tag)
{
    return preg_match(pattern_tags_html($tag), $text, $matches) ? $matches[1] : ' ';
}

/*
 * Cette méthode permet d'obtenir le titre
 */
function get_title($text)
{
    return get_tag_html($text, 'title');
}

/*
 * Cette méthode permet d'obtenir un motifs d'une balise html
 */
function pattern_tags_html($tag)
{
    return "/<$tag.*?>(.*?)<\/$tag>/is";
}

/*
 * Cette méthode permet de supprimer les balise <script>
 */
function strip_scripts($text)
{
    return preg_replace(pattern_tags_html('script'), ' ', $text);
}

/*
 * Cette méthode rassemble tous les éléments nécessaires du description et keywords
 */
function get_metas_description_and_keywords($path_file, $name)
{
    $metas = get_meta_tags($path_file);
    return html_entity_decode(utf8_encode(implode(' ', [
        array_key_exists($name, $metas) ? $metas[$name] : '',
    ])));
}

/*
 * Cette méthode obtient un tableau de mots vides
 */
function get_array_empty_words($path_file_empty_words)
{
    return explode("\n", file_get_contents($path_file_empty_words));
}

/*
 * Cette méthode obtient les séparateurs
 */
function get_array_separator_words($path_file_empty_words)
{
    return file_get_contents($path_file_empty_words);
}

function main($conn)
{
    # path de fichier des mots vides 
    $path_file_empty_words = '../data_configuration/empty_words.txt';

    # path de fichier de séparateur
    $path_file_separator_word = "../data_configuration/sep.txt";

    # lire la liste des mots vides 
    $array_empty_words = get_array_empty_words($path_file_empty_words);

    # lire le fichier de séparateur 
    $separator = get_array_separator_words($path_file_separator_word);

    $FILES = index_multiple_files('../Files', $array_empty_words, $separator);
    save_files_in_database($conn, $FILES);
}
