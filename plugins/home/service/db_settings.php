<?php

require '../../../api/src/config/crypt.php';

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);
$host = trim($_POST['host']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$database = trim($_POST['database']);
$get_action = $_POST['action'];
$get_name = $_POST['name'];

// output json information
function jsonMsg($status, $message, $name = '')
{
    $arr['status'] = $status;
    $arr['message'] = $message;
    $arr['name'] = $name;
    echo json_encode($arr);
}

if ($get_action == "db_name") {
    $ini_array = parse_ini_file("../../../genie_files/settings",true) or die("Unable to open file!");
    $genieCrypt=new genieCrypt();

    $host=$ini_array['settings'][host];
    $user=$ini_array['settings'][username];
    $pass=$genieCrypt->DecryptThis($ini_array['settings'][password]);
    $database=$ini_array['settings'][database];
    $ret=array();
    $ret["host"]=$host;
    $ret["user"]=$user;
    $ret["pass"]=$pass;
    $ret["database"]=$database;

    $conn = mysqli_connect($host, $user, $pass);
    if (!$conn) {
        jsonMsg('error', "Connection failed: " , json_encode($ret));
    }else{
        jsonMsg('success', "Database server connection was established.", json_encode($ret));
    }

}


/*
//Test Connection - check whther the given username and passwords are correct
if ($get_action == "db_name") {
    //Check the settings file for database name
    $settings_file = fopen("../../../genie_files/settings", "r") or die("Unable to open file!");
    $db_name = fgets($settings_file);
    fclose($settings_file);
    ($db_name == false) ? $db_name = "" : $db_name = $db_name;
    //Make a connection
    $conn = mysqli_connect($host, $username, $password);
    // Check connection
    if (!$conn) {
        jsonMsg('error', "Connection failed: " . mysqli_connect_error);
        die("Connection failed: " . mysqli_connect_error);
    } else {
        jsonMsg('success', "Database server connection was established.", $db_name);
    }
}*/

//Create a new database depending on the given name
if ($get_action == "create_database") {
    //Make a connection
    $link = mysqli_connect($host, $username, $password);
    if (!$link) {
        jsonMsg('error', "Wrong username and or password");
        exit;

    } else {
        if (!mysqli_select_db($link, $database)) {
            $sql = "CREATE DATABASE " . $database;
            // $sql = "CREATE DATABASE ".$database.";GRANT SELECT ON ".$database.".* TO ".$username."@'".$host."';GRANT INSERT,UPDATE,DELETE ON ".$database.".genebaskets TO ".$username."@'".$host."';GRANT INSERT,UPDATE,DELETE ON ".$database.".defaultgenebaskets TO ".$username."@'".$host."';";
            if ($link->query($sql) === true) {
                jsonMsg('success', "<strong>" . $database . "</strong> database was created", $database);
                mysqli_close($link);
                load_sql($host, $username, $password, $database, $get_name);
            } else {
                jsonMsg('error', "Not enough permssion to create <strong>" . $database . "</strong> database", $database);
                mysqli_close($link);
            }
        } else {
            jsonMsg('success', "Database <strong>" . $database . "</strong> already exist", $database);
            mysqli_close($link);
        }
    }

}

//Drop exsisting database
if ($get_action == "drop_database") {
    saveSettings();
    //Make a connection
 /*   $link = mysqli_connect($host, $username, $password);
    if (!$link) {
        jsonMsg('error', "Wrong username and or password");
        exit;
    } else {
        if (!mysqli_select_db($link, $database)) {
            jsonMsg('error', "Database <strong>" . $database . "</strong> does not exist");
        } else {
            $sql = "DROP DATABASE " . $database;
            $link->query($sql);
            saveSettings("");
            jsonMsg('success', "<strong>" . $database . "</strong> database was deleted");
        }
    }*/
}

//clone_database
if ($get_action == "clone_database") {
    $link = mysqli_connect($host, $username, $password);
    if (!$link) {
        jsonMsg('error', "Wrong username and or password");
        exit;
    } else {
        if (!mysqli_select_db($link, $database)) {
            $sql = "CREATE DATABASE " . $database;
            if ($link->query($sql) === true) {
                jsonMsg('success', "<strong>" . $database . "</strong> database was created  with $get_name data", $database);
                mysqli_close($link);
                load_sql($host, $username, $password, $database, $get_name);
            } else {
                jsonMsg('error', "Not enough permssion to create <strong>" . $database . "</strong> database", $database);
                mysqli_close($link);
            }
        } else {
            $sqlx = "DROP DATABASE " . $database;
            if ($link->query($sqlx) === true) {
                $link->query($sqlx);
                $sql = "CREATE DATABASE " . $database;
                jsonMsg('success', "<strong>" . $database . "</strong> already exist but created a new database with $get_name data", $database);
                mysqli_close($link);
                load_sql($host, $username, $password, $database, $get_name);
            } else {
                jsonMsg('error', "Not enough permssion to drop or create <strong>" . $database . "</strong> database", $database);
                mysqli_close($link);
            }
            mysqli_close($link);
        }
    }

}

function downloadZipFile($url, $filepath)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    $raw_file_data = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'error:' . curl_error($ch);
    }
    curl_close($ch);

    file_put_contents($filepath, $raw_file_data);
    return (filesize($filepath) > 0) ? true : false;
}

