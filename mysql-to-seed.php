#!/usr/bin/php
<?php
mb_internal_encoding("UTF-8");
$shortopts  = "h:u:p:d:s:";
$opts = getopt($shortopts);
if (!isset($opts['h']) || !isset($opts['u']) || !isset($opts['p']) ||
	!isset($opts['d']) || !isset($opts['s'])){
	die("Parameter missing\nUsage: ./database-to-seed -h hostname -u username -p password -d database -s schema\n");
}
$conn =  mysql_connect($opts['h'],$opts['u'],$opts['p']) or die("[ERROR] Unable to connect to database\n" . mysql_error());
mysql_select_db($opts['d'],$conn);
$schema = include($opts['s']);
foreach ($schema as $tableName => $columns){
	$sql = "SELECT * FROM ".mysql_real_escape_string($tableName);
	$result = mysql_query($sql,$conn);
	if (!$result){
		echo("The table ".$tableName." is not present in the database and will be ignored\n");	
	} else {
		echo("Processing the table ".$tableName."\n");	
		$filePath = "seed/".snake_to_camel($tableName)."TableSeeder.php";
		$file = fopen($filePath, "w");
		$firstRow = ignore_timestamps(mysql_fetch_assoc($result));
		fwrite($file,get_head($tableName));
		if($firstRow){
			print_comparison(array_keys($firstRow),$columns);
			mysql_data_seek($result, 0);
			while($row = ignore_timestamps(mysql_fetch_assoc($result))) {
				$newRow = [];
				foreach($columns as $field){
					$newRow[$field] = isset($row[$field]) ? $row[$field] : "";
				}
				$line = format_row($newRow);
				fwrite($file,$line);
			}
		}
		fwrite($file,get_tail());
		echo("File ".$filePath." created\n");	
		fclose($file);
	}
}
mysql_close($conn);

function print_comparison($array1,$array2){
	$result = "[";
	$ignored = array_diff($array1,$array2);
	foreach ($ignored as $field){
		$result .= "\033[31m-".$field.",";
	}
	$added = array_diff($array2,$array1);
	foreach ($added as $field){
		$result .= "\033[32m+".$field.",";
	}
	$imported = array_intersect($array2,$array1);
	foreach ($imported as $field){
		$result .= "\033[34m".$field.",";
	}
	$result = mb_substr($result, 0, -1);
	$result .= "\033[0m]";
	echo($result."\n");
}

function ignore_timestamps($row){
	unset($row['created_at']);
	unset($row['updated_at']);
	unset($row['deleted_at']);
	return $row;
}
function snake_to_camel($val) {  
	return str_replace(' ', '', ucwords(str_replace('_', ' ', $val)));  
}  
function get_head($tableName){
	$result = "<?php\n\nclass ".snake_to_camel($tableName)."TableSeeder extends Seeder {\n";
	$result .="  public function run() {\n    DB::table('".$tableName."')->insert([\n";
	return $result;
}
function get_tail(){
  	$result = "    ]);\n  }\n}";
	return $result;
}
function format_row($row){
	$result = "      [";
	foreach($row as $key => $value){
		if ($key == 'timestamps'){
			$result .= "'created_at' => new DateTime, 'updated_at' => new DateTime,";
		} else {
			$result .= "'".$key."' => '".utf8_encode($value)."',";
		}
	}
	$result = mb_substr($result, 0, -1);
	$result .= "],\n";
	return $result;
}
