<?php
includelogic('kid/kid');
$query_setup    = "select name, value from setup";
$setup = $_lib['storage']->get_hash(array('query' => $query_setup, 'key' => 'name', 'value' => 'value'));
$factoring_clientID = $setup['factoringID'];


/* elcon.p format for invoice / faktura */
Header("Content-Disposition: attachment; filename=\"FAKTURA.DAT\"\n");
Header("Content-Type: text/plain\n");

$sql = "SELECT CompanyID, InvoiceID, DATE_FORMAT(InvoiceDate,'%y%m%d') InvoiceDate, DATE_FORMAT(DueDate,'%y%m%d') DueDate, TotalCustPrice FROM invoiceout WHERE YEAR(invoiceout.InvoiceDate) = YEAR(NOW()) AND MONTH(invoiceout.InvoiceDate) = MONTH(NOW()) AND CommentCustomer NOT LIKE '%%NOK%%' AND InvoiceID >= '".$setup['factoringStartInvoiceId']."'";

$result = $_lib['db']->db_query($sql);

while ($row = $_lib['db']->db_fetch_assoc($result))
{
	$elconline = "";

	/* 01-03 Klientnr */
	$elconline .= str_pad($factoring_clientID, 3, "0", STR_PAD_LEFT);
	/* 04-09 Klients kundenr */
	$elconline .= str_pad($row["CompanyID"], 6, "0", STR_PAD_LEFT);
	/* 10-15 Fakturanr */
	$elconline .= str_pad($row["InvoiceID"], 6, "0", STR_PAD_LEFT);
	/* 16-21 Fakturadato */
	$elconline .= str_pad($row["InvoiceDate"], 6, "0", STR_PAD_LEFT);
	/* 22-27 Forfallsdato */
	$elconline .= str_pad($row["DueDate"], 6, "0", STR_PAD_LEFT);
	/* 28-29 Avdeling */
	$elconline .= str_pad(NULL, 2, "0", STR_PAD_LEFT);
	/* 30-33 Buntnr */
	$elconline .= str_pad(NULL, 6, "0". STR_PAD_LEFT);
	/* 40-41 Bilagskode (21=faktura, 22=kreditnota) */
        if (intval($price) < 0)
        {
                /* 9 Kreditnota */
                $elconline .= "229";
                $price = intval($price)*-1;
        }
        else
        {
                /* 1 Faktura */
                $elconline .= "21";
        }
	/* 42-52 Fakturabeløp */
	$elconline .= str_pad($price, 11 ,"0", STR_PAD_LEFT);
	/* 53-68 KID (16) */
	$kidlogic = new lodo_logic_kid();
	$factoringid = "90".$setup['factoringID'].str_pad($InvoiceID, 8, "0", STR_PAD_LEFT)."00";
	$kid = $factoringid.$kidlogic->gen_value_checksum($factoringid);
	/* 69-74 Referansenr (opprinnelig faktura) (6) */
	$elconline .= str_pad($row["InvoiceID"], 6, "0", STR_PAD_LEFT);
	/* 75-78 Rabattprosent (2) */
	$elconline .= str_pad("00", 2, "0", STR_PAD_LEFT);
	/* 79-84 Dato Rab Forfall Å M D (6) */
	$elconline .= str_pad($row["InvoiceDate"], 6, "0", STR_PAD_LEFT);
	/* 85-86 Landkode (2) */
	$elconline .= str_pad("NO", 2, "0", STR_PAD_LEFT);
	/* 87-89 Valuta (3) */
	$elconline .= str_pad("NOK", 3, "0", STR_PAD_LEFT);
	/* 90-128 Filler, blank (39) */
	$elconline .= str_pad(" ", 39, "0", STR_PAD_LEFT);
	echo($elconline."\r\n");
}

