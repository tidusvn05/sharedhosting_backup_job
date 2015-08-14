
<?php
/*
-- Version --
1.1
2015-08-14

-- Author --
Name: D.Nobita
Email: tidusvn05@gmail.com

*/

//backup config
define('CURRENT_PATH', dirname(__FILE__) );
define('BACKUP_PATH', CURRENT_PATH.'/backups' );
define('TIME_TO_BACKUP_AGAIN', 30 ); // 3days: 259200
define('TIME_BUFFER_FOR_ONE_BACKUP', 8 ); // 5 minutes: 300
define('NOW', round(microtime(true)) );
define('NUMBER_BACKUP_WILL_KEEP', 5 ); // keep 5 last backup file

// for database connect config
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'bl');



init_env();

task_backup();




function task_backup(){
  //check time can take backup
	if( can_backup() ){
		//call backup
		backup_tables(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
		
		//clear backup then new backup file generated
		task_clear_old_backup();
		
	}else{
	  echo "not enough time to take a backup.";
	  echo "\r\n";
	}

}

function backup_tables($host,$user,$pass,$name,$tables = '*')
{

    $link = mysql_connect($host,$user,$pass);
    mysql_select_db($name,$link);
    mysql_query("SET NAMES 'utf8'");

    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while($row = mysql_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    $return='';
    //cycle through
    foreach($tables as $table)
    {	
   // echo "--- table: $table \r\n";
        $result = mysql_query('SELECT * FROM `'.$table.'`');
        $num_fields = mysql_num_fields($result);
		//echo "num fields: $num_fields \r\n";
        $return.= 'DROP TABLE IF EXISTS `'.$table.'`;';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE `'.$table.'`'));
        $return.= "\n\n".$row2[1].";\n\n";
		//echo "show create query:  {$row2[1]}";
		
        for ($i = 0; $i < $num_fields; $i++) 
        {
            while($row = mysql_fetch_row($result))
            {
                $return.= 'INSERT INTO `'.$table.'` VALUES(';
                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }

    //save file
	$backup_file = BACKUP_PATH.'/'.DB_NAME.'_'.NOW.'.sql';
    $handle = fopen($backup_file,'w+');

    fwrite($handle,$return);
    fclose($handle);
    
    //check complete?
  	if(file_exists($backup_file)) {
      echo "backup successful!. new file: $backup_file";
    }else{
      echo "backup not successful. plz check dir permission!";
    }
    
    echo "\r\n"; //break new line on terminal  
}

function task_clear_old_backup(){
  $files = scandir(BACKUP_PATH, SCANDIR_SORT_DESCENDING);
  for($i= NUMBER_BACKUP_WILL_KEEP; $i < sizeof($files); $i++){
    if (in_array($files[$i], array('.', '..') )) {
      // skip current dir & parent dir
      continue;
    }
    
    $full_path = BACKUP_PATH. "/".$files[$i]; 
    unlink($full_path);
    if(!file_exists($full_path)) {
       echo "deleted old file: ".$full_path. "\r\n";
    }
    
  }
}

function init_env(){
  $dir = CURRENT_PATH."/backups";//set path of folder where to save file
  if(!(file_exists($dir))) {
    mkdir($dir, 0777);
  }

}

function can_backup(){
  $files = scandir(BACKUP_PATH, SCANDIR_SORT_DESCENDING);
  $newest_file = $files[0];
  $last_time = filemtime(BACKUP_PATH."/".$newest_file);
  
  if(sizeof( $files) <= 2 )
    return true; //for first
    
  if( (NOW - $last_time) > ( TIME_TO_BACKUP_AGAIN - TIME_BUFFER_FOR_ONE_BACKUP) )
    return true; // have files.
    
  return false;
}




?>




