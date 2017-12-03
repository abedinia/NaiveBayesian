<?php

// friendly prtint
$timeExecution = [
    'time'      => time(true),
    'microtime' => microtime(true),
];
echo '<pre>';


// error handling
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
set_time_limit(0);


// configs
$label_index        = 8;    // start from 0
$test_start_index   = 600;  // start from 0
$number_of_features = 8;


// run procedure
try {
    // read data from csv file
    $fileContent = file_get_contents('dataSet.csv');

    // reading data
    $cnt = 0;
    $data = [];
    foreach (explode(PHP_EOL, $fileContent) as $row) {
        $data[ $cnt ] = explode(',', $row);

        for($i = 0; $i < 9; $i++) {
            if($i == 8) {
                $data[ $cnt ][ $i ] = (int) $data[ $cnt ][ $i ];
            } else {
                $data[ $cnt ][ $i ] = (float) $data[ $cnt ][ $i ];
            }
        }

        $cnt++;
    }

    // counting train values
    $stats_w0 = [];
    $stats_w1 = [];
    $number_of_w0 = 0;
    $number_of_w1 = 0;
    for($i = 0; $i < $test_start_index; $i++) {
        for($j = 0; $j < $number_of_features; $j++) {
            if($data[ $i ][ $label_index ] == 0) {
                if(!array_key_exists($j, $stats_w0)) {
                    $stats_w0[ $j ] = [];
                }

                // count features of W0
                if(array_key_exists((string) $data[$i][$j], $stats_w0[ $j ])) {
                    $stats_w0[ $j ][ (string) $data[$i][$j] ] += 1;
                } else {
                    $stats_w0[ $j ][ (string) $data[$i][$j] ] = 1;
                }
            } else {
                if(!array_key_exists($j, $stats_w1)) {
                    $stats_w1[ $j ] = [];
                }

                // count features of W1
                if(array_key_exists((string) $data[$i][$j], $stats_w1[ $j ])) {
                    $stats_w1[ $j ][ (string) $data[$i][$j] ] += 1;
                } else {
                    $stats_w1[ $j ][ (string) $data[$i][$j] ] = 1;
                }
            }
        }

        if($data[ $i ][ $label_index ] == 0) {
            // count W0
            $number_of_w0 += 1;
        } else {
            // count W1
            $number_of_w1 += 1;
        }
    }

    // calc probabilities
    $classifier = [];
    $count_wrong = 0;
    $count_correct = 0;
    for($i = $test_start_index; $i < count($data); $i++) {
        $Pwx_w0 = 1;
        $Pwx_w1 = 1;

        for($j = 0; $j < $number_of_features; $j++) {
            if(array_key_exists((string) $data[$i][$j], $stats_w0[ $j ])) {
                $Pwx_w0 *= ($stats_w0[ $j ][ (string) $data[$i][$j] ] / $number_of_w0);
            } else {
                $Pwx_w0 *= 0;
            }

            if(array_key_exists((string) $data[$i][$j], $stats_w1[ $j ])) {
                $Pwx_w1 *= ($stats_w1[ $j ][ (string) $data[$i][$j] ] / $number_of_w1);
            } else {
                $Pwx_w1 *= 0;
            }
        }

        $Pwx_w0 *= ($number_of_w0 / ($number_of_w0 + $number_of_w1));
        $Pwx_w1 *= ($number_of_w1 / ($number_of_w0 + $number_of_w1));

        $classifier[ $i ]['original'] = $data[ $i ][ $label_index ];

        if($Pwx_w0 > $Pwx_w1) {
            $classifier[ $i ]['classifier'] = 0;
        } else {
            $classifier[ $i ]['classifier'] = 1;
        }

        if($classifier[ $i ]['original'] == $classifier[ $i ]['classifier']) {
            $count_correct += 1;
        } else {
            $count_wrong += 1;
        }
    }


    echo 'correct: ' . $count_correct . '<br />';
    echo 'wrong: ' . $count_wrong . '<br />';
    echo 'accuracy: ' . (int) (($count_correct / ($count_correct + $count_wrong)) * 100) . '%<br />';
    echo '<pre>';
    print_r($classifier);
}
catch (Exception $e) {
    $msg = "[step: " . $start . "] " . PHP_EOL
           . "Caught exception: " . PHP_EOL
           . "{$e->getMessage()} " . PHP_EOL
           . "in {$e->getFile()} (Line: {$e->getLine()}) " . PHP_EOL
           . $e->getTraceAsString();

    echo $msg;
}


// end time
echo '</pre>';
echo '</br></br>';
echo '- - Time: ' . (time(true) - $timeExecution['time']) . '</br>';
echo '- - Microtime: ' . (microtime(true) - $timeExecution['microtime']) . '</br></br>';
