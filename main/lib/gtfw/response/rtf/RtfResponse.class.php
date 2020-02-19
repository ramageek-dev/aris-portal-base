<?php
/*
	@author : Dyan Galih Nugroho Wicaksi
	@2009
*/

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/rtf/Rtf.class.php';

abstract class RtfResponse implements ResponseIntf
{
	
	protected $rtf;
	
	function __construct() 
	{
	}

	function GetFileName()
	{
		return 'filename.rtf';
	}
	
	function &GetHandler() 
	{
		return $this;
	}
	
	function Send() 
	{
		$this->rtf = new Rtf();
		
		$this->rtf->SetFileName($this->GetFileName());
		$this->ProcessRequest();
		$this->rtf->PrintRtf();
	}
}
?>
