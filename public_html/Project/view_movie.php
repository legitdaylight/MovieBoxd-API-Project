<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../partials/nav.php");
?>

<?php
    $id = se($_GET, "id", -1, false);

    $movie = [];
    if ($id > -1) 
    {
        //fetch
        $db = getDB();
        $query = "SELECT id, title, image_url, release_date, caption FROM `Movies` WHERE id = :id";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute([":id" => $id]);
            $r = $stmt->fetch();
            if ($r) {
                $movie = $r;
            }
        } catch (PDOException $e) {
            error_log("Error fetching record: " . var_export($e, true));
            flash("Error fetching record", "danger");
        }
    } 
    else 
    {
        flash("Invalid id passed", "danger");
        die(header("Location:" . get_url("home.php")));
    }

    //$table = ["data"=> $movie, "title" => "Options", "edit_url" => get_url("admin/edit_movie.php"), "delete_url" => get_url("admin/delete_movie.php")];
?>

<div>
    <div class="d-flex flex-column align-items-center mx-auto">
        <h3><?php echo $movie['title']; ?></h3>
        <img src="<?php echo $movie['image_url']; ?>" alt="Movie Poster" width="400" height="400" >
        <p class="w-50 text-center"><?php echo $movie['caption']; ?></p>
        <p>Release Date: <?php echo $movie['release_date']; ?></p>
    </div>
    <?php if (has_role("Admin")) : ?>
        <div class="d-flex justify-content-center">
            <a href="admin/edit_movie.php?id=<?php echo $movie['id']; ?>" class="btn btn-secondary mx-2">Edit</a>
            <a href="admin/delete_movie.php?id=<?php echo $movie['id']; ?>" class="btn btn-danger mx-2">Delete</a>
        </div>
    <?php endif; ?>
</div>
