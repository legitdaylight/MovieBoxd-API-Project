<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "Project/home.php"));
}

$id = se($_GET, "id", -1, false);

if(isset($_GET["title"]) && $_GET["amp;filter"])
{
    $listData = ["title"=>$_GET["title"], "filter"=>$_GET["amp;filter"]];
    $listURL = "admin/list_movies.php" . "?" . http_build_query($listData);
}
else
{
    $listURL = "admin/list_movies.php?title=&filter=";
}

if ($id > -1)
{
    $db = getDB();
    $queryAssoc = "DELETE FROM UserMovies WHERE movie_id = :id";
    try 
    {
        $stmt = $db->prepare($queryAssoc);
        $stmt->execute([":id" => $id]);
    } 
    catch (Exception $e) 
    {
        flash("Error: Could not delete association: Movieid: $id", "danger");
    }

    $db = getDB();
    $queryMovie = "DELETE FROM Movies WHERE id = :id";
    try 
    {
        $stmt = $db->prepare($queryMovie);
        $stmt->execute([":id" => $id]);
        flash("Sucessfully deleted movie.", "success");
    } 
    catch (Exception $e) 
    {
        flash("Error: Could not delete association: Movieid: $id", "danger");
    }

    die(header("Location:" . get_url($listURL)));
}
else
{
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url($listURL)));
}

?>