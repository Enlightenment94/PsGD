<?php

require_once( __DIR__ . "/../../classes/SyncImages.php" );
require_once( __DIR__ . "/../../classes/AnvideoPutter.php" );

//admin-panel/index.php?controller=StrDownloadVideo&token=939e127b820db7d0529b0914543d1fde&id=5139&onlyContent=t

class PsDownloadVideoController extends ModuleAdminController
{
    protected $local_path;
    public $name = 'PsDownloadVideo';
    public $tab = 'PsDownloadVideo';
    public const TAB_CLASS_NAME = 'PsDownloadVideo';

    public function __construct()
    {
        $this->controller_name = self::TAB_CLASS_NAME;
        $this->page_header_toolbar_title = 'PsDownloadVideo';
        $this->meta_title = 'PsDownloadVideo Configuration';
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

        $url .= $_SERVER['HTTP_HOST'] . "/";

        $psApiKey = Configuration::get('PSGD_PS_API');

        if($psApiKey == ''){
            return "<div id='onlycontent'>" . "Configure Prestashop Api Key" . "</div>";
        }

        $sync = new SyncImagesDriveApi($url, $psApiKey);
        $idProduct = Tools::getValue("id");

        $out = $sync->syncViedo($idProduct);

        ob_start();
        echo "<pre>";
        echo $idProduct;
        var_dump($out);
        echo "</pre>";

        $anvideoPutter = new AnvideoPutter();

        if(!empty($out['movie'])){
            $anvideoPutter->insert(array($idProduct), $out['movie']);
        }
        $out2 = ob_get_clean();

        $output = "<div id='onlycontent'>" . $out['output'] . "</div>";

        ob_start();
        echo "<pre>";
        var_dump($out['movie']);
        echo "</pre>";
        $movie = ob_get_clean();

        $onlyContent = Tools::getValue("onlyContent");
        if($onlyContent == "t"){
            return  "<div id='onlycontent'>" . $movie . "</div>";
            if($movie == ''){
                return "Empty";
            }else{
                return  "<div id='onlycontent'>" . $movie . "</div>";
            }
        }else{
            return "<div id='onlycontent'>" . "Controller" . "</div>";
        }
    }
}