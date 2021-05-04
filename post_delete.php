<?php
require "database.php";

$post_id = "";
$return_url = "";

$url = $_SERVER["REQUEST_URI"];
$parsed_url = parse_url($url, PHP_URL_QUERY);
$array = array();
parse_str(html_entity_decode($parsed_url), $array);
$_GET = $array;

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
} else {
    die("You need to provide the post ID to this script");
}

if (isset($_GET['return_url'])) {
    $return_url = $_GET['return_url'];
} else {
    die("You need to provide a return url to this script");
}

function processDelete() {
    global $post_id;
    global $return_url;
    global $conn;

    $delete_post_query = mysqli_query($conn, "delete from posts where post_id='" . $post_id . "';");

    header("Location: ".$return_url);
}


processDelete();
?>