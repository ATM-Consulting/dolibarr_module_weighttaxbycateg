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
 * 	\file		core/triggers/interface_99_modMyodule_weighttaxbycategtrigger.class.php
 * 	\ingroup	weighttaxbycateg
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_99_modMymodule_Mytrigger.class.php
 * 					interface_99_all_Mytrigger.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceMytrigger
 * 				- The constructor method must be named InterfaceMytrigger
 * 				- The name property name must be Mytrigger
 */

/**
 * Trigger class
 */
class Interfaceweighttaxbycategtrigger
{

    private $db;

    /**
     * Constructor
     *
     * 	@param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'weighttaxbycateg@weighttaxbycateg';
    }

    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') {
            return $langs->trans("Development");
        } elseif ($this->version == 'experimental')

                return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else {
            return $langs->trans("Unknown");
        }
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action
        // Users
        
        global $db;
		
        if ($action === 'BILL_VALIDATE' || $action === 'PROPAL_VALIDATE' || $action === 'ORDER_VALIDATE') {
        
	        dol_include_once('/product/class/product.class.php');
	        dol_include_once('/categories/class/categorie.class.php');
			
			$TCategsAndExcludedThird = unserialize($conf->global->WTBC_CATEGS_AND_EXCLUDED_THIRD);
			$TCategsConf = $TCategsAndExcludedThird['TCategs'];
			$TTiersConf = $TCategsAndExcludedThird['TCategTiers'];
			
			$c = new Categorie($db);
			
			// Exclusion par catégorie tiers
			$TCategs = $c->containing($object->socid, 'customer', 'id');
			foreach($TCategs as $id_categ) {
				if(!empty($TTiersConf) && in_array($id_categ, $TTiersConf)) return 0;
			}
			
			/* Tableau associatif qui va contenir en clé l'id de la catégorie
			 * et en valeur le montant total des produits de cette categ dans le document
			 */
			$TQty = array();
			
			// Va contenir tous les id des produits trouvés dans le document (on profite de la boucle)
			$TProductDocument = array();
			
        	foreach($object->lines as $line) {
        		
				if(!empty($line->fk_product)) {
					
					// On récupère les catégories du produit
					$p = new Product($db);
					$p->fetch($line->fk_product);
					
					$TCategs = $c->containing($line->fk_product, 'product', 'id');
					
					$TProductDocument[] = $line->fk_product;
					
					foreach($TCategsConf as $id_categ=>$TProducts) {
						if(in_array($id_categ, $TCategs)) $TQty[$id_categ] += $line->qty;
					}
					
				}
				
        	}
			
			foreach($TQty as $id_categ => $qty) {
				
				$service_utilise = $TCategsConf[$id_categ];
				if(!in_array($service_utilise, $TProductDocument)) {
					$srv = new Product($db);
					$srv->fetch($service_utilise);
					$object->addline('', $srv->price * $qty, 1, 0, 0, 0, $srv->id);
				}
				
			}
			
        }

        return 0;
    }
}