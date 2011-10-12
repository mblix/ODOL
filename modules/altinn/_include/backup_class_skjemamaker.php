<?php
class skjemaMaker
{
	var $skjemaFormat = array();
	var $skjemaXML;
	var $data;
	var $parentCollection;
	var $outputXml;
	var $repeatGroup;
	var $skjemaRoot;
	var $servletURL;
	function skjemaMaker($params)
	{
		setlocale(LC_ALL, 'no_NO.ISO8859-1');
		$this->db = $params["db"];
		if ($params["servlet"] == "")
			$this->servletURL = "http://konsulentvikaren.no:8080/axis/AltinnReceipt.jws";
		else
			$this->servletURL = $params["servlet"];
		if ($params["filename"] != "")
		{
			$format = new xmlFormat($params);
			$this->skjemaXML = new xmlSkjema($params);
		}
		else if ($params["fagsystemid"] != "")
		{
			$query = "select * from altinn_skjema where SkjemaNr = '" . $params["fagsystemid"] . "';";
			$rs = $this->db->Query( $query );
			$row = $this->db->NextRow( $rs );
			$format = new xmlFormat(array('xmldata' => $row["XsdData"]));
			$this->skjemaXML = new xmlSkjema(array('xmldata' => $row["XsdData"]));
		}
		$this->skjemaFormat = $format->getFormat();
		$this->childCollection = new childKeeper($this->skjemaXML);
		$this->outputXml = new DOMDocument('1.0', 'iso-8859-1');
		
	}
	function addData($data)
	{
		$this->data = $data;
	}
	function addDataAsString($dataStr)
	{
		$liste = split("&", $dataStr);
		foreach($liste as $linje)
		{
			list($key, $value) = split("=", $linje);
			$key = urldecode($key);
			$value = urldecode($value);
			if($this->data[$key] != "" || is_array($this->data[$key]))
			{
				if($this->data[$key] != "" &&  !is_array($this->data[$key]))
				{
					$this->data[$key][] = $this->data[$key];
				}
				$this->data[$key][] = $value;
			}
			else
				$this->data[$key] = $value;
		} 
	}
	function xmlescape($data)
	{
		$position=0;
		$length=strlen($data);
		$escapeddata="";
		for(;$position<$length;)
		{
			$character=substr($data,$position,1);
			$code=Ord($character);
			switch($code)
			{
				case 34:
					$character="&quot;";
					break;
				case 38:
					$character="&amp;";
					break;
				case 39:
					$character="&apos;";
					break;
				case 60:
					$character="&lt;";
					break;
				case 62:
					$character="&gt;";
					break;
				default:
					if($code<32 || $code>127)
						$character=("&#".strval($code).";");
					break;
			}
			$escapeddata.=$character;
			$position++;
		}
		return $escapeddata;
	}

