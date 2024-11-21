<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

if (isset($_POST["filter"])) 
{
    $num = $_POST["filter"];
    if(empty($num))
    {
        $num = 10; // default value
    }
    else if ($num > 101 || $num < 1)
    {
        flash("Filter has to be between 1 and 100", "warning");
        $num = 10;
    } 
}
else
{
    $num = 10;
}

$query = "SELECT id, title, image_url, is_api FROM `Movies` ORDER BY created DESC LIMIT $num";
$params = null;

if (isset($_POST["title"])) 
{
    if(strlen($_POST["title"]) > 200)
    {
        flash("[PHP] Title too long (cannot exceed 200 characters) ", "warning");
    }
    else
    {
        $search = se($_POST, "title", "", false);
        $query = "SELECT id, title, image_url, is_api FROM `Movies` WHERE title LIKE :title ORDER BY created DESC LIMIT $num";
        $params =  [":title" => "%$search%"];
    }
}



$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) 
    {
        $results = $r;
    }
} catch (PDOException $e) 
{
    error_log("Error fetching movies " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = ["data" => $results, "title" => "Latest Movies", "view_url" => get_url("view_movie.php"), "edit_url" => get_url("admin/edit_movie.php"), "delete_url" => get_url("admin/delete_movie.php?origin=list_movies.php")];
?>
<div class="container-fluid">
    <h3>List Movies</h3>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "title", "placeholder" => "Movie Title"]); ?>
        <?php render_input(["type" => "number", "name" => "filter", "placeholder" => "Number of Records"]); ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <?php render_table($table); ?>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>