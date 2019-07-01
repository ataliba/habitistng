<?php 
############################################
##### Ataliba Teixeira - ataliba@pm.me #####
###
############################################

###############################
###    Library inclusion    ###
###############################
 
require("HabitRPHPG.php");

####################################
###       Json section           ###
####################################
 
$JsonTask = json_decode(file_get_contents('php://input'), true);
print_r($JsonTask);

preg_match('/\[([^]]+)\]/', $JsonTask['Task'], $output_array);


#################################################################
#####            Static variables ( processed )             #####
#################################################################
 
$Task  = $JsonTask['Task'];
$SEC_KEY = $JsonTask['SEC_KEY'];

if ( count($output_array) > 1 ) {
$TypeofTask = $output_array[1];
}
else 
$TypeofTask="";

####################
####  Functions  ###
#################### 

 function showTask($task) {
        print " + " . $task['text'] . "\n";
 }

 function info($stats) {
        global $api;

        $stats = $api->getStats($stats);
        if(isset($stats['maxHealth']) and $stats['maxHealth']) {
                print "Health:\t\t" . getFillStatus($stats['hp'], $stats['maxHealth']) . "\n";
                print "Experience:\t". getFillStatus($stats['exp'], $stats['toNextLevel']) . "\n";
                if($stats['lvl'] > 10) print "Mana:\t\t". getFillStatus($stats['mp'], $stats['maxMP']) . "\n";
        } else {
                print "Health: " . $stats['hp'] . "\n";
                print "Experience: " . $stats['exp'] . "\n";
        }

        print "Gold: $stats[gold] | Silver: $stats[silver]\n";

        // Show Delta
        $status_file = dirname(__FILE__) . '/cache/user_status.json';
        $old_status = json_decode(file_get_contents($status_file), true);

        if($stats != $old_status) {
                print "Change: ";
                foreach($stats as $name => $value) {
                        if($name == 'maxHealth' or $name == 'toNextLevel') continue;
                        if($stats[$name] != $old_status[$name] and ($stats[$name] - $old_status[$name]) > 0)
                                print ucfirst($name) . ": " . ($stats[$name] - $old_status[$name]) . " | ";
                }
                print "\n";
        }

        file_put_contents($status_file, json_encode($stats));

        global $cache;
        if($cache) {

        }
 }
 

 function showDrops($result) {
        if(isset($result['_tmp']['drop'])) {
                print $result['_tmp']['drop']['dialog'] . "\n";
        }
 }

 function doTask($direction, $task_string) {
        global $api;
        $tasks = _search($task_string);

        if(count($tasks) == 1) {
                $result = $api->doTask($tasks[0]['id'], $direction);
                print "Task '{$tasks[0]['text']}' is done.\n";
                showDrops($result);
                print "\n";
                info($result);

        } elseif(count($tasks) > 1) {
                print "Search phrase '$task_string' matches the following tasks...\n";
                foreach ($tasks as $task) {
                        showTask($task);
                }

        } else {
                print "Could not find any tasks matching '$task_string'\n";
        }
    }

     /// Return only the tasks that matches the search string.
    function _search($task_string) {
        global $api;

        return $api->findTask($task_string);
    }

######################
###  Main program  ###
######################

if (getenv('SEC_KEY') == "$SEC_KEY" ) {

 if( "$TypeofTask" == "Habit" || "$TypeofTask" == "Todo" || "$TypeofTask" == "Daily" ) {
     $api = new HabitRPHPG(getenv('HABITICA_USER'),getenv('HABITICA_USER_KEY'));
     $data = $api->user();

    doTask('up',"$Task");
 }
 else 
    echo "Not run";	
 
}
else 
   echo "Not Authorized";

?>

