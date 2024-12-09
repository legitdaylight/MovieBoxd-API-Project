<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
//update associations
if (isset($_POST["users"]) && isset($_POST["movieList"])) 
{
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $movie_ids = $_POST["movieList"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($movie_ids)) 
    {
        flash("Both users and movies need to be selected", "warning");
    } 
    else 
    {
        foreach ($user_ids as $uid) 
        {
            foreach ($movie_ids as $mid) 
            {
                $needsDelete = false;
                $db = getDB();
                try 
                {
                    $stmt = $db->prepare("INSERT INTO UserMovies (user_id, movie_id) VALUES (:uid, :mid)");
                    $stmt->execute([":uid" => $uid, ":mid" => $mid]);
                } catch (PDOException $e) 
                {
                    // use duplicate error as a delete trigger
                    if ($e->errorInfo[1] == 1062) 
                    {
                        $needsDelete = true;
                    } else {
                        flash("Error", "danger");
                        error_log("Error updating associations" . var_export($e, true));
                    }
                }

                if ($needsDelete) 
                {
                    try {
                        $stmt = $db->prepare("DELETE FROM UserMovies WHERE movie_id = :mid AND user_id = :uid");
                        $stmt->execute([":uid" => $uid, ":mid" => $mid]);
                    } catch (PDOException $e) {
                        flash("Error updating associations", "danger");
                        error_log("Error updating associations" . var_export($e, true));
                    }
                }
            }
        }
        flash("Updated associations ", "success");
    }
    
}

$users = [];
$username = "";
$movie = "";
$movies = [];
if (isset($_POST["username"]) && isset($_POST["movie"] )) 
{
    #get users
    $username = se($_POST, "username", "", false);
    $movie = se($_POST, "movie", "", false);
    if(empty($username)) 
    {
        flash("Username must not be empty", "warning");
    }
    elseif(empty($movie))
    {
        flash("Movie must not be empty", "warning");
    }
    else
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT username, id,
        (SELECT GROUP_CONCAT(Movies.title ) 
        FROM Movies 
        JOIN UserMovies 
        ON Movies.id = UserMovies.movie_id 
        WHERE UserMovies.user_id = Users.id) as movie
        FROM Users
        WHERE username LIKE :username 
        LIMIT 25");
        try 
        {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) 
            {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id, title as `name` FROM Movies WHERE title LIKE :movie LIMIT 25");
        try {
            $stmt->execute([":movie" => "%$movie%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) 
            {
                $movies = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}


?>
<div class="container-fluid">
    <h1>Assign Movies</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "movie", "placeholder" => "Movie Search", "value" => $movie]); ?>
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username) && isset($movie) && !empty($movie)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
            <input type="hidden" name="movie" value="<?php se($movie, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>Movies to Assign</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <label for="user_<?php se($user, 'id'); ?>"><?php se($user, "username"); ?></label>
                                        <input id="user_<?php se($user, 'id'); ?>" type="checkbox" name="users[]" value="<?php se($user, 'id'); ?>" />
                                    </td>
                                    <td><?php se($user, "movie", "No Movies"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php foreach ($movies as $curr_movie) : ?>
                            <div>
                                <label for="movie_<?php se($curr_movie, 'id'); ?>"><?php se($curr_movie, "name"); ?></label>
                                <input id="movie_<?php se($curr_movie, 'id'); ?>" type="checkbox" name="movieList[]" value="<?php se($curr_movie, 'id'); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Association", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>