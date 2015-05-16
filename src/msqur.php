<?php

require "config.php";
require "db.php";
require "ini.php";
require "msq.php";

/*
 * @brief Public API here?
 * 
 * Defines the actions taken at the user level:
 * upload
 * browse
 * view
 * etc.
 * 
 * @see http://www.aljtmedia.com/blog/creating-a-php-rest-routing-class-for-your-application/
 */
class Msqur
{
	private $db;
	
	function __construct()
	{
		$this->db = new MsqurDB(); //TODO check reuse
	}
	
	public function getMSQ($id)
	{
		//TODO hrm
		return $this->db->getMSQ($id);
	}
	
	public function addMSQs($files, $engineid)
	{
		$fileList = array();
		
		foreach ($files as $file)
		{
			//echo 'Adding ' . $file['tmp_name'];
			//TODO if -1 failed
			$fileList[] = $this->db->addMSQ($file, $engineid);
		}
		
		return $fileList;
	}
	
	public function header() { include "view/header.php"; }
	public function footer() { include "view/footer.php"; }
	
	public function splash()
	{
		$this->header();
		include "view/splash.php";
		$this->footer();
	}
	
	public function browse($page = 0)
	{
		return $this->db->browse($page);
	}
	
	/**
	 * get html from md id
	 * if msq xml not cached,
	 * parse xml and update engine
	 * else if cached just return html
	 */
	public function view($id)
	{
		$this->header();
		if (DEBUG) echo '<div class="debug">Load MSQ: ' . $id . '</div>';
		//Get cached HTML and display it, or reparse and display (in order)
		$html = $this->getMSQ($id);
		if ($html !== null)
		{
			$this->db->updateViews($id);
			$msq = new MSQ(); //ugh
			
			if ($html == FALSE)
			{
				$engine = array();
				$metadata = array();
				$xml = $this->db->getXML($id);
				$groupedHtml = $msq->parseMSQ($xml, $engine, $metadata);
				$this->db->updateMetadata($id, $metadata);
				$this->db->updateEngine($id, $engine);
				
				$html = "";
				foreach($groupedHtml as $group => $v)
				{
					//TODO Group name as fieldset legend or sth
					$html .= "<div class=\"group-$group\">";
					$html .= $v;
					$html .= '</div>';
				}
				
				$this->db->updateCache($id, $html);
			}
		}
		//TODO else show 404
		
		echo $html;
		$this->footer();
	}
	
	public function addEngine($make, $code, $displacement, $compression, $turbo)
	{
		return $this->db->addEngine($make, $code, $displacement, $compression, $turbo);
	}
}

$msqur = new Msqur();

?>
