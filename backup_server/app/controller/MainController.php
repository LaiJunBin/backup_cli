<?php

    function show_files($dir, $depth = 4){
        $files = glob($dir.'/'.'{,.}[!.,!..]*',GLOB_MARK|GLOB_BRACE);
        if(count($files) == 0)
            echo str_repeat(' ', $depth)."not exists file.".PHP_EOL;

        foreach($files as $file){
            echo str_repeat(' ', $depth).basename($file).PHP_EOL;
            if(is_dir($file)){
                show_files($dir.'/'.basename($file), $depth+2);
            }
        }
    }

    function list_all(Request $request){
        $input = $request->all();
        echo "Files List:".PHP_EOL;
        echo "  Public Files:".PHP_EOL;
        
        show_files(get_files_url('public/'));

        if(login($request)){
            echo "  Private Files:".PHP_EOL;
            show_files(get_files_url('private/'));
        }
        
    }

    function upload(Request $request){
        $input = $request->all();
        try {
            $name = ($input['path'] ?? 'public/').($input['rename'] ?? $input['filename']);
            $filename = get_files_url($name);
            $temp = "";
            foreach(explode('/', $name) as $f){
                $temp .= $f;
                $t = get_files_url(dirname($temp));
                if(!is_dir($t)){
                    mkdir($t);
                }
                $temp .= '/';
            }
            if(file_exists($filename) && !is_dir($filename) && !isset($input['force'])){
                echo "file ".($input['rename'] ?? $input['filename'])." already exists.";
            }else{
                file_put_contents($filename, base64_decode($input['raw_data']));
                echo 'upload success.';
            }
        } catch (\Exception $e) {
            echo 'upload fails.';
        }
    }

    function check_file(Request $request){
        $file = get_files_url($_POST['path'].'/'.$_POST['filename']);
        if(file_exists($file) && !is_dir($file)){
            echo 1;
        }else{
            echo 0;
        }
    }

    function download(Request $request){
        $_GET['filename'] = str_replace('@%@', '/', $_GET['filename']);
        $file = get_files_url($_GET['path'].'/'.$_GET['filename']);

        if(file_exists($file) && !is_dir($file)){
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:".filesize($file));
            header("Content-Disposition: attachment; filename=".basename($_GET['filename']));
            readfile($file);
        }else{
            echo "file {$_GET['filename']} not found.";
        }
    }

    function rename_file(Request $request){
        // $raw_data = file_get_contents("PHP://input");
        // dd(html_entity_decode($raw_data));
        // $input = array_combine(array_map(function($data){
        //     return explode('=', $data)[0];
        // }, explode('&', $raw_data)), array_map(function($data){
        //     return explode('=', $data)[1];
        // }, explode('&', $raw_data)));
        $input = $request->all();
        $filename = get_files_url(($input['path']??'public/').$input['old_filename']);

        if(file_exists($filename) && !is_dir($filename)){
            $new_filename = get_files_url(($input['path']??'public/').$input['new_filename']);
            if(is_dir(dirname($new_filename))){
                if(file_exists($new_filename) && !is_dir($new_filename)){
                    echo "file {$input['new_filename']} already exists.";
                }else{
                    rename($filename, $new_filename);
                    echo 'rename file success.';
                }
            }else{
                echo 'directory '.dirname($input['new_filename'])."/ not exists.";
            }
        }else{
            echo "file {$input['old_filename']} not found.";
        }
    }

    function delete(Request $request){
        // $raw_data = file_get_contents("PHP://input");
        // $input = array_combine(array_map(function($data){
        //     return explode('=', $data)[0];
        // }, explode('&', $raw_data)), array_map(function($data){
        //     return explode('=', $data)[1];
        // }, explode('&', $raw_data)));
        $input = $request->all();
        $file = get_files_url(($input['path']??'public/').$input['filename']);
        if(is_dir($file)){
            if(url_parse($input['filename']) != ''){
                deleteDirectory($file);
                echo 'delete file success.';
            }else{
                echo "can't delete root directory.";
            }
        }else if(file_exists($file)){
            unlink($file);
            echo 'delete file success.';
        }else{
            echo 'file not found.';
        }
    }

    function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
    
        if (!is_dir($dir)) {
            return unlink($dir);
        }
    
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
    
            if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
    
        }
    
        return rmdir($dir);
    }

    function login(Request $request){
        $input = $request->all();
        $username = $input['username'];
        $password = $input['password'];
        if($username == BACKUP_USERNAME && md5($password) == BACKUP_PASSWORD){
            if(array_slice(explode('/', $request->uri), -1)[0] == "login") echo 1;
            return true;
        }else{
            if(array_slice(explode('/', $request->uri), -1)[0] == "login") echo 0;
            return false;
        }
    }

    function move(Request $request){
        $input = $request->all();
        $filename = $input['filename'];
        $file = get_files_url($filename);
        $dest = get_files_url($input['destination']);
        if(dirname($input['destination']) !== 'public' &&
           dirname($input['destination']) !== 'private' &&
           substr(($input['destination'].'/'), 0, 7) !== 'public/' &&
           substr(($input['destination'].'/'), 0, 8) !== 'private/'
        ){
            echo 'request denied.';
            exit;
        }
        // dd($file, $dest);
        if(isset($input['dir'])){
            if(is_dir($file)){
                if(is_dir($dest)){  
                    if(!is_dir($dest.'/'.basename($filename))){
                        rename($file, $dest.'/'.basename($file));
                        echo 'move success. current file list: ';
                        list_all($request);
                    }else{
                        echo "destination directory exists.";
                    }
                }else {
                    echo "destination fails.";
                }
            }else{
                echo "dir {$filename} not exists.";
            }
        }else{
            if(is_file($file)){
                if(is_dir($dest)){  
                    rename($file, $dest.'/'.basename($file));
                    echo 'move success. current file list: ';
                    list_all($request);
                }else if(is_file($dest)) {
                    echo "destination {$input['destination']} exists.";
                }else if(!is_dir(dirname($dest))){
                    echo "destination {$input['destination']} not exists.";
                }else{
                    rename($file, $dest);
                    echo 'move success. current file list: ';
                    list_all($request);
                }
            }else{
                echo "file {$filename} not exists.";
            }
        }
    }

    function publish(Request $request){
        $input = $request->all();

        try {
            $name = BACKUP_PUBLISH_PATH.$input['publish_path'].'/'.$input['filename'];
            $filename = url_parse($name);
            $temp = "";
            foreach(explode('/', url_parse($input['publish_path'].'/'.$input['filename'])) as $f){
                $temp .= $f;
                $t = url_parse(BACKUP_PUBLISH_PATH.dirname($temp));
                if(!is_dir($t)){
                    mkdir($t);
                }
                $temp .= '/';
            }
            if(file_exists($filename) && !is_dir($filename) && !isset($input['force'])){
                echo "file ".url_parse($input['publish_path'].'/'.$input['filename'])." already exists.";
            }else{
                file_put_contents($filename, base64_decode($input['raw_data']));
                echo "publish ".url_parse($input['publish_path'].'/'.$input['filename'])." success.";
            }
        } catch (\Exception $e) {
            echo "publish ".url_parse($input['publish_path'].'/'.$input['filename'])." fails.";
        }
    }  

    function clear_publish(Request $request){
        $input = $request->all();
        
        $directory = url_parse($input['directory']);
        if($directory == ''){
            echo "can't read root directory."; 
            exit;
        } 
        $directory = BACKUP_PUBLISH_PATH.$directory;

        if(is_dir($directory)){
            deleteDirectory($directory);
            echo "clear success.";
        }else{
            echo "{$directory} not exists.";
        }
    }

    function list_publish(Request $request){
        $input = $request->all();
        $directory = url_parse($input['directory']);
        if($directory == ''){
            echo "can't read root directory."; 
            exit;
        } 
        echo "File List: ".PHP_EOL;
        $directory = BACKUP_PUBLISH_PATH.$directory;
        show_files($directory);
    }

    function pull_publish(Request $request){
        $input = $request->all();
        $files = get_publish_files($input['directory'].'/');
        echo implode(',@%@', $files);
    }

    function get_publish_files($dir, $prefix=""){
        $url = url_parse(BACKUP_PUBLISH_PATH.'/'.$dir);
        $total_files = glob($url.'/'.'{,.}[!.,!..]*',GLOB_MARK|GLOB_BRACE);
        $files = array_filter($total_files, function($file){
            return is_file($file);
        });
        $dirs = array_filter($total_files, function($file){
            return is_dir($file);
        });
        foreach($dirs as $d){
            $files = array_merge($files, get_publish_files($dir.'/'.basename($d), $prefix.basename($d).'/'));
        }
        return array_map(function($file){
            return str_replace(BACKUP_PUBLISH_PATH, '', $file);
        }, $files);
    }

    function pull_download(Request $request){
        $_GET['filename'] = str_replace('@%@', '/', $_GET['filename']);
        $file = BACKUP_PUBLISH_PATH.$_GET['filename'];

        if(file_exists($file) && !is_dir($file)){
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:".filesize($file));
            header("Content-Disposition: attachment; filename=".basename($_GET['filename']));
            readfile($file);
        }else{
            echo "file {$_GET['filename']} not found.";
        }
    }