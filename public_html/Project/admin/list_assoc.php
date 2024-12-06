<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

$title=$_GET["title"];
$num=$_GET["filter"];
$user=$_GET["user"];
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

if(!$title && !$user)//both user and title are blank
{
    $query = "SELECT UserMovies.id as assoc_id, Movies.id as id, Movies.title, Movies.is_api, Users.username, Users.id as user_id,
    (SELECT COUNT(*) FROM UserMovies WHERE Movies.id = UserMovies.movie_id) as assocCount
    FROM Movies 
    INNER JOIN UserMovies ON Movies.id = UserMovies.movie_id  
    INNER JOIN Users ON Users.id = UserMovies.user_id 
    ORDER BY Movies.created 
    DESC LIMIT $num";
}
else
{
    if(strlen($title) > 200)
    {
        flash("[PHP] Title too long (cannot exceed 200 characters) ", "warning");
        $query = "SELECT UserMovies.id as assoc_id, Movies.id as id, Movies.title, Movies.is_api, Users.username, Users.id as user_id,
        (SELECT COUNT(*) FROM UserMovies WHERE Movies.id = UserMovies.movie_id) as assocCount
        FROM Movies 
        INNER JOIN UserMovies 
        ON Movies.id = UserMovies.movie_id 
        INNER JOIN Users 
        ON Users.id = UserMovies.user_id 
        ORDER BY Movies.created
        DESC LIMIT $num";
    }
    elseif(!empty($user) && !is_valid_username($user))
    {
        flash("[PHP] Invalid username", "warning");
        $query = "SELECT UserMovies.id as assoc_id, Movies.id as id, Movies.title, Movies.is_api, Users.username, Users.id as user_id,
        (SELECT COUNT(*) FROM UserMovies WHERE Movies.id = UserMovies.movie_id) as assocCount
        FROM Movies 
        INNER JOIN UserMovies 
        ON Movies.id = UserMovies.movie_id 
        INNER JOIN Users 
        ON Users.id = UserMovies.user_id 
        ORDER BY Movies.created
        DESC LIMIT $num";
    }
    else
    {
        $searchTitle = se($_GET, "title", "", false);
        $searchUsername = se($_GET, "user", "", false);
        $query = "SELECT UserMovies.id as assoc_id, Movies.id as id, Movies.title, Movies.is_api, Users.username, Users.id as user_id,
        (SELECT COUNT(*) FROM UserMovies WHERE Movies.id = UserMovies.movie_id) as assocCount
        FROM Movies 
        INNER JOIN UserMovies 
        ON Movies.id = UserMovies.movie_id 
        INNER JOIN Users 
        ON Users.id = UserMovies.user_id 
        WHERE Movies.title LIKE :title 
        AND Users.username LIKE :user
        ORDER BY Movies.created 
        DESC LIMIT $num";
        $params =  [":title" => "%$searchTitle%", ":user"=>"%$searchUsername%"];
    }
}

$db = getDB();
$stmt = $db->prepare($query);
$results = [];
$numAssoc = 0;
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) 
    {
        $results = $r;
    }
    $numAssoc = count($results);
} catch (PDOException $e) 
{
    error_log("Error fetching movies " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$filterData = ["title" => $title, "user"=>$user, "filter"=>$num];
$deleteURL = "admin/delete_assoc.php" . "?" . http_build_query($filterData);
$ignore_column = ["user_id"];
$table = ["data" => $results, "title" => "Latest Movies", "ignored_columns" =>$ignore_column,"view_url" => get_url("view_movie.php"), "delete_url" => get_url($deleteURL), "profile_url" => get_url("profile.php")];

if(isset($_POST['partial_user']))
{
    $db = getDB();
    $query = "DELETE FROM `UserMovies` WHERE user_id LIKE :user";
    $params =  [":user"=>"%$searchUsername%"];
    try 
    {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Sucessfully deleted associations: ", "success");
        die(header("Location: " . get_url("admin/list_assoc.php?title=$title&user=$user&filter=$filter")));
    } 
    catch (Exception $e) 
    {
        flash("Error: Could not delete association.", "danger");
    }
}

?>
<div class="container-fluid">
    <h3 class = "text-center">All User Associations</h3>
    <h4 class="text-center" >Number of Associations: 
        <?php 
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM UserMovies");
                $stmt->execute();
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($r) 
                {
                    $assocNum = $r[0]["COUNT(*)"];
                    echo $assocNum;
                }
            } catch (PDOException $e) {
                flash("Error retrieving number of movies in watch list", "danger");
                error_log("Error removing watch: " . var_export($e, true));
            }
        ?>
    </h4>
    <h5 class="text-center">
        Associations On Page: 

        <?php echo $numAssoc; ?>
    </h5>
    <form onsubmit="return validate(this)" method="GET">
        <?php render_input(["type" => "search", "name" => "title", "placeholder" => "Movie Title", "value"=>$title]); ?>
        <?php render_input(["type" => "search", "name" => "user", "placeholder" => "Username", "value"=>$user]); ?>
        <?php render_input(["type" => "number", "name" => "filter", "placeholder" => "Number of Records", "value"=>$num]); ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <?php if($user): ?>
            <form method="POST">
                <?php render_input(["type" => "hidden", "name" => "partial_user", "value"=>$user]); ?>
                <?php render_button(["text" => "Clear Associations", "type" => "submit", "color"=>"danger"]); ?>
            </form>
        <?php endif; ?>
    <?php render_table($table); ?>
</div>
<script>
    function validate(form)
    {
        let title = form.title.value;
        let filter = form.filter.value;
        let username = form.user.value;
        let isValid = true;

        if(title.length > 200)
        {
            flash("[JavaScript] Title too long (cannot exceed 200 characters) ", "warning");
            isValid = false;
        }

        if(filter < 1 || filter > 100)
        {
            flash("[JavaScript] Filter has to be between 1 and 100", "warning");
            isValid = false;
        }

        if(username.length >= 1 && !validate_username(username))
        {
            flash("[JavaScript] Invalid username", "warning");
            isValid = false;
        }

        return isValid;
    }
</script>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>