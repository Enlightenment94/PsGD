<?php

require_once __DIR__ . '/vendor/autoload.php';

/*
if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}*/

use Google\Client;
use Google\Service\Drive;

class GoogleApi{
    public function clientConfig(){
        $client = new Client();
        $client->setApplicationName('TestApi');

        $client->setScopes([
          Google_Service_Drive::DRIVE,
          Google_Service_Drive::DRIVE_FILE,
          Google_Service_Drive::DRIVE_APPDATA,
          Google_Service_Drive::DRIVE_METADATA
        ]);


        //$absolute_path = realpath("./");
        //echo $absolute_path;

        $client->setAuthConfig(__DIR__ . '/client_secret_598838019153-273i1nbbusb9tmmepo73hr966dlpi0p1.apps.googleusercontent.com.json');
        
        $client->setAccessType('offline');
        //$client->setRedirectUri("https://127.0.0.1");
        $client->setPrompt('select_account consent');
        //$client->setDeveloperKey('AIzaSyDlZyWnp3ME6uwwFXLubtrb9GbIIQW215U');

        return $client;
    }

    public function getClient(){
        $client = $this->clientConfig();

        $tokenPath = __DIR__ . '/token.json';
        //echo $tokenPath . "</br>";

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                $client->setAccessToken($accessToken);

                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function createAuthUrl(){
        $client = $this->clientConfig();
        $authUrl = $client->createAuthUrl();
        return $authUrl;        
    }

    public function createToken($authCode){
        $client = $this->clientConfig();
        $tokenPath = __DIR__ . '/token.json';

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $authUrl = $client->createAuthUrl();
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

    }

    public function listRootDirs($service){
        try{
            $optParams = array(
                'pageSize' => 10,
                'fields' => 'files(id,name,mimeType)',
                'q' => 'mimeType = "application/vnd.google-apps.folder" and "root" in parents',
                'orderBy' => 'name'
            );
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (empty($files)) {
                print "No files found.\n";
            } else {
                print "Files:\n";
                foreach ($files as $file) {
                    $id = $file->id;

                    printf("%s - (%s) - (%s)\n", $file->getId(), $file->getName(), $file->getMimeType());
                }
            }
        }catch (Exception $e){

        }
    }

    public function listFiles($service) {
        try {
            $optParams = array(
                'pageSize' => 10,
                'fields' => 'files(id,name,mimeType)',
                'q' => "mimeType != 'application/vnd.google-apps.folder'",
                'orderBy' => 'name'
            );
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (empty($files)) {
                print "No files found.\n";
            } else {
                print "Files:\n";
                foreach ($files as $file) {
                    $id = $file->id;

                    printf("%s - (%s) - (%s)\n", $file->getId(), $file->getName(), $file->getMimeType());
                }
            }
        } catch (Exception $e) {

        }
    }

    public function listImages($service) {
        try {
            $optParams = array(
                'pageSize' => 10,
                'fields' => 'files(id,name,mimeType)',
                'q' => "mimeType contains 'image/'",
                'orderBy' => 'name'
            );
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (empty($files)) {
                print "No image files found.\n";
            } else {
                print "Image files:\n";
                foreach ($files as $file) {
                    $id = $file->id;

                    printf("%s - (%s) - (%s)\n", $file->getId(), $file->getName(), $file->getMimeType());
                }
            }
        } catch (Exception $e) {
            // obsługa błędu
        }
    }




    public function optImages(){
        return array(
                'pageSize' => 10,
                'fields' => 'nextPageToken, files(id,name,mimeType)',
                'q' => "mimeType contains 'image/'",
                'orderBy' => 'name'
            );
    }

    public function optFiles(){
        return array(
                'pageSize' => 10,
                'fields' => 'nextPageToken, files(id,name,mimeType)',
                'q' => "mimeType != 'application/vnd.google-apps.folder'",
                'orderBy' => 'name'
            );
    }

    public function optRootDirs(){
        return array(
                'pageSize' => 10,
                'fields' => 'nextPageToken, files(id,name,mimeType)',
                'q' => 'mimeType = "application/vnd.google-apps.folder" and "root" in parents',
                'orderBy' => 'name'
            );
    }

    public function list($service, $optParams) {
        try {
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            while (!empty($files)) {
                foreach ($files as $file) {
                    $id = $file->getId();
                    printf("%s - (%s) - (%s)\n", $file->getId(), $file->getName(), $file->getMimeType());
                }

                if ($results->getNextPageToken()) {
                    $optParams['pageToken'] = $results->getNextPageToken();
                    $results = $service->files->listFiles($optParams);
                    $files = $results->getFiles();
                } else {
                    break;
                }
            }

            if (empty($files)) {
                print "No image files found.\n";
            }

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }

    function searchFilesDoc()
    {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);
            $files = array();
            $pageToken = null;
            do {
                $response = $driveService->files->listFiles(array(
                    'q' => "mimeType='image/jpeg'",
                    'spaces' => 'drive',
                    'pageToken' => $pageToken,
                    'fields' => 'nextPageToken, files(id, name)',
                ));
                foreach ($response->files as $file) {
                    printf("Found file: %s (%s)\n", $file->name, $file->id);
                }
                array_push($files, $response->files);

                $pageToken = $response->pageToken;
            } while ($pageToken != null);
            return $files;
        } catch(Exception $e) {
        echo "Error Message: ".$e;
        }
    }

