    <? print $_lib['sess']->doctype ?>
<head>
        <title>Empatix - <? print $_lib['sess']->get_companydef('CompanyName') ?> : <? print $_lib['sess']->get_person('FirstName') ?> <? print $_lib['sess']->get_person('LastName') ?> - Factoring filer</title>
        <? includeinc('head') ?>
    </head>
<body>

<? includeinc('top') ?>
<? includeinc('left') ?>

<p><strong>Datafiles for this month</strong></p>
<p>SG Finans / Elcon: <a href="<?php echo($_lib['sess']->dispatch) ?>t=factoring.kunde-elconp">KUNDE.DAT</a></p>
<p>SG Finans / Elcon: <a href="<?php echo($_lib['sess']->dispatch) ?>t=factoring.faktura-elconp">FAKTURA.DAT</a></p>
<p>Glitnir / Factonor: <a href="<?php echo($_lib['sess']->dispatch) ?>t=factoring.kunde-factonor">KUNDE.DAT</a></p>
<p>Glitnir / Factonor: <a href="<?php echo($_lib['sess']->dispatch) ?>t=factoring.faktura-factonor">FAKTURA.DAT</a></p>
