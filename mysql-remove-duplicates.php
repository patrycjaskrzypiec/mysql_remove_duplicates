<?php

/*
This script does not modify your data. Run it:
- via cli - recommended
- in browser (view page source)
and you'll see a query.
*/

// CONFIG START
const DB_HOST = 'localhost';
const DB_USER = 'username';
const DB_PASS = 'password';
const DB_NAME = 'dbname';
const DB_CHARSET = 'utf8mb4';

const TABLE = 'table_name'; // table containing duplicates
const PK = 'primary_key'; // primary key column name, e.g. "id"
const DUPLICATES = 'column_name'; // column containing duplicates

const CREATE_INDEX = true; // display second query for creating unique index
// CONFIG END

$m = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$m->set_charset(DB_CHARSET);

$q = 'SELECT '.DUPLICATES.' AS d, count('.DUPLICATES.') AS c FROM '.TABLE.' GROUP BY '.DUPLICATES.' HAVING count('.DUPLICATES.')>1 ORDER BY count('.DUPLICATES.') DESC';
$r = $m->query($q);

if($r->num_rows==0)
	die(TABLE.'/'.DUPLICATES.' has no duplicates');

echo TABLE.'/'.DUPLICATES.' has '.$r->num_rows." duplicated value\n\nexamples:\n";

$n = 0;
$pk_in = '';
$duplicates_in = '';
while($ro = $r->fetch_object())
{
	if(++$n<10)
		echo $ro->c.' / '.$ro->d."\n";

	$q2 = 'SELECT '.PK.' AS pk FROM '.TABLE.' WHERE '.DUPLICATES.'=\''.addslashes($ro->d).'\' ORDER BY id LIMIT 1';
	$r2 = $m->query($q2);

	while($ro2 = $r2->fetch_object())
		$pk_in .= '\''.addslashes($ro2->pk).'\',';

	$duplicates_in .= '\''.addslashes($ro->d).'\',';
}

$q = 'DELETE FROM '.TABLE.' WHERE '.PK.' NOT IN('.mb_substr($pk_in,0,-1).') AND '.DUPLICATES.' IN ('.mb_substr($duplicates_in,0,-1).');';
if(CREATE_INDEX===true)
	$q .= "\n".'CREATE UNIQUE INDEX '.DUPLICATES.' ON '.TABLE.' ('.DUPLICATES.');';

echo "\nbackup your table (!) and run this:\n\n".$q."\n";

?>