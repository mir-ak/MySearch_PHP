<?php

function  get_select_databese_with_condition($table, $modeSelect, $where)
{
    return "SELECT $modeSelect FROM $table WHERE $where;";
}

function get_item_database($data, $param)
{
    $dataSet = [];
    foreach ($data as $index => $value) {
        array_push($dataSet, $value[$param]);
    }
    return $dataSet;
}

function set_insert_to_database($table, $columns, $values)
{
    return "INSERT INTO $table ($columns) VALUES $values;";
}

function get_database($conn, $path, $modeSelect, $value)
{
    $data = get_select_databese_with_condition("$path", $modeSelect, $value);
    $result = $conn->prepare($data);
    return $result;
}

function get_diff_array($words, $existing_words)
{
    return array_filter($words, function ($item) use ($existing_words) {
        return !in_array($item, $existing_words);
    });
}

function split_word($string)
{
    $word = preg_split('~~u', $string, -1, PREG_SPLIT_NO_EMPTY);
    return $word;
}

function get_database_word($words)
{
    $tab = [];
    foreach ($words as $index => $value) {
        $split = split_word($value);
        asort($split);
        array_push($tab, "(\"$value\", \"" . implode('', $split) . "\")");
    }
    return implode(', ', $tab);
}

function get_keys_in_string_format($words)
{
    $tab = [];
    foreach ($words as $index => $value) {
        array_push($tab, "'$value'");
    }
    return implode(',', $tab);
}


function add_description($words)
{
    $output = array_slice($words, 0, 20);
    return implode(" ", $output);
}

function get_item_words($data, $words)
{
    $wrd = [];
    foreach ($data as $index => $value) {
        if (in_array($value['word'], $words)) {
            $wrd[$value['id_word']] = $value['word'];
        }
    }
    return $wrd;
}

function save_words_in_database($conn, $words)
{
    $formatString = get_keys_in_string_format($words);
    //echo $formatString . '<br>';
    $result = get_database($conn, "words", '*', "word IN ($formatString)");
    if ($result->execute()) {
        $rows = $result->fetchAll();
        $database = get_item_database($rows, 'word');
        $not_exist_words = get_diff_array($words, $database);
        if (!empty($not_exist_words)) {
            $insert_data = set_insert_to_database("words", "word, word_order", get_database_word($not_exist_words));
            $result1 = $conn->prepare($insert_data);
            if (!$result1->execute()) {
                echo $insert_data;
            }
            if ($result->execute()) {
                return get_item_words($result->fetchAll(), $words);
            }
        }
    } else {
        $result->error_log();
    }
}

function save_documents_in_database($conn, $title, $path, $description, $words)
{
    $description = $description == '' ? add_description($words) : $description;

    $result = get_database($conn, "documents", '*', "title = \"$title\" AND path = \"$path\" AND description = \"$description\" limit 1");
    if ($result->execute()) {
        $rows = $result->fetchAll();
        $database = get_item_database($rows, 'path');
        if (!in_array($path, $database)) {
            $insert_data = set_insert_to_database("documents", "title, path, description", "(\"$title\",\"$path\",\"$description\")");
            $result1 = $conn->prepare($insert_data);
            if (!$result1->execute()) {
                echo $insert_data;
            }
            if ($result->execute()) {
                return $result->fetch()['id_document'];
            }
        }
    }
}

function get_id_words_and_weights_and_id_documenst($id_words, $weights, $id_documenst)
{
    $tab = [];
    foreach ($id_words as $id => $value) {
        array_push($tab, "($id, $id_documenst, $weights[$value])");
    }
    return implode(', ', $tab);
}

function save_join_documents_and_words_in_database($conn, $data_words, $data_documents, $words)
{
    if (!empty($data_words) && !empty($data_documents)) {
        $insert_data = set_insert_to_database("join_documents_and_words", "id_word, id_document, weight", get_id_words_and_weights_and_id_documenst($data_words, $words, $data_documents));
        $result = $conn->prepare($insert_data);
        if (!$result->execute()) {
            debug_to_console($result);
        }
    }
}

function save_files_in_database($conn, $docs)
{
    foreach ($docs as $index => $doc) {
        $words = $doc->word_and_weight;
        if (!empty(count($words))) {
            $title = $doc->title;
            $path = $doc->path;
            $description = $doc->description;
            $data_words = save_words_in_database($conn, array_keys($words));
            $data_documents = save_documents_in_database($conn, $title, $path, $description, array_keys($words));
            save_join_documents_and_words_in_database($conn, $data_words, $data_documents, $words);
        }
    }
}
