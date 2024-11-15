<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
$output = [];
if (isset($_GET["title"])) {
    //function=GLOBAL_QUOTE&symbol=MSFT&datatype=json
    $data = ["titleType"=>"movie"];
    $title = rawurlencode($_GET["title"]);
    $endpoint = "https://moviesdatabase.p.rapidapi.com/titles/search/title/$title";
    $isRapidAPI = true;
    $rapidAPIHost = "moviesdatabase.p.rapidapi.com";
    $result = get($endpoint, "STOCK_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
        //var_dump($result);
        if(count($result) > 1 && $result['entries'] != 0)
        {
            $output['url'] = $result['results'][0]['primaryImage']['url'];
            $output['title'] = $result['results'][0]['titleText']['text'];
            $day = $result['results'][0]['releaseDate']['day'];
            $month = $result['results'][0]['releaseDate']['month'];
            $year = $result['results'][0]['releaseDate']['year'];
            $output['release_date'] = $year . "/" . $month . "/" . $day;
        }
        else
        {
            flash("Could not find movie!", "warning");
        }

    } else {
        $output = [];
    }
}
?>
<div class="container-fluid">
    <h1>Movie Info</h1>
    <form>
        <div>
            <label>Movie Title</label>
            <input name="title" />
            <input type="submit" value="Fetch Movie" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($output) && !empty($output)) : ?>
            <?php foreach ($output as $attribute) : ?>
                <pre>
                    <?php var_export($attribute);?>
                </pre>
            <?php endforeach; ?>
            <img src="<?php echo $output['url']; ?>" alt="image">
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");