<?php

class AnvideoPutter{
    public function insertTo_ps_an_product_video_gallery($img){
        $db = DB::getInstance();
        $res = $db->execute("INSERT INTO " . _DB_PREFIX_ . "an_product_video_gallery (preview, active, type_video, relation, position) VALUES ('$img','1','0','2','0')");

        $lastId = $db->executeS("SELECT LAST_INSERT_ID() AS last_id");
        $lastId = $lastId[0]['last_id'];
        return $lastId;
    }

    public function insertTo_ps_an_product_video_gallery_lang($id_video, $video){
        $db = DB::getInstance();
        var_dump($video);
        for($i = 1; $i < 9; $i++){
            $db->execute("INSERT INTO " . _DB_PREFIX_ . "an_product_video_gallery_lang (id_video, title, video, youtube, id_lang) VALUES ($id_video,'','$video','',$i)");
        }
    }

    public function insertTo_ps_an_product_video_gallery_relations($id_video, $id_productArr){
        $db = DB::getInstance();
        foreach ($id_productArr as $id) {
            var_dump($id);
            $db->execute("INSERT INTO " . _DB_PREFIX_ . "an_product_video_gallery_relations (type, id_video, id_type) VALUES ('2', $id_video, $id)");
        }
    }

    public function deleteVideo($idProduct){
        $db = DB::getInstance();

        $results = $db->executeS("SELECT * FROM " . _DB_PREFIX_ . "an_product_video_gallery_relations WHERE id_type = '" . $idProduct . "'");

        echo "<pre>";
        echo 'Delete video';
        var_dump($results);
        echo count($results);
        echo "</pre>";

        if(is_array($results) && !empty($results) ){
            foreach ($results as $el) {
                $db->execute("DELETE FROM " . _DB_PREFIX_ . "an_product_video_gallery WHERE id_video = '" . $el['id_video'] . "'");
                $db->execute("DELETE FROM " . _DB_PREFIX_ . "an_product_video_gallery_lang WHERE id_video = '" . $el['id_video'] . "'");
                $db->execute("DELETE FROM " . _DB_PREFIX_ . "an_product_video_gallery_relations WHERE id_video = '" . $el['id_video'] . "'");
            }
        }
    }

    public function checkExist($id_productArr){
        $db = DB::getInstance();
        
        $existingRecords = [];

        foreach ($id_productArr as $id) {
            $result = $db->executeS("SELECT id_video FROM " . _DB_PREFIX_ . "an_product_video_gallery_relations WHERE id_type = '" . $id . "'");

            if(isset($result)){
                array_push($existingRecords, $result);
            }
        }
        
        if(count($existingRecords) > 0){
            return 1;
        }else{
            return 0;
        }
    }

    public function insert($id, $movieNameArr){
        $db = DB::getInstance();

        $result = $this->checkExist($id);

        if($result){
            echo "<p style='color: red;'>" . $result . "</p>";
            $this->deleteVideo($id[0]);
            foreach($movieNameArr as $movie){
                $id_video = $this->insertTo_ps_an_product_video_gallery('');
                //var_dump($id_video);
                $movie = str_replace(' ', '', $movie);
                $this->insertTo_ps_an_product_video_gallery_lang($id_video, $movie);
                $this->insertTo_ps_an_product_video_gallery_relations($id_video, $id);
                break;
            }
        }else{
            //$id_productArr = array($id);
            foreach($movieNameArr as $movie){
                $id_video = $this->insertTo_ps_an_product_video_gallery('');
                $movie = str_replace(' ', '', $movie);
                $this->insertTo_ps_an_product_video_gallery_lang($id_video, $movie);
                $this->insertTo_ps_an_product_video_gallery_relations($id_video, $id);
                break;
            }
        }
    }    
}