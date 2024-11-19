<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
$output = [];
if (isset($_GET["title"])) {
    //function=GLOBAL_QUOTE&symbol=MSFT&datatype=json
    $data = ["exact"=>"false", "titleType"=>"movie"];
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
            $entries = $result['entries'];

            for($i = 0; $i < $entries; $i++)
            {
                if($result['results'][$i]['primaryImage'] == NULL)
                {
                    $output[$i]['url'] = NULL;
                    $output[$i]['caption'] = "No information is currently available.";
                }
                else
                {
                    $output[$i]['url'] = $result['results'][$i]['primaryImage']['url'];
                    $output[$i]['caption'] = $result['results'][$i]['primaryImage']['caption']['plainText'];
                }
                $output[$i]['title'] = $result['results'][$i]['titleText']['text'];
                if($result['results'][$i]['releaseDate'] == NULL)
                {
                    $output[$i]['release_date'] = "0000-00-00";
                }
                else
                {
                    $day = $result['results'][$i]['releaseDate']['day'];
                    $month = $result['results'][$i]['releaseDate']['month'];
                    $year = $result['results'][$i]['releaseDate']['year'];
                    $output[$i]['release_date'] = $year . "-" . $month . "-" . $day;
                }
            }
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
            <?php for ($i = 0; $i < $entries; $i++) : ?>
                <?php foreach ($output[$i] as $attribute) : ?>
                    <pre> 
                        <?php var_export($attribute); ?>
                    </pre>
                <?php endforeach; ?>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");