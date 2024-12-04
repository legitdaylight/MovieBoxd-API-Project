<?php
session_start();
require(__DIR__ . "/../../../lib/functions.php");

if(isset($_GET["title"]) && $_GET["filter"])
{
    $listData = ["title"=>$_GET["title"], "filter"=>$_GET["filter"]];
    $searchURL = "search_movie.php" . "?" . http_build_query($listData);
}
else
{
    $searchURL = "search_movie.php?title=&filter=";
}

if (isset($_POST["toggleWatched"])) {
    $movieId = se($_POST, "movieId", -1, false);
    $userId = get_user_id();
    if ($userId) {
        $db = getDB();
        $params = [":movie_id" => $movieId, ":user_id" => $userId];
        $needsDelete = false;
        try {
            $stmt = $db->prepare("INSERT INTO UserMovies(movie_id, user_id)
            VALUES (:movie_id, :user_id)");
            $stmt->execute($params);
            flash("Added to watch list", "success");
        } catch (PDOException $e) {
            // use duplicate error as a delete trigger
            if ($e->errorInfo[1] == 1062) {
                $needsDelete = true;
            } else {
                flash("Error adding item to watch list", "danger");
                error_log("Error adding watch: " . var_export($e, true));
            }
        }
        if ($needsDelete) {
            try {
                $stmt = $db->prepare("DELETE FROM UserMovies WHERE movie_id = :movie_id AND user_id = :user_id");
                $stmt->execute($params);
                flash("Removed from watch list", "success");
            } catch (PDOException $e) {
                flash("Error removing item from watch list", "danger");
                error_log("Error removing watch: " . var_export($e, true));
            }
        }
    } else {
        flash("You must be logged in to do this action", "warning");
    }
    die(header("Location: " . get_url($searchURL)));
}
flash("Error toggling watched", "danger");
die(header("Location: " . get_url("home.php")));