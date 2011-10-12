<?php
$setup = $_lib['storage']->get_hash(array('query' => $query_setup, 'key' => 'name', 'value' => 'value'));
$factoring_clientID = $setup['factoringID'];

/* Generate KUNDE.DAT (elcon.p) */
Header("Content-Disposition: attachment; filename=\"KUNDE.DAT\"\n");
Header("Content-Type: text/plain\n");

$sql = "SELECT a.AccountPlanID, a.OrgNumber, a.AccountName, a.Address, a.ZipCode, a.City, a.Mobile, a.Country FROM accountplan a, invoiceout i WHERE a.AccountPlanID > 10000 AND a.AccountPlanID < 100000000 AND a.AccountPlanID = i.CompanyID AND YEAR(i.InvoiceDate) = YEAR(NOW()) AND i.CommentCustomer NOT LIKE '%%NOK%%'";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result))
{
	$ekunde = "";
	$ekunde .= str_pad("K94090", 6, " ", STR_PAD_RIGHT);		//K94090
	$ekunde .= str_pad($factoring_clientID, 3, "0", STR_PAD_RIGHT);		//Klientnr
	$ekunde .= str_pad($row["AccountPlanID"], 9, "0", STR_PAD_LEFT);			//Kundenr
	$ekunde .= str_pad($row["OrgNumber"], 11, "0", STR_PAD_RIGHT);			//Orgnr
	$ekunde .= substr(str_pad($row["AccountName"], 55, " ", STR_PAD_RIGHT),0,55);			//Kundenavn
	$ekunde .= substr(str_pad($row["Address"], 30, " ", STR_PAD_RIGHT),0,30);			//Addresse
	$ekunde .= str_pad($row["ZipCode"], 4, "0", STR_PAD_RIGHT);			//Postnr
	$ekunde .= str_pad($row["City"], 23, " ", STR_PAD_RIGHT);			//Sted
	$ekunde .= str_pad("", 20, " ", STR_PAD_RIGHT);			//Tomt felt
	$ekunde .= str_pad(str_replace(" ","",$row["Mobile"]), 11, " ", STR_PAD_RIGHT);			//Telefon
	$ekunde .= str_pad("", 21, " ", STR_PAD_RIGHT);			//Tomt felt
	if($row["country"] == "Norge")
	$ekunde .= str_pad("NO", 2, " ", STR_PAD_RIGHT);			//Landkode 
	else
	$ekunde .= "  ";
	echo($ekunde."\r\n");
}
