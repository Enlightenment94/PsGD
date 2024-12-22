<?php

require_once('googleApi/Gapi.php');
require_once( __DIR__ . "/../../PsCommon/api/PsApi.php");

use Google\Client;
use Google\Service\Drive;

class SyncImagesDriveApi{
	public $psProductApi;
	public $psImageApi;
	public $apiGoogleDirver;
	public $service;

	public function __construct($url, $apiPrestashopKey){
		$this->apiGoogleDirver = new GoogleApi();
    	$client = $this->apiGoogleDirver->getClient();
    	$this->service = new Drive($client);
		$this->psProductApi = new PsProductApi($url, $apiPrestashopKey);;
		$this->psImageApi   = new PsImageApi($url, $apiPrestashopKey);;
	}

	public function syncTest($idProduct){
		$pi = new ParseIndex();

		$index = $this->apiPrestashop->getIndexProduct($idProduct);
		$googleDriverIndex = $index;

		echo "\nDriver reference: ". $googleDriverIndex . "\n";
		if($googleDriverIndex != ""){
			$resultArr = $this->apiGoogleDirver->searchFileWithoutExtenstion($this->service, $googleDriverIndex);
		}

		if(count($resultArr) != 0){
			return array(
			    'syncResult' => 1,
			    'googleDriverIndex' => $googleDriverIndex,
			    'resultArr' => $resultArr,
			    'index' => $index,
			    'parseIndex' => $googleDriverIndex
			);
		}
		return array(
		    'syncResult' => 0,
		    'googleDriverIndex' => $googleDriverIndex,
		    'resultArr' => $resultArr,
		    'index' => $index,
		    'parseIndex' => $googleDriverIndex
		);
	}

	public function sync($idProduct){
		$index = $this->psProductApi->getIndexProduct($idProduct);
		$googleDriverIndex = $index; 
		echo "\n". $googleDriverIndex . "\n";

		ob_start();
		$pathBase = __DIR__ ."/tempDriver";
		$resultArr = $this->apiGoogleDirver->searchFileWithoutExtenstion($this->service, $googleDriverIndex);

		$this->apiGoogleDirver->flushDir($pathBase);
		echo "\n";
		$output = ob_get_clean();

		echo $output;

		echo "n: " . count($resultArr). "\n";
		
		ob_start();
		if(count($resultArr) != 0){
			$this->apiGoogleDirver->downloadDriverAll($this->service, $googleDriverIndex, $pathBase);
			$dir = $this->apiGoogleDirver->listDir($pathBase);

			$this->psImageApi->removeAllImg($idProduct);
			foreach ($dir as $fileName) {
				try{
					echo $pathBase . "/" . $fileName . "\n";
					$extension = pathinfo($pathBase . "/" . $fileName , PATHINFO_EXTENSION);
					$this->psImageApi->addImg($idProduct, $pathBase . "/" . $fileName , 'image/' . $extension);
				}catch (Exception $e){
					echo 'Wystąpił błąd Curl: ' . $e->getMessage();
				}
			}
		}
		$output = ob_get_clean();

		echo $output;
	}

	public function syncViedo($idProduct){
		$index = $this->psProductApi->getIndexProduct($idProduct);
		$googleDriverIndex = $index;

		ob_start();
		echo $index;
		echo "\n". $googleDriverIndex . "\n";

		$path =  __DIR__ ;

		$position = strstr($path, 'public_html', true);
		$path_to_public_html = substr($path, 0, strpos($path, $position) + strlen($position));

		$pathBase = $path_to_public_html . "public_html/modules/anproductvideogallery/img";
		echo $pathBase;

		$resultArr = $this->apiGoogleDirver->downloadDriverGroupBy($this->service, $googleDriverIndex . ".mp4", $pathBase);

		echo "\n";
		if(isset($resultArr)){
			echo "n: " . count($resultArr). "\n";
		}else{
			echo "n: " . "0". "\n";
		}
		$output = ob_get_clean();

		return array('output' => $output, 'movie' => $resultArr);
	}
}
