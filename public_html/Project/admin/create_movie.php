<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php

//TODO handle stock fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $title =  $_POST["title"];
    $isValid = true;
    $movies = [];

    //jd755 11/21/24
    if($title) 
    {
        if(strlen($title) > 200)
        {
            flash("[PHP] Title too long (cannot exceed 200 characters)", "warning");
            $isValid = false;
        }
        else
        {
            if ($action === "fetch") 
            {
                $result = fetch_movie($title);
                error_log("Data from API" . var_export($result, true));
                if ($result) 
                {
                    $movies = $result;
                    //$movies["is_api"] = 1;
                }
            } 
            else if ($action === "create") 
            {
                $date = $_POST["release_date"];
                $caption = $_POST["caption"];
                if(strlen($date) == 0)
                {
                    flash("[PHP] You must provide a date", "warning");
                    $isValid = false;
                }
                else if(!preg_match('/^\d{4}\-(0?[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/', $date))
                {
                    flash("[PHP] Invalid date", "warning");
                    $isValid = false;
                }
                else if(strlen($caption) > 500)
                {
                    flash("[JavaScript] Caption too long. (cannot exceed 500 characters)", "warning");
                    $isValid = false;
                }
                else
                {
                    foreach ($_POST as $k => $v) 
                    {
                        if (!in_array($k, ["image_url", "title", "caption", "release_date"])) {
                            unset($_POST[$k]);
                        }
                        $movies = $_POST;
                        error_log("Cleaned up POST: " . var_export($movies, true));
                    }
                }
            }
        }
    } 
    else 
    {
        flash("[PHP] You must provide a title", "warning");
        $isValid = false;
    }
    //insert data
    if($isValid)
    {
        /*$db = getDB();
        $query = "INSERT INTO `Movies` ";
        $columns = [];
        $params = [];*/
        //per record
        if(count($movies) > 0)
        {
            if($action == "fetch")
            {
                insert("Movies", $movies, ["debug" => true, "update_duplicate" => true]);
                flash("Sucessfully fetched movie(s)!", "success");
            }
            else
            {
                try
                {
                    insert("Movies", $movies, ["debug" => true]);
                    flash("Successfully created movie!", "success");
                }
                catch(PDOException $e)
                {
                    movie_check_duplicate($e->errorInfo);
                }
            }
            /*foreach ($movies as $k => $v) {
                array_push($columns, "`$k`");
                $params[":$k"] = $v;
            }
            $query .= "(" . join(",", $columns) . ")";
            $query .= "VALUES (" . join(",", array_keys($params)) . ")";
            error_log("Query: " . $query);
            error_log("Params: " . var_export($params, true));*/
            /*try 
            {
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                insert("Movies", $movies, ["debug" => true, "update_duplicate" => true]);
                flash("Inserted record " . $db->lastInsertId(), "success");
            } 
            catch (PDOException $e) 
            {
                movie_check_duplicate($e->errorInfo);
            }*/
        }
        else
        {
            flash("[PHP] Movie could not be found.", "warning");
        }
    }
}

//TODO handle manual create stock
?>
<div class="container-fluid bg">
    <h3>Create or Fetch Movie</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-dark text-white " href="#" onclick="switchTab('create')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-dark text-white" href="#" onclick="switchTab('fetch')">Create</a>
        </li>
    </ul>
    <div id="fetch" class="tab-target">
        <form onsubmit="return validate_fetch(this)"method="POST">
            <?php render_input(["type" => "search", "name" => "title", "placeholder" => "Movie Title", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form onsubmit="return validate(this)" method="POST">

            <?php render_input(["type" => "text", "name" => "title", "placeholder" => "Movie Title (required)", "label" => "Movie Title", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "image_url", "placeholder" => "Movie Image", "label" => "Movie Image"]); ?>
            <?php render_input(["type" => "text", "name" => "caption", "placeholder" => "Movie Caption", "label" => "Movie Caption", "value"=>"No information is available for this movie"]); ?>
            <?php render_input(["type" => "text", "name" => "release_date", "placeholder" => "Release Date (YYYY-MM-DD, required)", "label" => "Release Date", "rules" => ["required" => "required"]]); ?>

            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>
<script>
    function switchTab(tab) 
    {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }

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
            form.caption.value = "No information is available for this movie."
        }

        return isValid; //return true to test PHP validation
    }

    function validate_fetch(form)
    {
        let title = form.title.value;
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

        return isValid;
    }

</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>