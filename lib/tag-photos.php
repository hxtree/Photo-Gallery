<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// change current working directory to same as script
chdir(__DIR__);

require '../lib/rest.class.php';
require '../lib/log.class.php';
require '../lib/db.class.php';


$rest = new REST();


$config = parse_ini_file('../resources/config/default.conf');

$db = new database($config['host'], $config['user'], $config['password'], $config['dbname']);
$results = $db->query('
    SELECT `file_id` 
    FROM `file` 
    WHERE `tags_detected` IS NULL 
    ;
');

foreach($results as $file){
    $file_path = '../assets/output/'.$file['file_id'].'/800.jpg';
    if( ! file_exists($file_path)){
        echo 'File ' . $file['file_id'] . ' not found skipping ' . PHP_EOL;
        continue;
    }

    // send rest request
    $response = $rest->request(
        [
            'url' => 'https://api.example.com/api/v1/aws/rekognition',
            'username' => 'photos',
            'password' => 'password',
            'method' => 'POST',
            'parameters' => [
                'file' => curl_file_create($file_path, 'image/jpeg', 'test.jpg'),
            ],
            'verbose' => false
        ]
    );

    if($response['status'] != 'success'){
        echo 'Failed REST request skipping ' . $file['file_id'] . PHP_EOL;
        continue;
    }
    
    // if no tags recieved skip
    if(( ! isset($response['message']['labels'])) || ( ! is_array($response['message']['labels']))){
        // wait we may want to make tags_detected?
        echo 'No tags found skipping ' . $file['file_id'] . PHP_EOL;
        continue;
    }

    // insert tags;
    foreach($response['message']['labels'] as $label){
        echo 'Adding to file' . $file['file_id'] . ' tag ' . $label . PHP_EOL;

        $db->bind('file_id', $file['file_id']);
        $db->bind('tag', $label);
        $db->query('
            INSERT INTO `file_tag` (`file_id`, `tag`) 
            VALUES (:file_id, :tag);
        ');
    }

    // mark as tags_detected
    echo 'Marking file' . $file['file_id'] . ' as tags detected' . PHP_EOL;
    $db->bind('file_id',$file['file_id']);
    $db->query('
        UPDATE `file`
        SET `tags_detected` = CURRENT_TIMESTAMP
        WHERE file_id = :file_id
        LIMIT 1;
    ');
}

