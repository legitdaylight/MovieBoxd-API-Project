<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

$title=$_GET["title"];
$num=$_GET["filter"];
$params = [];

if(!$num)
{
    $num = 10;
}

if($num < 1 || $num > 100)
{
    flash("[PHP] Filter has to be between 1 and 100", "warning");
    $num = 10;
}

if(!$title)
{
    $query = "SELECT Movies.id, Movies.title, Movies.image_url, Movies.release_date FROM Movies LEFT JOIN UserMovies ON Movies.id = UserMovies.movie_id WHERE UserMovies.movie_id IS NULL ORDER BY Movies.created DESC LIMIT $num";
}
else
{
    if(strlen($title) > 200)
    {
        flash("[PHP] Title too long (cannot exceed 200 characters) ", "warning");
        $query = "SELECT Movies.id, Movies.title, Movies.image_url, Movies.release_date FROM Movies LEFT JOIN UserMovies ON Movies.id = UserMovies.movie_id WHERE UserMovies.movie_id IS NULL ORDER BY Movies.created DESC LIMIT $num";
    }
    else
    {
        $search = se($_GET, "title", "", false);
        $query = "SELECT Movies.id, Movies.title, Movies.image_url, Movies.release_date FROM Movies LEFT JOIN UserMovies ON Movies.id = UserMovies.movie_id WHERE UserMovies.movie_id IS NULL AND Movies.title LIKE :title ORDER BY Movies.created DESC LIMIT $num";
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
    $numMovies = count($results);
} catch (PDOException $e) 
{
    error_log("Error fetching movies " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$filterData = ["title" => $title, "filter"=>$num];
$deleteURL = "admin/delete_movie.php" . "?" . http_build_query($filterData);
$table = ["data" => $results, "title" => "Latest Movies", "view_url" => get_url("view_movie.php")];
?>
<div class="container-fluid">
    <h3 class = "text-center">Movies That Have Not Been Watch Listed</h3>
    <h4 class="text-center" >Number of Movies: 
        <?php 
            try {
                $stmt = $db->prepare("SELECT COUNT(Movies.id) FROM Movies LEFT JOIN UserMovies ON Movies.id = UserMovies.movie_id WHERE UserMovies.movie_id IS NULL");
                $stmt->execute();
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($r) 
                {
                    $movieNum = $r[0]["COUNT(Movies.id)"];
                    echo $movieNum;
                }
            } catch (PDOException $e) {
                flash("Error retrieving number of movies in watch list", "danger");
                error_log("Error removing watch: " . var_export($e, true));
            }
        ?>
    </h4>
    <h5 class="text-center">
        Movies On Page: 

        <?php echo $numMovies; ?>
    </h5>
    <form onsubmit="return validate(this)" method="GET">
        <?php render_input(["type" => "search", "name" => "title", "placeholder" => "Movie Title", "value"=>$title]); ?>
        <?php render_input(["type" => "number", "name" => "filter", "placeholder" => "Number of Records", "value"=>$num]); ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <?php render_table($table); ?>
</div>
<script>
    function validate(form)
    {
        let title = form.title.value;
        let filter = form.filter.value;
        let isValid = true;

        if(title.length > 200)
        {
            flash("[JavaScript] Title too long (cannot exceed 200 characters) ", "warning");
            isValid = false;
        }

        if(filter < 0 || filter > 100)
        {
            flash("[JavaScript] Filter has to be between 1 and 100", "warning");
            isValid = false;
        }

        return isValid;
    }
</script>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>