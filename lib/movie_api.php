<?php

function fetch_quote($title)
{
    $data = ["titleType"=>"movie"];
    $title = rawurlencode($title);
    $endpoint = "https://moviesdatabase.p.rapidapi.com/titles/search/title/$title";
    $isRapidAPI = true;
    $rapidAPIHost = "moviesdatabase.p.rapidapi.com";
    
    $result = get($endpoint, "STOCK_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    $output = [];
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
        if(count($result) > 1 && $result['entries'] != 0)
        {
            if($result['results'][0]['primaryImage'] == NULL)
            {
                $output['image_url'] = NULL;
            }
            else
            {
                $output['url'] = $result['results'][0]['primaryImage']['image_url'];
            }
            $output['title'] = $result['results'][0]['titleText']['text'];
            $day = $result['results'][0]['releaseDate']['day'];
            $month = $result['results'][0]['releaseDate']['month'];
            $year = $result['results'][0]['releaseDate']['year'];
            $output['release_date'] = $year . "-" . $month . "-" . $day;
        }
    } else {
        $result = [];
    }
    
    return $output;
}