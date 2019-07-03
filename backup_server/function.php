<?php

    // 經常使用的方法定義在這邊

    function dd(...$params){
        foreach($params as $param){
            var_dump($param);
        }
        exit;
    }

    function keys($array){
        return array_keys($array);
    }

    function values($array){
        return array_values($array);
    }

    function containsKey($array,$data){
        return array_search($data,keys($array)) !==false;
    }

    function contains($array,$data){
        return array_search($data,$array) !==false;
    }

    function array_fetch($array, $keys){
        $output = [];
        foreach($array as $row){
            $output[count($output)] = [];
            foreach($keys as $key){
                array_copy($output[count($output)-1], $row, $key);
            }
        }
        return $output;
    }

    function array_only($array, $keys){
        return array_fetch([$array], $keys);
    }

    function array_get($array, $key){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));

        if($key == ""){
            return $array[$current_key];
        }

        if(!containsKey($array, $current_key))
            return false;

        return array_get($array[$current_key], $key);
    }

    function array_copy(&$a, &$b, $key){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));
        
        if($key == ""){
            $a[$current_key] = $b[$current_key];
            return;
        }

        if(!containsKey($a, $current_key))
            $a[$current_key] = [];

        if(!containsKey($b, $current_key))
            $b[$current_key] = [];

        array_copy($a[$current_key], $b[$current_key], $key);
    }

    function clearEmpty(&$array){
        $array = values(array_filter($array,function($d){
            return $d !="";
        }));
    }

    function Response($res=null){
        return new Response($res);
    }

    function get_mime_type($filename) {
        $idx = explode('.', $filename );
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode-1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset( $mimet[$idx] )) {
         return $mimet[$idx];
        } else {
         return 'application/octet-stream';
        }
     }

     function url($path){

        $is_cli_server = php_sapi_name() == 'cli-server';
        $path = array_filter(explode('/', $path), function($x){
            return $x !== '.';
        });

        clearEmpty($path);
        $path = implode('/', $path);

        if(!$is_cli_server){
            $current_dir = str_replace('\\','/',getcwd());
            $root = $_SERVER['DOCUMENT_ROOT'];
            $except_url = explode('/',str_replace($root,'',$current_dir));

            clearEmpty($except_url);
            $path = explode('/', ('/'.implode('/', $except_url).'/'.$path));
            clearEmpty($path);
            $path = implode('/', $path);
            return '/'.$path;
        }

        return $path;
     }

     function get_files_url($uri=''){
        return './public/assets/files/'.$uri;
     }

     function url_parse($url){
         $url = explode('/', $url);
         $url = array_filter($url, function($u){
             return $u != "" && $u != ".";
         });
         $url = implode('/', $url);
         return $url;
     }