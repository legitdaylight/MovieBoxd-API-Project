<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "Project/home.php"));
}

$id = se($_GET, "assoc_id", -1, false);

if(isset($_GET["title"]) && isset($_GET["amp;filter"]) && isset($_GET["amp;user"]))
{
    $listData = ["title"=>$_GET["title"], "user"=>$_GET["amp;user"], "filter"=>$_GET["amp;filter"]];
    $listURL = "admin/list_assoc.php" . "?" . http_build_query($listData);
}
else
{
    $listURL = "admin/list_assoc.php?title=&user=&filter=";
}

if ($id > -1)
{
    $db = getDB();
    $query = "DELETE FROM `UserMovies` WHERE id = :id";
    try 
    {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        flash("Sucessfully deleted association.", "success");
    } 
    catch (Exception $e) 
    {
        flash("Error: Could not delete association.", "danger");
    }
    die(header("Location:" . get_url($listURL)));
}
else
{
    flash("Invalid id passed $id", "danger");
    die(header("Location:" . get_url($listURL)));
}

?>