	function makeSkjema()
	{
		$this->skjemaXML->rewindElement();
		$this->skjemaXML->nextElement();
		$child = $this->skjemaXML->getElement();
		$this->skjemaroot = $this->outputXml->CreateElement($child);
		$this->skjemaXML->firstAttrib();
		$attribName = $this->skjemaXML->getAttrib("name");
		// XSD in DB has UTF-8 encoding, so we convert to iso-8859-1 when getting values
		$attribFValue = $this->xmlescape(iconv("UTF-8", "ISO-8859-1", $this->skjemaXML->getAttrib("fixed")));
		//print "Name: -" . $attribName . "- Value: " . $attribFValue . "<br>";
		if ($attribFValue != "")
			$this->skjemaroot->setAttribute($attribName, $attribFValue);
		else
		{
			$enum = $this->skjemaXML->getOptions();
			if (count($enum) > 0)
				$this->skjemaroot->setAttribute($attribName, $this->xmlescape(iconv("UTF-8", "ISO-8859-1", $enum[0])));
		}
		
		while($this->skjemaXML->nextAttrib())
		{
			$attribName = $this->skjemaXML->getAttrib("name");
			$attribFValue = $this->skjemaXML->getAttrib("fixed");
 			print "Name: -" . $attribName . "- Value: " . $attribFValue . "<br>";
			
			if ($attribName == "etatid")
					$attribFValue = "974761076";
			if ($attribName == "titxyz")
					$attribFValue = "";
			else
			{
				if ($attribFValue != "")
					$this->skjemaroot->setAttribute($attribName, $attribFValue);
				else
				{
					$enum = $this->skjemaXML->getOptions();
					print_r($enum);
					if (count($enum) > 0)
						$this->skjemaroot->setAttribute($attribName, $this->xmlescape(iconv("UTF-8", "ISO-8859-1", $enum[0])));
				}
			}	
		}
				
		$nodes = $this->makeGroup($child);
		for($j = 0; $j < count($nodes); $j++)
			$this->skjemaroot->appendChild($nodes[$j]);
		
	}
	function makeOr($name, $index = 0)
	{
		$this->skjemaXML->rewindElement();
		while ($foundElement != $name)
		{
			$this->skjemaXML->NextElement();
			$foundElement = $this->skjemaXML->getElement();
		}
		$orid = $this->skjemaXML->getOrid();
		if (!is_array($this->data["D". $orid]))
		{
			$value = $this->data["D". $orid];
		}
		else
		{
			$value = $this->data["D". $orid][$index];
			if (count($this->data["D". $orid]) - $index > 1)
				$this->repeatGroup = true;
		}
		
		$f = $this->skjemaXML->getFormat();
		if ($value != "")
		{
			if (strtolower($value) == "ja" || strtolower($value) == "nei")
			{
				$value = ucfirst(strtolower($value));
			}
			$value_ok = true;
			if (is_array($this->skjemaFormat[$f]["enumerators"]))
			{
				$found = false;
				foreach ($this->skjemaFormat[$f]["enumerators"] as $allowedValue)
				{
					if (strtolower($allowedValue) == strtolower($value))
					{
						$value = $allowedValue;
						$found = true;
					}
				}
				if ($found == false)
				{
					$value_ok = false;
				}
			}
			
			if ($this->skjemaFormat[$f]["varType"] == "string")
			{
				if ($this->skjemaFormat[$f]["maxLength"] != "" && $this->skjemaFormat[$f]["maxLength"] < strlen($value))
				{
					$value_ok = false;
				}
				if ($this->skjemaFormat[$f]["minLength"] != "" && $this->skjemaFormat[$f]["minLength"] > strlen($value) )
				{
					$value_ok = false;
				}
				if ($this->skjemaFormat[$f]["length"] != "" && $this->skjemaFormat[$f]["length"] != strlen($value) )
				{
					$value_ok = false;
				}
				if ($this->skjemaFormat[$f]["pattern"] != "" && !ereg($this->skjemaFormat[$f]["pattern"], $value) )
				{
					$value_ok = false;
				}
			
			}
			if ($this->skjemaFormat[$f]["varType"] == "integer")
			{
				if (!is_int($value) )
				{
					$value_ok = false;
				}
				if ($this->skjemaFormat[$f]["totalDigits"] != "" && $this->skjemaFormat[$f]["totalDigits"] < strlen("" . $value) )
				{
					$value_ok = false;
				}
			}
			if ($this->skjemaFormat[$f]["varType"] == "decimal")
			{
				if (!is_numeric($value) )
				{
					$value_ok = false;
				}
				if ($this->skjemaFormat[$f]["totalDigits"] != "" && $this->skjemaFormat[$f]["totalDigits"] < strlen("" . $value) )
				{
					$value_ok = false;
				}
				if ($this->skjemaFormat[$f]["pattern"] != "" && !ereg($this->skjemaFormat[$f]["pattern"], $value) )
				{
					$value_ok = false;
				}
			}
			if ($this->skjemaFormat[$f]["varType"] == "date")
			{
				list($y, $m, $d) = split("-", $value);
				if (!checkdate ( $m, $d, $y) )
				{
					$value_ok = false;
				}
			}
			if ($this->skjemaFormat[$f]["varType"] == "gYear")
			{
				if ($value > 1970 && $value < 2050)
				{
					$value_ok = false;
				}
			}
			if ($value_ok)
			{
print "***DEBUG orid " . $this->skjemaXML->getOrid() . "=" . $value . "\n<br>";
				$cdatanode = $this->outputXml->createCDATASection(iconv("UTF-8", "ISO-8859-1", $value));
				//$node = $this->outputXml->CreateElement($foundElement, iconv("UTF-8", "ISO-8859-1", $value));
				$node = $this->outputXml->CreateElement($foundElement);
				$node->appendChild($cdatanode);

				$node->setAttribute("orid", $this->skjemaXML->getOrid());
			}
			else
				$this->errorMessage .= "Feil validering av variablen: " . $foundElement . " \n";
		}
		return $node;
	}
	function makeGroup($parent)
	{
		set_time_limit(30);
		
		$i = 0;
		$this->repeatGroup = false;
		do
		{
			$myChildren = $this->childCollection->findChildren($parent);
			$this->skjemaXML->rewindElement();
			$counter = 0;
			foreach ($myChildren as $child)
			{
				$this->skjemaXML->rewindElement();
				while ($foundElement != $child && $counter < (count($myChildren) * 2))
				{
					if ($this->skjemaXML->NextElement())
						$foundElement = $this->skjemaXML->getElement();
				}
				// print "Child: " . $child . " - " . $foundElement . "<br>";
				
				if ($this->skjemaXML->isValue()) // Hvis det er en ORID verdi
				{ // Det er en ORID
					$myNode = $this->makeOr($foundElement);
					if (!is_null($myNode))
					{ 
//						print "Jippi! Klarte &aring; lage denne: " . $foundElement . "<br>";
						$nodes[$i] = $myNode;
						$i++;
					}
//					else print "Klarte ikke &aring; lage denne: " . $foundElement . "<br>";
					// print "Må være en ORID<br>";
				}
				else
				{ // Det må være en gruppe.
					$gruppeid = $this->skjemaXML->getGruppeid();
					// Lag alle undergrupper!
					$n = $this->makeGroup($child);
					if (count($n) != 0)
					{
						$nodes[$i] = $this->outputXml->CreateElement($child);
						$nodes[$i]->setAttribute("gruppeid", $gruppeid);
						for($j = 0; $j < count($n); $j++)
							$nodes[$i]->appendChild($n[$j]);
						$i++;
					}
				}
				// Legg gruppene inn under denne noden.
	
			}
		}
		while($this->repeatGroup);
		// $this->outputXml
		//$time_end = microtime_float();
		//$time = $time_end - $time_start;
		//print "Gruppen " . $parent . " tok " . number_format($time, 2, ',', ' ') . "sekunder &aring; kj&oslash;re<br>";
		return $nodes;
	}
	function getXML()
	{
		$this->outputXml->appendChild($this->skjemaroot);
		print "\nReplacing amps: " . str_replace("&amp;#", "&#", $this->outputXml->saveXML()) . "\n\n";
		return str_replace("&amp;#", "&#", $this->outputXml->saveXML());
	}
	function getErrors()
	{
		return $this->errorMessage;
	}
	function makeDataUnit($inputData)
	{
		$dunit = $this->outputXml->CreateElement("DataUnit");
		$dunit->setAttribute("participantId", $inputData["orgnr"]);
		$dunit->setAttribute("sendersReference", $inputData["sendersRef"]);
		if ($inputData["parentsRef"] != "")
		$dunit->setAttribute("parentReference", $inputData["parentsRef"]);
		if ($inputData["completed"] != "")
			$dunit->setAttribute("completed", $inputData["completed"]);
		else
			$dunit->setAttribute("completed", "0");
		if ($inputData["locked"] != "")
			$dunit->setAttribute("locked", $inputData["locked"]);
		else
			$dunit->setAttribute("locked", "0");
		$dunit->appendChild($this->skjemaroot);
		$this->skjemaroot = $dunit;
	}
	function makeDataUnits($inputData)
	{
		$dunits = $this->outputXml->CreateElement("DataUnits");
		$dunits->appendChild($this->skjemaroot);
		$this->skjemaroot = $dunits;
	}
	function makeDataBatch($inputData)
	{
		$dbatch = $this->outputXml->CreateElement("DataBatch");
		$dbatch->setAttribute("schemaVersion", "1.1");
		$dbatch->setAttribute("batchId", $inputData["batchId"]);
		$dbatch->setAttribute("enterpriseSystemId", $inputData["systemID"]);
		$dbatch->setAttribute("receiptType", "OnDemand");
		$dbatch->setAttribute("receiptUrl", $this->servletURL);
		$dbatch->appendChild($this->skjemaroot);
		$this->skjemaroot = $dbatch;
	}
	function makeEnvelope($inputData)
	{
		$this->makeDataUnit($inputData);
		$this->makeDataUnits($inputData);
		$this->makeDataBatch($inputData);
	}
}
?>
