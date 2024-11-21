<?php

function fetch_movie($title)
{
    $data = ["exact"=>"false", "titleType"=>"movie"];
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
            $entries = $result['entries'];

            for($i = 0; $i < $entries; $i++)
            {
                if($result['results'][$i]['primaryImage'] == NULL)
                {
                    $output[$i]["image_url"] = NULL;
                    $output[$i]["caption"] = "No information is currently available.";
                }
                else
                {
                    $output[$i]["image_url"] = $result['results'][$i]['primaryImage']['url'];
                    $output[$i]["caption"] = $result['results'][$i]['primaryImage']['caption']['plainText'];
                }
                $output[$i]["title"] = $result['results'][$i]['titleText']['text'];
                if($result['results'][$i]['releaseDate'] == NULL)
                {
                    $output[$i]["release_date"] = "0001-01-01";
                }
                else
                {
                    $day = $result['results'][$i]['releaseDate']['day'];
                    $month = $result['results'][$i]['releaseDate']['month'];
                    $year = $result['results'][$i]['releaseDate']['year'];
                    $output[$i]["release_date"] = $year . "-" . $month . "-" . $day;
                }
                $output[$i]["is_api"] = 1;
            }
        }
    } else 
    {
        $result = [];
    }
    
    return $output;
}