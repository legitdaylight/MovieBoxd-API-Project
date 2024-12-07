<?php
require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$title=$_GET["title"];
$num=$_GET["filter"];
$params = [];

$params[":user_id"] = get_user_id();

$assoc_check = ", (SELECT IFNULL(count(1), 0) FROM UserMovies WHERE user_id = :user_id and movie_id = Movies.id LIMIT 1) as is_watched";

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
    $query = "SELECT Movies.id, Movies.title, Movies.image_url, Movies.release_date $assoc_check FROM Movies INNER JOIN UserMovies ON Movies.ID = UserMovies.movie_id WHERE UserMovies.user_id = :user_id ORDER BY Movies.created DESC LIMIT $num";
}
else
{
    if(strlen($title) > 200)
    {
        flash("[PHP] Title too long (cannot exceed 200 characters) ", "warning");
        $query = "SELECT Movies.id, Movies.title, Movies.image_url, Movies.release_date $assoc_check FROM Movies INNER JOIN UserMovies ON Movies.ID = UserMovies.movie_id WHERE UserMovies.user_id = :user_id ORDER BY Movies.created DESC LIMIT $num";
    }
    else
    {
        $search = se($_GET, "title", "", false);
        $query = "SELECT Movies.id, Movies.title, Movies.image_url, Movies.release_date $assoc_check FROM Movies INNER JOIN UserMovies ON Movies.ID = UserMovies.movie_id WHERE UserMovies.user_id = :user_id AND title LIKE :title ORDER BY Movies.created DESC LIMIT $num";
        //$params =  [":title" => "%$search%"];
        $params[":title"] = "%$search%";
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
    $numMovies = 0;
    if(count($results) != 0)
    {
        $numMovies = count($results);
    }
} catch (PDOException $e) 
{
    error_log("Error fetching movies " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

if(isset($_POST['user_id']))
{
    try {
        $stmt = $db->prepare("DELETE FROM UserMovies WHERE user_id = :user_id");
        $stmt->execute($params);
        flash("Cleared Watch List", "success");
        die(header("Location: " . get_url("movie_watchlist.php?title=$title&filter=$filter")));
    } catch (PDOException $e) {
        flash("Error removing item from watch list", "danger");
        error_log("Error removing watch: " . var_export($e, true));
    }
}

$filterData = ["title" => $title, "filter"=>$num];
$ignore_columns = ["id", "is_watched"];
$table = ["data" => $results, "title" => "Latest Movies", "ignored_columns"=>$ignore_columns, "view_url" => get_url("view_movie.php")];
?>
<div class="container-fluid">
    <h3 class="text-center"> <?php echo get_username(); ?>'s Watch List</h3>
    <h4 class="text-center" >Movies in Watch List: 
        <?php 
            $userID = $params[':user_id'];
            try {
                $stmt = $db->prepare("SELECT COUNT(user_id) FROM UserMovies WHERE user_id = $userID");
                $stmt->execute();
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($r) 
                {
                    $movieNum = $r[0]["COUNT(user_id)"];
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
    <form method="POST">
        <?php render_input(["type" => "hidden", "name" => "user_id", "value"=>get_user_id()]); ?>
        <?php render_button(["text" => "Clear Watch List", "type" => "submit", "color"=>"danger"]); ?>
    </form>
    <div class="row">
        <?php foreach ($results as $movie): ?>
            <div class="col-3">
                <?php movie_card($movie); ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($results)): ?>
            No records to show
        <?php endif; ?>
    </div>
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
require_once(__DIR__ . "/../../partials/flash.php");
?>