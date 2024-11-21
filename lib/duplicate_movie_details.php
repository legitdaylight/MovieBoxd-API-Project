<?php
function movie_check_duplicate($errorInfo)
{
    if ($errorInfo[1] === 1062) {
        //https://www.php.net/manual/en/function.preg-match.php
        preg_match("/Movies.(\w+)/", $errorInfo[2], $matches);
        if (isset($matches[1])) {
            flash("The chosen movie is already in the database.", "warning");
        } else {
            flash("An unhandled error occured", "danger");
            //this will log the output to the terminal/console that's running the php server
            error_log(var_export($errorInfo, true));
        }
    } else {
        flash("An unhandled error occured", "danger");
        //this will log the output to the terminal/console that's running the php server
        error_log(var_export($errorInfo, true));
    }
}