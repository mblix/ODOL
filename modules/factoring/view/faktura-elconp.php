<?php
$query_setup    = "select name, value from setup";
$setup = $_lib['storage']->get_hash(array('query' => $query_setup, 'key' => 'name', 'value' => 'value'));
$factoring_clientID = $setup['factoringID'];

/* elcon.p format for invoice / faktura */
Header("Content-Disposition: attachment; filename=\"FAKTURA.DAT\"\n");
Header("Content-Type: text/plain\n");

$sql = "SELECT CompanyID, InvoiceID, DATE_FORMAT(InvoiceDate,'%y%m%d') InvoiceDate, DATE_FORMAT(DueDate,'%y%m%d') DueDate, TotalCustPrice FROM invoiceout WHERE YEAR(invoiceout.InvoiceDate) = YEAR(NOW()) AND MONTH(invoiceout.InvoiceDate) = MONTH(NOW()) AND CommentCustomer NOT LIKE '%%NOK%%'";
$result = $_lib['db']->db_query($sql);

while ($row = $_lib['db']->db_fetch_assoc($result))
{
        $elconline = "";

        $price = str_replace(".","",$row["TotalCustPrice"]);
        if (intval($price) < 0)
        {
                /* 9 Kreditnota */
                $elconline .= "9";
                $price = intval($price)*-1;
        }
        else
        {
                /* 1 Faktura */
                $elconline .= "1";
        }

        /* Klientnr */
        $elconline .= str_pad($factoring_clientID, 4, "0", STR_PAD_LEFT);
        /* Kundenr */
        $elconline .= str_pad($row["CompanyID"], 9, "0", STR_PAD_LEFT);
        /* Fakturanr */
        $elconline .= str_pad($row["InvoiceID"], 8, "0", STR_PAD_LEFT);
        /* Fakturadato */
        $elconline .= $row["InvoiceDate"];
        /* Forfallsdato */
        $elconline .= $row["DueDate"];
        /* Fakturabeløp */
        $elconline .= str_pad($price, 11 ,"0", STR_PAD_LEFT);
        echo($elconline."\r\n");
}

