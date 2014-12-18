<?php
function connect()
{
	$db = null;
	try
	{
		$db = new PDO("mysql:dbname=msqur;host=localhost", "msqur", "LwEYrxvUpjhnCdTc");
		//echo "Connected";
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error connecting to database.</div>'; //$e->getMessage();
		$db = null; //Redundant.
	}
	
	return $db;
}

function addFiles($files)
{
	$db = connect();
	if ($db == null) return null;
	
	$fileList = array();
	
	foreach ($files as $file)
	{
		//echo 'Adding ' . $file['tmp_name'];
		$fileList[] = addFile($file);
	}
	
	return $fileList;
}

function addFile($file, $db = null)
{
	if ($db == null)
	{
		$db = connect();
		if ($db == null) return null;
	}
	
	try
	{
		//TODO Compress?
		$st = $db->prepare("INSERT INTO msqs (xml) VALUES (:xml)");
		$xml = file_get_contents($file['tmp_name']);
		//Convert encoding to UTF-8
		$xml = mb_convert_encoding($xml, "UTF-8");
		//Strip out invalid xmlns
		$xml = preg_replace('/xmlns=".*?"/', '', $xml);
		$st->bindParam(":xml", $xml);
		$st->execute();
		$id = $db->lastInsertId();
		$st = $db->prepare("INSERT INTO metadata (url,msq,fileFormat,signature) VALUES (:url, :id, '4.0', 'unknown')");
		$st->bindParam(":url", $id); //could do hash but for now, just the id
		$st->bindParam(":id", $id);
		$st->execute();
		$id = $db->lastInsertId();
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error adding to the database.</div>'; echo $e->getMessage();
	}
	
	return $id;
}

function getMSQ($id)
{
	$db = connect();
	if ($db == null) return null;
	
	try
	{
		$st = $db->prepare("SELECT msqs.xml FROM msqs INNER JOIN metadata ON metadata.msq = msqs.id WHERE metadata.id = :id");
		$st->bindParam(":id", $id);
		$st->execute();
		$result = $st->fetch(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error executing database query.</div>'; echo $e->getMessage();
	}
	
	if (!$result) return null;
	else return $result['xml'];
}

//TODO Rename?
function getAll()
{
	$db = connect();
	if ($db == null) return null;
	
	try
	{
		$st = $db->prepare("SELECT * FROM metadata");
		$st->execute();
		$result = $st->fetchAll(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e)
	{
		echo '<div class="error">Error executing database query: </div>'; echo $e->getMessage();
	}
	
	if (!$result) return null;
	else return $result;
}

?>
