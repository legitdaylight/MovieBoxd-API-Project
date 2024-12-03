<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
$id = se($_GET, "id", -1, false);
//TODO handle stock fetch
if (isset($_POST["title"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["title", "image_url", "caption", "release_date"])) 
        {
            unset($_POST[$k]);
        }
        $quote = $_POST;
        error_log("Cleaned up POST: " . var_export($quote, true));
    }
    //insert data
    $db = getDB();
    $query = "UPDATE `Movies` SET ";

    $params = [];
    $isValid = true;
    //per record
    foreach ($quote as $k => $v) 
    {
        if($k == "title")
        {
            if(strlen($v) > 200)
            {
                flash("[PHP] Title too long (cannot exceed 200 characters)", "warning");
                $isValid = false;
            }
        }

        if($k == "release_date")
        {
            if(strlen($v) == 0)
            {
                flash("[PHP] You must provide a date", "warning");
                $isValid = false;
            }
            else if(!preg_match('/^\d{4}\-(0?[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/', $v))
            {
                flash("[PHP] Invalid date", "warning");
                $isValid = false;
            }
        }

        if($k == "caption")
        {
            if(strlen($v) > 500)
            {
                flash("[PHP] Caption too long. (cannot exceed 500 characters)", "warning");
                $isValid = false;
            }
        }

        if ($params) 
        {
            $query .= ",";
        }
        //be sure $k is trusted as this is a source of sql injection
        $query .= "$k=:$k";
        $params[":$k"] = $v;
    }

    if($isValid)
    {
        $query .= " WHERE id = :id";
        $params[":id"] = $id;
        error_log("Query: " . $query);
        error_log("Params: " . var_export($params, true));
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Sucessfully updated movie! ", "success");
        } catch (PDOException $e) {
            movie_check_duplicate($e->errorInfo);
        }
    }
}

$movie = [];
if ($id > -1) 
{
    //fetch
    $db = getDB();
    $query = "SELECT title, image_url, caption, release_date FROM `Movies` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $movie = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching movie", "danger");
    }
} 
else 
{
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_movies.php")));
}


$form = [
    ["type" => "text", "name" => "title", "placeholder" => "Movie Title", "label" => "Movie Title", "rules" => ["required" => "required"]],
    ["type" => "text", "name" => "image_url", "placeholder" => "Image URL", "label" => "Image URL"],
    ["type" => "text", "name" => "caption", "placeholder" => "Movie Caption", "label" => "Movie Caption"],
    ["type" => "text", "name" => "release_date", "placeholder" => "Release Date (YYYY-MM-DD)", "label" => "Release Date", "rules" => ["required" => "required"]],
    

];
$keys = array_keys($movie);

foreach ($form as $k => $v) {
    if (in_array($v["name"], $keys)) {
        $form[$k]["value"] = $movie[$v["name"]];
    }
}

//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Edit Movie</h3>
    <form onsubmit="return validate(this)" method="POST">
        <?php foreach ($form as $k => $v) {

            render_input($v);
        } ?>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
    </form>

</div>
<script>
    //jd755 11/21/24
    function validate(form)
    {
        let title = form.title.value;
        let release_date = form.release_date.value;
        let caption = form.caption.value;
        const regex = /^\d{4}\-(0?[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/;
        let isValid = true;

        //check for valid title
        if(title.length > 200)
        {
            flash("[JavaScript] Title too long (cannot exceed 200 characters)", "warning");
            isValid = false;
        }

        if(title.length == 0)
        {
            flash("[JavaScript] You must enter a title", "warning");
            isValid = false;
        }

        //check for valid date
        if(release_date.length == 0)
        {
            flash("[JavaScript] You must enter a release date", "warning");
            isValid = false;
        }
        else
        {
            if(!regex.test(release_date))
            {
                flash("[JavaScript] Invalid date. Use YYYY-MM-DD", "warning");
                isValid = false;
            }
        }

        if(caption.length > 500)
        {
            flash("[JavaScript] Caption too long. (cannot exceed 500 characters)", "warning");
            isValid = false;
        }
        else if(caption.length == 0)
        {
            form.caption.value = "No information is available for this movie.";
        }

        return isValid;
    }
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>