    public function searchFile($service, $fileName) {
        try {
            $optParams = array(
                'q' => "name = '" . $fileName . "'"
            );

            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (count($files) > 0) {
                foreach ($files as $file) {
                    printf("%s - (%s) - (%s)\n", $file->getId(), $file->getName(), $file->getMimeType());
                }
            } else {
                print "File not found.\n";
            }

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }

    //!!!!!!!!!!!!!! Key function
    public function searchFileWithoutExtenstion($service, $fileName) {
        //echo $fileName . "</br>";
        $result = array();
        try {
            $optParams = array(
                'q' => "name contains '" . $fileName . "'"
            );

            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (count($files) > 0) {
                foreach ($files as $file) {
                    printf("%s - (%s) - (%s)\n", $file->getId(), $file->getName(), $file->getMimeType());
                    array_push($result, array($file->getName(), $file->getMimeType()));
                }
            } else {
                print "File not found.\n";
            }
            return $result;
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }

    public function createDocument($service, $newFileName){
        try{
            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => $newFileName,
                'mimeType' => 'application/vnd.google-apps.document'
            ));
            $file = $service->files->create($fileMetadata);

            echo 'Utworzono plik o ID: ' . $file->id;
        }
        catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
    }

    public function downloadDriverGroupBy($service, $fileName, $output){
        try{
            $results = $service->files->listFiles([
                'q' => "name = '" . $fileName . "'"
            ]);

            $files = $results->getFiles();
            echo count($files);
            if (count($files) > 0) {
                $file = $files[0];
                $fileId = $file->getId();
                
                $content = $service->files->get($fileId, ['alt' => 'media']);

                $outHandle = fopen($output . "/" . $file->getName() , "wb");
                while (!$content->getBody()->eof()) {
                    fwrite($outHandle, $content->getBody()->read(1024));
                }
                fclose($outHandle);
                
                return array($file->getName(), $file->getMimeType());
                echo "Pobieranie zakończone.";
            } else {
                echo "Nie znaleziono pliku o podanej nazwie do pobrania.";
            }
        }catch(Exception $e){
            echo $e;
        }
    }


    public function downloadDriver($service, $fileName, $output){
        try{
            $results = $service->files->listFiles([
                'q' => "name = '" . $fileName . "'"
            ]);

            $files = $results->getFiles();
            echo count($files);
            if (count($files) > 0) {
                $file = $files[0];
                $fileId = $file->getId();
                
                $content = $service->files->get($fileId, ['alt' => 'media']);

                $outHandle = fopen($output . "/" . $file->getName() , "wb");
                while (!$content->getBody()->eof()) {
                    fwrite($outHandle, $content->getBody()->read(1024));
                }
                fclose($outHandle);
                
                echo "Pobieranie zakończone.";
            } else {
                echo "Nie znaleziono pliku o podanej nazwie do pobrania.";
            }
        }catch(Exception $e){
            echo $e;
        }
    }

    public function downloadDriverAll($service, $fileName, $output){
        try{
            $results = $service->files->listFiles([
                'q' => "name contains '" . $fileName . "'"
            ]);

            $files = $results->getFiles();
            echo count($files);
            if (count($files) > 0) {
                foreach($files as $f){
                    $file = $f;
                    $fileId = $file->getId();
                    
                    $content = $service->files->get($fileId, ['alt' => 'media']);

                    $outHandle = fopen($output . "/" . $file->getName() , "wb");
                    while (!$content->getBody()->eof()) {
                        fwrite($outHandle, $content->getBody()->read(1024));
                    }
                    fclose($outHandle);
                    
                    echo "Pobieranie zakończone.";
                }
            } else {
                echo "Nie znaleziono pliku o podanej nazwie do pobrania.";
            }
        }catch(Exception $e){
            echo $e;
        }
    }
    
