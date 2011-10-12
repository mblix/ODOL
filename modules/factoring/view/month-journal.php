<?php
includelogic('kid/kid');
$query_setup    = "select name, value from setup";
$setup = $_lib['storage']->get_hash(array('query' => $query_setup, 'key' => 'name', 'value' => 'value'));
$factoring_clientID = $setup['factoringID'];

$company_sql = "SELECT * FROM company WHERE CompanyID = 1";
$company_res = $_lib['db']->db_query($company_sql);
$company = $_lib['db']->db_fetch_assoc($company_res);

$sql = "SELECT
		i.CompanyID,
		i.InvoiceID,
		DATE_FORMAT(i.InvoiceDate,'%d.%m.%y') InvoiceDate,
		DATE_FORMAT(i.DueDate,'%d.%m.%y') DueDate,
		i.TotalCustPrice,
		a.AccountPlanID,
		a.OrgNumber,
		a.AccountName,
		a.Address,
		a.ZipCode,
		a.City,
		a.Mobile
	FROM
		invoiceout i,
		accountplan a
	WHERE
		a.AccountPlanID = i.CompanyID
		AND YEAR(i.InvoiceDate) = YEAR(NOW())
		AND MONTH(i.InvoiceDate) = MONTH(NOW())
		AND CommentCustomer NOT LIKE '%%NOK%%'
	ORDER BY
		InvoiceDate
	";
$result = $_lib['db']->db_query($sql);

$html = "
<html>
<head>
  <style>
    th { text-align: left; padding: 0 10px 0 0px; font-size: 15px; border-bottom: 1px solid black; }
    td { text-align: left; padding: 0 15px 0 0px; font-size: 15px; }
    .number { text-align: right }
    .sum { border-top: 1px solid black; font-weight: bold; }
  </style>
</head>

<body>
";

$html .= sprintf("
<p>
<b>%s</b><br />
<b>%s</b><br />
<b>%s %s</b><br />
<b>Org. nr. %s</b><br />
</p>

<p>
<b>Factoringliste %s</b>
</p>

",
		$company['VName'],
		$company['IAddress'],
		$company['IZipCode'], $company['ICity'],
		$company['OrgNumber'],
		date("d.m.Y"));


$html .= sprintf("
<table>
  <tr>
    <th>Organisasjonsnr.</th>
    <th>Fakturanr.</th>
    <th>Fakturadato</th>
    <th>Forfallsdato</th>
    <th>Kundenr.</th>
    <th>Kundenavn</th>
    <th>Postadresse</th>
    <th>Telefon</th>
    <th>Valuta</th>
    <th class='number'>Bel&oslash;p</th>
  </tr>
");


$sum = 0.0;
while ($row = $_lib['db']->db_fetch_assoc($result))
{
  $sum += $row['TotalCustPrice'];
	$html .= sprintf("
  <tr>
    <td>%s</td>
    <td class='number'>%s</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s, %s %s</td>
    <td>%s</td>
    <td>%s</td>
    <td class='number'>%s</td>
  </tr>
       ", 
	 $row['OrgNumber'],
	 $row['InvoiceID'],
	 $row['InvoiceDate'],
	 $row['DueDate'],
	 $row['CompanyID'],
	 $row['AccountName'],
	 $row['Address'],
	 $row['ZipCode'],
	 $row['City'],
	 $row['Mobile'],
	 "NOK",
	 $row['TotalCustPrice']);

}

$html .= sprintf("<tr><td class='sum'>Sum:</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td class='number sum'>%2.2f</td>", $sum);

$html .= "</table></body>";

echo $html;