// load MySQL dump file into the database
function load_sql($host, $username, $password, $database, $get_name)
{

    if ($get_name == "dump") {
        $url = "http://build.plantgenie.org/tmp/dump/dump.sql";
        $file_name = "dump.sql";
    } else {
        $url = "http://build.plantgenie.org/tmp/Athaliana_447/Athaliana_447.sql";
        $file_name = "Athaliana_447.sql";
    }
    //$url="http://build.plantgenie.org/tmp/".$key."/".$file_name;
    $targetFile = fopen($file_name, 'w');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progressCallback');
    curl_setopt($ch, CURLOPT_FILE, $targetFile);
    curl_exec($ch);
    fclose($targetFile);

    function progressCallback($download_size, $downloaded_size, $upload_size, $uploaded_size)
    {
        static $previousProgress = 0;

        if ($download_size == 0) {
            $progress = 0;
        } else {
            $progress = round($downloaded_size * 100 / $download_size);
        }

        if ($progress > $previousProgress) {
            $previousProgress = $progress;
            //echo $progress;
        }
    }

    $script_path = getcwd() . "/" . $file_name;

    $conn = new mysqli($host, $username, $password, $database);
    $conn->query('SET @@global.max_allowed_packet = ' . 100 * 1024 * 1024);
    //$maxp2 = $conn->query( 'SELECT @@global.max_allowed_packet' )->fetch_array();
    $sql = file_get_contents($script_path);
    if (mysqli_multi_query($conn, $sql)) {
        do {
            /* store first result set */
            if ($result = mysqli_store_result($conn)) {
                //do nothing since there's nothing to handle
                mysqli_free_result($result);
            }
            /* print divider */
            if (mysqli_more_results($conn)) {
                //I just kept this since it seems useful
                //try removing and see for yourself
            }
        } while (mysqli_next_result($conn));
    }

    //saveSettings($database);
    #User permissions:
    $conn->query("CREATE USER IF NOT EXISTS geniecmsuser@'" . $host . "' IDENTIFIED BY 'geniepass'; ");
    $conn->query("GRANT ALL ON " . $database . ".* TO geniecmsuser@'" . $host . "';"); //ALL replace with SELECT
    //$conn->query("GRANT INSERT,UPDATE,DELETE ON ".$database.".genebaskets TO geniecmsuser@'".$host."';");
    //$conn->query("GRANT INSERT,UPDATE,DELETE ON ".$database.".defaultgenebaskets TO geniecmsuser@'".$host."';");
    exec("rm -r $file_name");

    if (!file_exists('upload')) {
        mkdir('upload', 0777, true);
    }

}

/*if the settings file exist save the settings*/
function saveSettings()
{
  $genieCrypt=new genieCrypt();
    $data = array(
        'settings' => array(
            'host' =>  trim($_POST['host']),
            'username' => trim($_POST['username']),
            'password' => $genieCrypt->EncryptThis(trim($_POST['password'])),
            'database' => trim($_POST['database'])
        )
    );

    write_php_ini($data, "../../../genie_files/settings") ;
    jsonMsg('success', "settings file saved.", "done");
}



function write_php_ini($array, $file)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        }
        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    safefilerewrite($file, implode("\r\n", $res));
}

