<?
# $Id: list.php,v 1.54 2005/01/30 12:35:03 thomasek Exp $ person_list.php,v 1.3 2001/11/20 18:04:43 thomasek Exp $
# Based on EasyComposer technology
# Copyright Thomas Ekdahl, 1994-2005, thomas@ekdahl.no, http://www.ekdahl.no

global $_dsn, $_SETUP, $_dbh;

$db_table = "leilighet";

$limitSet = $_REQUEST['limit'];
$limitSet = 1;

if(!$CompanyID) { $CompanyID = 1; }

/* S?kestreng */
$selectCompany = "select * from company where CompanyID = '$CompanyID';";
$select = "select * from $db_table ";
?>
<? print $_lib['sess']->doctype ?>
<head>
    <title>Empatix - Borettslag leiligheter</title>
    <meta name="cvs"                content="$Id: list.php,v 1.54 2005/01/30 12:35:03 thomasek Exp $" />
    <? includeinc('head') ?>
</head>

<body>

<? includeinc('top') ?>
<? includeinc('left') ?>
<?
$row_c = $_dbh[$_dsn]->get_row(array('query' => $selectCompany));
?>
<h2><? print "Leiligheter i " . $row_c->VName; ?></h2>
<table class="lodo_data">
<thead>
  <tr>
    <th colspan="6">Leligheter</th>
  <tr>
    <th class="menu">Seksjon Nr</th>
    <th class="menu">Kvadrat</th>
    <th class="menu">AndelTotal</th>
    <th class="menu">BorettInnskudd</th>
    <th class="menu">Husleie</th>
    <th class="menu">Eier</th>
  </tr>
</thead>
<tbody>
<?
$result_l = $_dbh[$_dsn]->db_query($select);
while($row_l = $_dbh[$_dsn]->db_fetch_object($result_l))
{
?>
      <tr class="<? print "$sec_color"; ?>">
          <td><a href="<? print $_SETUP['DISPATCH'] ?>t=borettslag.leilighet&leilighet.LeilighetID=<? print $row_l->LeilighetID ?>"><? print $row_l->Seksjonsnr; ?></a></td>
          <td><? print $row_l->Kvadrat; ?></td>
          <td><? print $row_l->AndelTotal; ?></td>
          <td><? print $row_l->BorettInnskudd; ?></td>
          <td><? print $row_l->Husleie; ?></td>
<?
// Finn nåværende eier
$selectEier = "select a.AccountName from eierforhold e, accountplan a where e.AccountPlanID = a.AccountPlanID and e.LeilighetID = '" . $row_l->LeilighetID . "' and e.TilDato = '0000-00-00' order by FraDato desc;";
$result_e = $_dbh[$_dsn]->db_query($selectEier);
?>
          <td><a href="<? print $_SETUP['DISPATCH'] ?>t=borettslag.leiligheteier&eierforhold.LeilighetID=<? print $row_l->LeilighetID ?>">
          <? if ( $_dbh[$_dsn]->db_numrows($result_e) == 0) print "Ingen eier"; while($row_e = $_dbh[$_dsn]->db_fetch_object($result_e)) print $row_e->AccountName; ?></a></td>
    </tr>
    <?
}
?>
</tbody>
<tfoot>
  <tr class="BGColorDark">
    <td align="right" colspan="3">
        &nbsp;
    </td>
    <td align="right" colspan="3">
        <!-- <a href="<? print $_SETUP['DISPATCH'] ?>t=borettslag.leilighet&new=1&">Ny leilighet</a> -->
        <input type="button" value="Ny leilighet" name="action_leiligheter_new" tabindex="2" onClick="document.location='<? print $_SETUP['DISPATCH'] ?>t=borettslag.leilighet';">
    </td>

</tfoot>
</table>

</body>
</html>