    public function flushDir($folder){
        if(file_exists($folder)) {
            $files = glob($folder . '/*');
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
            
            foreach($files as $file) {
                if(is_dir($file)) {
                    $this->clearFolder($file);
                    rmdir($file);
                }
            }
            echo 'Folder wyczyszczony.';
        } else {
            echo 'Folder nie istnieje.';
        }
    }

    public function listDir($folder){
        if(file_exists($folder)) {
            $contents = scandir($folder);
            $contents = array_diff($contents, array('.', '..'));
            
            $arr = array();
            // Iteracja przez każdy element
            foreach($contents as $element) {
                echo $element . "\n";
                array_push($arr, $element);
            }
            return $arr;
        } else {
            echo 'Folder nie istnieje.';
        }
    }
















    public function searchFile2($service, $fileName) {
        try {
            $optParams = array(
                'q' => "name = '" . $fileName . "'",
                'fields' => 'files(id, name, mimeType, parents)'
            );

            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            if (count($files) > 0) {
                foreach ($files as $file) {
                    $path = $this->buildPath($service, $file->getParents()[0]);
                    printf("%s - (%s) - (%s) - %s\n", $file->getId(), $file->getName(), $file->getMimeType(), $path);
                }
            } else {
                print "File not found.\n";
            }

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }

    public function searchFolderAndFilesOne($service, $folderName, $fileName) {
        try {
            // Wyszukaj folder po nazwie
            $optParams = array(
                'q' => "mimeType='application/vnd.google-apps.folder' and name='$folderName'",
                'fields' => 'files(id, name)',
            );
    
            $results = $service->files->listFiles($optParams);
            $folders = $results->getFiles();
    
            if (count($folders) > 0) {
                $folderId = $folders[0]->getId();
    
                // Przeglądaj pliki w folderze i jego podfolderach
                return $this->searchFilesInFolder($service, $folderId, $fileName);
            } else {
                print "Folder not found.\n";
            }
    
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }
    
    function searchFilesInFolderOne($service, $folderId, $fileName) {
        try {
            $optParams = array(
                'q' => "'" . $folderId . "' in parents and name = '" . $fileName . "'",
                'fields' => 'files(id, name, mimeType, parents)'
            );
    
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();
    
            $idArr = array();
            if (count($files) > 0) {
                foreach ($files as $file) {
                    $path = $this->buildPath($service, $file->getParents()[0]);
                    printf("%s - (%s) - (%s) - %s\n", $file->getId(), $file->getName(), $file->getMimeType(), $path);
                    array_push($idArr, array($file->getId(), $file->getName(), $file->getMimeType()) );
                }
            } else {
                print "File not found in the specified folder.\n";
            }

            return $idArr; 
    
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }

    public function searchFolderAndFilesRecursive($service, $folderName, $fileName) {
        try {
            // Wyszukaj folder po nazwie
            $optParams = array(
                'q' => "mimeType='application/vnd.google-apps.folder' and name='$folderName'",
                'fields' => 'files(id, name)',
            );
    
            $results = $service->files->listFiles($optParams);
            $folders = $results->getFiles();
    
            if (count($folders) > 0) {
                $folderId = $folders[0]->getId();
    
                // Przeglądaj pliki w folderze i jego podfolderach
                $idArr = [];
                $idArr = $this->searchFilesInFolder($service, $folderId, $fileName, $idArr);
                
                return $idArr;
            } else {
                print "Folder not found.\n";
            }
    
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }
    
    function searchFilesInFolderRecursive($service, $folderId, $fileName, $idArr) {
        try {
            $optParams = array(
                'q' => "'" . $folderId . "' in parents and name = '" . $fileName . "'",
                'fields' => 'files(id, name, mimeType, parents)',
            );
    
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();
    
            if (count($files) > 0) {
                foreach ($files as $file) {
                    $path = $this->buildPath($service, $file->getParents()[0]);
                    printf("%s - (%s) - (%s) - %s\n", $file->getId(), $file->getName(), $file->getMimeType(), $path);
                    array_push($idArr, array($file->getId(), $file->getName(), $file->getMimeType()));
                }
            } else {
                print "File not found in the specified folder.\n";
            }
    
            // Znajdź podfoldery i przeszukaj je
            $optParams = array(
                'q' => "mimeType='application/vnd.google-apps.folder' and '" . $folderId . "' in parents",
                'fields' => 'files(id, name)',
            );
    
            $results = $service->files->listFiles($optParams);
            $subfolders = $results->getFiles();
    
            foreach ($subfolders as $subfolder) {
                $idArr = $this->searchFilesInFolder($service, $subfolder->getId(), $fileName, $idArr);
            }
    
            return $idArr;
        
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }


    public function searchFileInFolderByName($driveService, $folderName, $fileName) {
        try {
            // Znajdź folder po nazwie
            $folderId = null;
            $optParams = array(
                'q' => "mimeType='application/vnd.google-apps.folder' and name='$folderName'",
            );
            $results = $driveService->files->listFiles($optParams);
            $folders = $results->getFiles();
    
            if (count($folders) > 0) {
                $folderId = $folders[0]->getId();
                return $this->searchFileInFolderRecEasy($driveService, $folderId, $fileName);
            }
    
            return null;
        } catch (Exception $e) {
            echo "Error Message: " . $e;
        }
    }


    public function searchFileInFolderRecEasy($driveService, $folderId, $fileName) {
        try {
            // Przeszukaj pliki w folderze
            $response = $driveService->files->listFiles(array(
                'q' => "'$folderId' in parents",
                'spaces' => 'drive',
                'fields' => 'files(id, name, mimeType)',
            ));
    
            foreach ($response->files as $item) {
                if ($item->mimeType == 'application/vnd.google-apps.folder') {
                    // Rekurencyjne wywołanie funkcji dla podfoldera
                    $foundFile = $this->searchFileInFolderRecEasy($driveService, $item->id, $fileName);
                    if ($foundFile !== null) {
                        return $foundFile;
                    }
                } else {
                    if ($item->name == $fileName) {
                        printf("Found file: %s (%s)\n", $item->name, $item->id);
                        return $item;
                    }
                }
            }
    
            return null;
        } catch (Exception $e) {
            echo "Error Message: " . $e;
        }
    }



    function buildPath($service, $folderId) {
        $path = "";
        do {
            try {
                $file = $service->files->get($folderId, array('fields'=>'id, name, parents'));
                $path = $file->getName() . "/" . $path;
                $folderId = $file->getParents()[0];
            } catch (Exception $e) {
                break;
            }
        } while (!empty($folderId));
        return $path;
    }

    public function downloadDriverAllIdentical($service, $fileName, $output){
        try{
            $optParams = array(
                'q' => "name = '" . $fileName . "'"
            );

            $results = $service->files->listFiles($optParams);

            $files = $results->getFiles();
            echo count($files);
            if (count($files) > 0) {
                foreach($files as $f){
                    $file = $f;
                    $fileId = $file->getId();
                    
                    $content = $service->files->get($fileId, ['alt' => 'media']);

                    $outHandle = fopen($output . "/" . $file->getName() , "wb");
                    while (!$content->getBody()->eof()) {
                        fwrite($outHandle, $content->getBody()->read(1024));
                    }
                    fclose($outHandle);
                    
                    echo "Pobieranie zakończone.";
                }
            } else {
                echo "Nie znaleziono pliku o podanej nazwie do pobrania.";
            }
        }catch(Exception $e){
            echo $e;
        }
    }

    public function downloadDriverAllIdenticalByFolder($service, $folderName, $fileName, $output){
        try{
            /*
            $optParams = array(
                'q' => "name = '" . $fileName . "'"
            );

            $results = $service->files->listFiles($optParams);

            $files = $results->getFiles();
            */
            $files = $this->searchFolderAndFilesOne($service, $folderName, $fileName);
            var_dump($files);

            echo count($files);
            if (count($files) > 0) {
                foreach($files as $f){
                    $file = $f;
                    $fileId = $file['0'];
                    
                    $content = $service->files->get($fileId, ['alt' => 'media']);

                    $outHandle = fopen($output . "/" . $file['1'] , "wb");
                    while (!$content->getBody()->eof()) {
                        fwrite($outHandle, $content->getBody()->read(1024));
                    }
                    fclose($outHandle);
                    
                    echo "Pobieranie zakończone.";
                }
            } else {
                echo "Nie znaleziono pliku o podanej nazwie do pobrania.";
            }
        }catch(Exception $e){
            echo $e;
        }
    }
}