function safefilerewrite($fileName, $dataToSave)
{    if ($fp = fopen($fileName, 'w'))
    {
        $startTime = microtime(TRUE);
        do
        {            $canWrite = flock($fp, LOCK_EX);
           // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
           if(!$canWrite) usleep(round(rand(0, 100)*1000));
        } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

        //file was locked so now we can store information
        if ($canWrite)
        {            fwrite($fp, $dataToSave);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

}


//Upload GFF3 file
if ($get_action == "upload_gff3") {
    // CHANGE THE UPLOAD LIMITS
    ini_set('upload_max_filesize', '500M');
    ini_set('post_max_size', '500M');
    ini_set('max_input_time', 10000);
    ini_set('max_execution_time', 10000);

    $arr_file_types = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'];

    if ($_FILES["file"]["size"] == 0) {
/*     if (!(in_array($_FILES['file']['type'], $arr_file_types))) {*/
        echo "false";
        return;
    }
    move_uploaded_file($_FILES['file']['tmp_name'], 'upload/' . $_FILES['file']['name']);
    exec("awk '$3==\"gene\"{g=$4\" \"$5}$3~/RNA$/{split($9,a,/[;=]/);for(i=1;i in a;i+=2)k[a[i]]=a[i+1]; print k[\"ID\"], k[\"Parent\"], \"desc\", $1, $7, $4, $5, g}' " . 'upload/' . $_FILES['file']['name'] . " >" . 'upload/' . $_FILES['file']['name'] . "_transcript.tsv");
    exec("awk '/gene/{split($9,a,\"ID=\");split(a[2],b,\";\");print b[1],$1,$4,$5}' FS='\t' OFS='\t' " . 'upload/' . $_FILES['file']['name'] . " >" . 'upload/' . $_FILES['file']['name'] . "_gene.tsv");
    load_files('upload/' . $_FILES['file']['name'] . "_gene.tsv", 'gene_info');
}

//Loading tables
function load_files($input_file, $table_name)
{
    //$input_file=getcwd().'/../tmp/'.$folder.'/'.$table_name.'.txt';
    // $database=$source;
    //echo $input_file, $table_name;

    //Build the connection
    include dirname(__FILE__) . '/geniesys/plugins/settings.php';
    $private_url = parse_url($db_url['genelist']);
    $conn = new mysqli($private_url['host'], $private_url['user'], $private_url['pass'], str_replace('/', '', $private_url['path']));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //Truncate and load table
    $query = <<<eof
TRUNCATE TABLE $table_name;
ALTER TABLE $table_name AUTO_INCREMENT = 1;
load data local infile '$input_file' ignore  INTO TABLE $table_name CHARACTER SET UTF8 fields terminated by '\t' LINES TERMINATED BY '\n' ignore 0 lines;
eof;
    /* execute multi query */
    if (mysqli_multi_query($conn, $query)) {
        do {
            /* store first result set */
            if ($result = mysqli_store_result($conn)) {
                //do nothing since there's nothing to handle
                mysqli_free_result($result);
            }
            /* print divider */
            if (mysqli_more_results($conn)) {
                //I just kept this since it seems useful
                //try removing and see for yourself
            }
        } while (mysqli_next_result($conn));
    }
    mysqli_close($conn);
}

$output_dir = "upload/";
if (isset($_FILES["myfile"])) {
    // CHANGE THE UPLOAD LIMITS
    ini_set('upload_max_filesize', '500M');
    ini_set('post_max_size', '500M');
    ini_set('max_input_time', 10000);
    ini_set('max_execution_time', 10000);
    $ret = array();

//    This is for custom errors;
    /*    $custom_error= array();
    $custom_error['jquery-upload-file-error']="File already exists";
    echo json_encode($custom_error);
    die();
     */
    $error = $_FILES["myfile"]["error"];
    //You need to handle  both cases
    //If Any browser does not support serializing of multiple files using FormData()
    if (!is_array($_FILES["myfile"]["name"])) //single file
    {
        $fileName = $_FILES["myfile"]["name"];
        move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir . $fileName);
        $ret[] = $fileName;
    } else //Multiple files, file[]
    {
        $fileCount = count($_FILES["myfile"]["name"]);
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES["myfile"]["name"][$i];
            move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $output_dir . $fileName);
            $ret[] = $fileName;
        }

    }
    echo json_encode($ret);
}
