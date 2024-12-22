<?php
/**
* 2007-2024 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

#ini_set("display_errors", true);

require_once(__DIR__ . "/classes/googleApi/Gapi.php");

if (!defined('_PS_VERSION_')) {
    exit;
}

class Psgd extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'psgd';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'PsBackend';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PsGD');
        $this->description = $this->l('Sync images with google driver by reference ...');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') && 
            $this->registerHook('displayAdminProductsMainStepLeftColumnMiddle') && 
            $this->installTab() &&
            $this->installTab1() &&
            $this->registerController('PsSyncImages') &&
            $this->registerController('PsDownloadVideo');
    }

    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('PsSyncImages');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'PsSyncImages';
        $tab->name = [];

        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('PsSyncImages', [], 'Modules.psgd.Admin', $lang['locale']);
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdvancedParameters');
        $tab->module = $this->name;

        return $tab->save();
    }

    private function installTab1()
    {
        $tabId = (int) Tab::getIdFromClassName('PsDownloadVideo');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'PsDownloadVideo';
        $tab->name = [];
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('PsDownloadvideo', [], 'Modules.psgd.Admin', $lang['locale']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdvancedParameters');
        $tab->module = $this->name;

        return $tab->save();
    }

    public function uninstall()
    {
        //Configuration::deleteByName('STR_SYNCIMAGES_LIVE_MODE');
        return parent::uninstall();
    }

    public function authLink($authUrl)
    {
        return '<a href="' . htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') . '" 
                    class="btn btn-primary" 
                    style="padding: 10px 15px; color: #fff; background-color: #007bff; text-decoration: none; border-radius: 5px;"
                >Click here to authenticate</a>';
    }

    public function getContent()
    {
        $googleDriverApi = new GoogleApi();

        if (((bool)Tools::isSubmit('submit')) == true) {
            //echo "ApiUrl set";
            $strSyncImagesPsAPI = Tools::getValue('PSGD_PS_API');
            Configuration::updateValue('PSGD_PS_API', $strSyncImagesPsAPI);
        }

        if (((bool)Tools::isSubmit('submitGd')) == true) {
            $authCode = Tools::getValue('PSGD_GD_API');
            $googleDriverApi->createToken($authCode);
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        $authUrl = $googleDriverApi->createAuthUrl();

        $controllerLink = $this->context->link->getAdminLink('PsSyncImages');

        return $output . $this->renderForm()
             . $this->authLink($authUrl)
             . $this->renderTokenForm()
             . "Link to controller: <a href='" . $controllerLink . "'>" . $controllerLink . "</a>" 
             . "<p>Hook in edit Product: {hook h='DisplayAdminProductsMainStepLeftColumnMiddle'</p>"; 
    }

    protected function getConfigFormValues()
    {
        return array(
            'PSGD_PS_API'    => Configuration::get('PSGD_PS_API', ''),
            'PSGD_GD_API'    => Configuration::get('PSGD_GD_API', ''),
        );
    }


    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        //$this->fields_form = [];
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit';
        $helper->token = Tools::getAdminTokenLite("PsSyncImages");

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
               'languages' => $this->context->controller->getLanguages(),
             'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm(){
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API/URL'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'PSGD_PS_API',
                        'desc' => $this->l('Enter shop API'),
                        'label' => $this->l('Prestashop API_KEY'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('set'),
                ),
            ),
        );
    }


    public function renderTokenForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGd';
        $helper->token = Tools::getAdminTokenLite("PsSyncImages");

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
               'languages' => $this->context->controller->getLanguages(),
             'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigTokenForm()]);
    }

    protected function getConfigTokenForm(){
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('GoogleDriver token generate code'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'PSGD_GD_API',
                        'desc' => $this->l('Enter code from auth link'),
                        'label' => $this->l('Google driver response code'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('generate'),
                ),
            ),
        );
    }



    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
    


    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }
    

    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/front.js');
        $this->context->controller->addCSS($this->_path.'views/css/front.css');
    }

    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params)
    {
        $url = "https://";

        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $parts = explode('/', $url);

        $idProduct = $parts[8];

        $idProduct = explode('?', $idProduct)[0]; 
        
        $controllerSyncLink =  $this->context->link->getAdminLink('PsSyncImages') . "&id=" . (int)$idProduct . "&straction=link";
        $controllerTestLink =  $this->context->link->getAdminLink('PsSyncImages') . "&id=" . (int)$idProduct;
        $strDownloadVideoController =  $this->context->link->getAdminLink('PsDownloadVideo') . "&id=" . (int)$idProduct;
        $controllerFlushImg =  $this->context->link->getAdminLink('PsSyncImages') . "&id=" . (int)$idProduct . "&flush=t";

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $controllerSyncLink = preg_replace('/^http:/i', 'https:', $controllerSyncLink);
            $controllerTestLink = preg_replace('/^http:/i', 'https:', $controllerTestLink);
            $strDownloadVideoController = preg_replace('/^http:/i', 'https:', $strDownloadVideoController);
            $controllerFlushImg = preg_replace('/^http:/i', 'https:', $controllerFlushImg);
        }

    
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    
        // Przypisanie zmiennych do Smarty
        $this->context->smarty->assign([
            'idProduct' => $idProduct,
            'controllerSyncLink' => $controllerSyncLink,
            'controllerTestLink' => $controllerTestLink,
            'PsDownloadVideoController' => $strDownloadVideoController,
            'controllerFlushImg' => $controllerFlushImg
        ]);
    
        $this->context->smarty->assign([
            'idProduct' => $idProduct,
            'controllerSyncLink' => $controllerSyncLink,
            'controllerTestLink' => $controllerTestLink,
            'PsDownloadVideoController' => $strDownloadVideoController,
            'controllerFlushImg' => $controllerFlushImg
        ]);

        $hook = $this->context->smarty->fetch($this->local_path.'views/templates/admin/hook.tpl');        

        return $hook;
    }
}
