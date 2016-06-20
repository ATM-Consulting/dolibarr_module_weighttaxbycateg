<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/weighttaxbycateg.php
 * 	\ingroup	weighttaxbycateg
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
require '../config.php';

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/weighttaxbycateg.lib.php';
dol_include_once('/core/class/html.form.class.php');
dol_include_once('/core/lib/functions.lib.php');
dol_include_once('/societe/class/societe.class.php');
dol_include_once('/categories/class/categorie.class.php');
dol_include_once('/product/class/product.class.php');

// Translations
$langs->load("weighttaxbycateg@weighttaxbycateg");

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

$TCategsAndExcludedThird = unserialize($conf->global->WTBC_CATEGS_AND_EXCLUDED_THIRD);

if ($action === 'addCateg' && !empty($_REQUEST['fk_categ']) && !empty($_REQUEST['fk_product']))
{
	$TCateg = array();
	if(!empty($TCategsAndExcludedThird['TCategs'])) $TCateg = $TCategsAndExcludedThird['TCategs'];
	$TCateg[$_REQUEST['fk_categ']] = $_REQUEST['fk_product'];
	$TCategsAndExcludedThird['TCategs'] = $TCateg;
	dolibarr_set_const($db, 'WTBC_CATEGS_AND_EXCLUDED_THIRD', serialize($TCategsAndExcludedThird), 'chaine', 0, '', $conf->entity);
} elseif($action === 'delCateg') {
	
	unset($TCategsAndExcludedThird['TCategs'][$_REQUEST['fk_categ']]);
	dolibarr_set_const($db, 'WTBC_CATEGS_AND_EXCLUDED_THIRD', serialize($TCategsAndExcludedThird), 'chaine', 0, '', $conf->entity);
	
} elseif($action === 'addCategSociete') {
	$TCategSoc = array();
	if(!empty($TCategsAndExcludedThird['TCategTiers'])) $TCategSoc = $TCategsAndExcludedThird['TCategTiers'];
	if($_REQUEST['fk_categ_soc'] > 0) $TCategSoc[$_REQUEST['fk_categ_soc']] = $_REQUEST['fk_categ_soc'];
	$TCategsAndExcludedThird['TCategTiers'] = $TCategSoc;
	dolibarr_set_const($db, 'WTBC_CATEGS_AND_EXCLUDED_THIRD', serialize($TCategsAndExcludedThird), 'chaine', 0, '', $conf->entity);
} elseif($action === 'delSociete') {
	
	unset($TCategsAndExcludedThird['TCategTiers'][$_REQUEST['fk_categ_soc']]);
	dolibarr_set_const($db, 'WTBC_CATEGS_AND_EXCLUDED_THIRD', serialize($TCategsAndExcludedThird), 'chaine', 0, '', $conf->entity);
	
}

//var_dump($TCategsAndExcludedThird);

/*
 * View
 */
$page_name = "weighttaxbycategSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = weighttaxbycategAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104995Name"),
    0,
    "weighttaxbycateg@weighttaxbycateg"
);

// Setup page goes here
$form=new Form($db);
$var=false;

print_titre($langs->trans('title1'));
print '<br />';

print '<table class="noborder">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Category").'</td>'."\n";
print '<td>'.$langs->trans("Service").'</td>'."\n";
print '<td>'.$langs->trans("Delete").'</td></tr>'."\n";

$bg = array(0=>'impair', 1=>'pair');

if(!empty($TCategsAndExcludedThird['TCategs'])) {
	
	foreach($TCategsAndExcludedThird['TCategs'] as $fk_categ=>$fk_product) {
	
		$c = new Categorie($db);
		$c->fetch($fk_categ);
		$c->color = 'ffffff'; // L'affichage de la couleur du texte dépend de couleur définie sur la categ
		
		$p = new Product($db);
		$p->fetch($fk_product);
		
		print '<tr class="'.$bg[$var].'">';
		print '<td>';
		print $c->getNomUrl(1);
		print '</td>';
		print '<td>';
		print $p->getNomUrl(1);
		print '</td>';
		print '<td>';
		print '<a href="?action=delCateg&fk_categ='.$fk_categ.'&fk_product='.$fk_product.'">'.img_picto($titlealt, 'delete.png').'</a>';
		print '</td>';
		print '</tr>';

		$var = !$var;

	}

}

print '</table>';

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print $langs->trans('addServiceAndCateg1');
print $form->select_produits('', 'fk_product', '', 20, 0, 1, 2, '', 1);
print ' '.$langs->trans('addServiceAndCateg2').' ';
print $form->select_all_categories('product', '', 'fk_categ');
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="addCateg">';
print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
print '</form>';

$var=false;
print '<br /><br />';
print_titre($langs->trans('title2'));
print '<br />';

print '<table class="noborder">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("ThirdParty").'</td>'."\n";
print '<td>'.$langs->trans("Delete").'</td></tr>'."\n";

if(!empty($TCategsAndExcludedThird['TCategTiers'])) {

	foreach($TCategsAndExcludedThird['TCategTiers'] as $fk_categ_soc) {
	
		$categ = new Categorie($db);
		$categ->fetch($fk_categ_soc);
		$categ->color = 'ffffff'; // L'affichage de la couleur du texte dépend de couleur définie sur la categ
		
		print '<tr class="'.$bg[$var].'">';
		print '<td>';
		print $categ->getNomUrl(1);
		print '</td>';
		print '<td>';
		print '<a href="?action=delSociete&fk_categ_soc='.$fk_categ_soc.'">'.img_picto($titlealt, 'delete.png').'</a>';
		print '</td>';
		print '</tr>';

		$var = !$var;

	}

}

print '</table>';

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print $langs->trans('addCategSociete');
print $form->select_all_categories('customer', '', 'fk_categ_soc');
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="addCategSociete">';
print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
print '</form>';

llxFooter();

$db->close();