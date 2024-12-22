<?php
/**
 * 2007-2016 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2016 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

//require_once( __DIR__ . "/../../src/StrSyncShop.php");

require_once(__DIR__ . "/../../classes/SyncImages.php");

class PsSyncImagesController extends ModuleAdminController
{
    protected $local_path;
    public $name = 'PsSyncImages';
    public $tab = 'PsSyncImages';

    public function __construct()
    {
        $this->controller_name = 'PsSyncImages';
        $this->meta_title = 'PsGD Configuration';
        $this->bootstrap = true;
        $this->local_path = _PS_MODULE_DIR_ . 'PsGD/';
        parent::__construct();
    }

    public function initContent(){
        $this->display = 'view';
        parent::initContent();
    }

    public function renderView(){
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";
        } else {
            $url = "http://";
        }

        $db = DB::getInstance();
        $record = $db->executeS("SELECT physical_uri FROM " . _DB_PREFIX_ . "shop_url" );

        $url .= $_SERVER['HTTP_HOST'] . $record[0]['physical_uri'];        

        $id = Tools::getValue("id");
        $action = Tools::getValue("straction");
        $psApiKey = Configuration::get('PSGD_PS_API');

        if($psApiKey == ''){
            return "Configure Prestashop Api Key";
        }

        $onlyContent = Tools::getValue("onlyContent");

        $id = Tools::getValue("id");
        $output = "";
        $sync = new SyncImagesDriveApi($url, $psApiKey);

        $flush = Tools::getValue("flush");
        if($flush == 't'){
            ob_start();
            $sync->psImageApi->removeAllImg($id);
            $output = ob_get_clean();
            echo $output;
            die("Zdjęcia usunięto ...");
            //return "Zdjęcia usunięto ...";
        }

        if($action == "link" && $id != ""){
            ob_start(); 
            $sync->sync($id);
            $echoSync = ob_get_clean();
            ob_end_clean(); 
            if($onlyContent){
                die($echoSync);
            }
            return "Sync in Api tunnel end." . "<pre style='max-width: 1200px; white-space: pre-wrap;'>" . $echoSync . "</pre>";
        }

        if($id != ""){           
            $output = $sync->syncTest($id);        
            
            $result = "<p><b>Sync image:</b></p>"
                . "<p>" . $output['index'] . "</p>"
                . "<p>" . $output['parseIndex'] . "</p>"
                . "id_product: ". $id . "<pre>" . print_r($output['resultArr'], true) . "</pre>";
            if($onlyContent){
                die($result);
            }
            return $result;
        }else{
            return "Try download image in edit product panel ...";
        }
    }
}