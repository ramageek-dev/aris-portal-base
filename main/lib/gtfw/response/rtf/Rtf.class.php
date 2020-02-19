<?php

/*
@author : Dyan Galih Nugroho Wicaksi
@2009
@version : 0.1
*/

class Rtf
{
	
	private $contents;
	
	private $fileName;
	
	private $cells;
	
	private $dynRow;
	
	private $htmlOnly = false;
	function __construct() 
	{
	}
	
	public function SetFileName($fileName) 
	{
		$this->fileName = $fileName;
	}
	
	public function SetContent($contents) 
	{
		$this->contents = file_get_contents($contents);
	}
	
	public function GetContent() 
	{
		
		return $this->contents;
	}
	
	public function AddVar($parameter, $value) 
	{
		$this->contents = str_replace('[' . $parameter . ']', $value, $this->contents);
	}
	
	public function AddVars($parameter, $arrValues) 
	{
		$newValues = '';
		$arrValues = array_values($arrValues);
		
		for ($i = 0;$i < count($arrValues);$i++) 
		{
			$newValues.= '\rtlch ' . $arrValues[$i] . '\par';
		}
		$newValues = $this->RemoveLastPar($newValues);
		$this->AddVar($parameter, $newValues);
	}
	
	public function RemoveLastPar($str) 
	{
		$str = substr_replace($str, '', strlen($str) - 4);
		
		return $str;
	}
	
	public function SetCell($arrCell) 
	{
		
		for ($i = 0;$i < count($arrCell);$i++) 
		{
			
			if (strpos($this->contents, $arrCell[$i])) 
			{
				$this->cells[] = $arrCell[$i];
			}
		}
	}
	
	public function SetDataRowFromCell() 
	{
		
		for ($i = 0;$i < count($this->cells);$i++) 
		{
			$this->dynRow.= '\intbl {[' . $this->cells[$i] . ']} \cell' . "\n\r";
		}
		$this->dynRow.= '\row\pard' . "\r\n";
	}
	
	public function Dwrite($parameter, $value) 
	{
		$this->dynRow = str_replace('[' . $parameter . ']', $value, $this->dynRow);
	}
	
	public function ParseDynRow($parameter) 
	{
		$this->AddVar($parameter, $this->dynRow);
	}
	
	public function PrintRtf() 
	{
		$this->SetHeader();
		print $this->GetContent();
	}
	
	public function SetHeader() 
	{
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=" . $this->fileName);
		header("Content-length: " . strlen($this->GetContent()));
	}
}
?>
