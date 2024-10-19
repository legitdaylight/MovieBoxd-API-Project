<?php
// Array set A (user information)
$a1_users = [
    ["userId" => 1, "name" => "Alice", "age" => 28],
    ["userId" => 2, "name" => "Bob", "age" => 34]
];

$a2_users = [
    ["userId" => 3, "name" => "Charlie", "age" => 22],
    ["userId" => 4, "name" => "Diana", "age" => 29]
];

$a3_users = [
    ["userId" => 5, "name" => "Eve", "age" => 31],
    ["userId" => 6, "name" => "Frank", "age" => 26]
];

$a4_users = [
    ["userId" => 7, "name" => "Grace", "age" => 25],
    ["userId" => 8, "name" => "Hank", "age" => 30]
];

// Array set B (user activity)
$a1_activities = [
    ["userId" => 1, "activity" => "Running"],
    ["userId" => 2, "activity" => "Swimming"]
];

$a2_activities = [
    ["userId" => 3, "activity" => "Cycling"],
    ["userId" => 4, "activity" => "Hiking"]
];

$a3_activities = [
    ["userId" => 5, "activity" => "Climbing"],
    ["userId" => 6, "activity" => "Skiing"]
];

$a4_activities = [
    ["userId" => 7, "activity" => "Diving"],
    ["userId" => 8, "activity" => "Surfing"]
];

function joinArrays($users, $activities) {
    echo "<br>Processing Arrays:<br><pre>Users: " . var_export($users, true) . "<br>Activities: " . var_export($activities, true) . "</pre>";
    echo "<br>Joined output:<br>";
    
    // Note: use the $users and $activities variables to iterate over, don't directly touch $a1-$a4 arrays
    // TODO add logic here to join the arrays on userId
    $joined = []; // result array
    // Start edits
    // jd755 10/16/24
    $subArrayNum = 0; // iterator for $joined subarrays
    foreach($users as $user) // for loop that goes through every subarray in $users
    { 
        foreach($activities as $act) // for loop that goes through every subarray in $activies
        {
            if($user['userId'] == $act['userId']) // checks if the subarrays have the same userID
            {
                $joined[$subArrayNum]['userId'] = $user['userId']; //add userId to $joined

                foreach($user as $x => $y) // goes through every key, value pair and adds it to $joined
                {
                    if($x != 'userId')
                    {
                        $joined[$subArrayNum][$x] = $y;
                    }
                }

                foreach($act as $x => $y)
                {
                    if($x != 'userId')
                    {
                        $joined[$subArrayNum][$x] = $y;
                    }
                }

                break;// breaks out of for loop as matching subarray was already found
            }
        }
        $subArrayNum++;
    }

    // End edits
    echo "<pre>" . var_export($joined, true) . "</pre>";
}

echo "Problem 3: Joining Arrays on userId<br>";
?>
<table>
    <thead>
        <tr>
            <th>A1</th>
            <th>A2</th>
            <th>A3</th>
            <th>A4</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?php joinArrays($a1_users, $a1_activities); ?>
            </td>
            <td>
                <?php joinArrays($a2_users, $a2_activities); ?>
            </td>
            <td>
                <?php joinArrays($a3_users, $a3_activities); ?>
            </td>
            <td>
                <?php joinArrays($a4_users, $a4_activities); ?>
            </td>
        </tr>
    </tbody>
</table>
<style>
    table {
        border-spacing: 2em 3em;
        border-collapse: separate;
    }

    td {
        border-right: solid 1px black;
        border-left: solid 1px black;
    }
</style>