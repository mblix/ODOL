<?php
$query_setup    = "select name, value from setup";
$setup = $_lib['storage']->get_hash(array('query' => $query_setup, 'key' => 'name', 'value' => 'value'));
$factoring_clientID = $setup['factoringID'];

/* Generate KUNDE.DAT (elcon.p) */
Header("Content-Disposition: attachment; filename=\"KUNDE.DAT\"\n");
Header("Content-Type: text/plain\n");

$sql = "SELECT a.AccountPlanID, a.OrgNumber, a.AccountName, a.Address, a.ZipCode, a.City, a.Mobile, a.CountryCode FROM accountplan a, invoiceout i WHERE a.AccountPlanID > 10000 AND a.AccountPlanID < 100000000 AND a.AccountPlanID = i.CompanyID AND YEAR(i.InvoiceDate) = YEAR(NOW()) AND i.CommentCustomer NOT LIKE '%%NOK%%' AND InvoiceID >= '".$setup['factoringStartInvoiceId']."'";
$result = $_lib['db']->db_query($sql);

while ($row = $_lib['db']->db_fetch_assoc($result))
{
printf("V%s;%s;%s;%s;%s;%s %s;%s;%s;%s;%s;%s;%s;%s;%s\r\n",

				$factoring_clientID, 	// 1: Vnnn
				$row["AccountPlanID"], 	// 2: Kundenr
				$row["AccountName"],	// 3: Navn
				$row["Address"], 	// 4: Addresse1
				NULL,			// 5: Addresse2
				$row["ZipCode"],	// 6: Postkode
				$row["City"],		// 7: Poststed
				$row["Mobile"],		// 8: Telefon
				NULL,			// 9: Telefaks
				$row["OrgNumber"],	// 10: Foretaksnummer
				"F",			// 11: Firma/privat
				NULL,			// 12: Bankkonto
				$row["CountryCode"],	// 13: Landkode
				NULL,			// 14: e-postaddresse
				$row["AccountName"]	// 15: alfasøkefelt	
				
	);

}
