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
printf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\r\n",

				$factoring_clientID, 	// 1: Annn
				$row["AccountPlanID"], 	// 2: Kundenr
				$row["AccountName"],	// 3: Navn
				$row["Address"], 	// 4: Addresse1
				NULL,			// 5: Addresse2
				NULL,			// 6: Addresse3
				$row["ZipCode"],	// 7: Postkode
				$row["City"],		// 8: Poststed
				$row["Mobile"],		// 9: Telefon
				NULL,			// 10: Telefaks
				$row["OrgNumber"],	// 11: Foretaksnummer
				NULL,			// 12: Firma/privat
				NULL,			// 13: Bankkonto
				"NO",			// 14: Landkode
				NULL,			// 15: Språkkode
				NULL,			// 16: Valutakode
				NULL,			// 17: e-postaddresse
				$row["AccountName"]	// 18: alfasøkefelt	
				
	);

}
