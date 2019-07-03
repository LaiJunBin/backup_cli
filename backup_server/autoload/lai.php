<?php

    class Lai {
        public static function decryptFile($filename, $params) {
            $html_array = [];
            $html_file = fopen($filename, 'r');
            while(($line = fgets($html_file)) !== false){
                $html_array[] = $line;
            }
            fclose($html_file);

            self::_extends($html_array);
            self::_include($html_array);
            self::_yield($html_array, $params);
            self::_section($html_array, $params);
            self::_decrypt_for_expression($html_array, $params);
            self::_decrypt_if_expression($html_array, $params);
            self::_decrypt($html_array, $params);

            $html_text = implode(' ', $html_array);
            preg_match_all('/{{\s*([^}]*)\s*}}/', $html_text, $matches);

            foreach($params as $key =>$value){
                $$key = $value;
            }

            for($i = 0; $i < count($matches[0]); $i++){
                $syntax = "return {$matches[1][$i]} ?? '';";
                $html_text = str_replace($matches[0][$i], htmlspecialchars(eval($syntax)), $html_text);
            }

            return $html_text;
        }

        private static function _extends(&$html_array){
            $check_extends = true;
            while($check_extends){
                $check_extends = false;
                for($i = 0; $i < count($html_array); $i++){
                    if(mb_strpos(trim($html_array[$i]), '@extends') === 0){
                        $check_extends = true;
                        $extend_file = trim(self::get_condition($html_array[$i]),'\'');
                        $template_dir = ['app', 'views'];
                        array_push($template_dir, ...explode('.', $extend_file));
                        $file = implode('/', $template_dir).'.lai.php';
                        if(!file_exists($file)){
                            throw new Error('extend template error!, template not found.');
                        }
                        $html_file = fopen($file, 'r');
                        $array = [];
                        while(($line = fgets($html_file)) !== false){
                            $array[] = $line;
                        }
                        fclose($html_file);
                        array_splice($html_array, $i, 1, $array);
                        break;
                        // $html_array = $array;
                    }
                }

            }
        }

        private static function _include(&$html_array){
            $check_include = true;
            while($check_include){
                $check_include = false;
                for($i = 0; $i < count($html_array); $i++){
                    if(mb_strpos(trim($html_array[$i]), '@include') === 0){
                        $check_include = true;
                        $include_file = trim(self::get_condition($html_array[$i]),'\'');
                        $template_dir = ['app', 'views'];
                        array_push($template_dir, ...explode('.', $include_file));
                        $file = implode('/', $template_dir).'.lai.php';
                        if(!file_exists($file)){
                            throw new Error('include template error!, template not found.');
                        }
                        $html_file = fopen($file, 'r');
                        $array = [];
                        while(($line = fgets($html_file)) !== false){
                            $array[] = $line;
                        }
                        fclose($html_file);
                        array_splice($html_array, $i, 1, $array);
                        break;
                        // $html_array = $array;
                    }
                }

            }
        }

        private static function _yield(&$html_array, &$params){
            for($i = 0; $i < count($html_array); $i++){
                if(mb_strpos(trim($html_array[$i]), '@yield') !== false){
                    $yield_name = self::get_condition($html_array[$i]);
                    $variable = array_map(function($v){
                        return trim(trim($v), '\'');
                    }, explode(',', $yield_name));
                    $html_array[$i] = preg_replace("/@yield\(".$variable[0]."[^)]*\)/", '{{ $yield_'. $variable[0]. ' }}', $html_array[$i]);
                    if(count($variable) == 2){
                        $params['yield_'.$variable[0]] = $variable[1];
                    }
                }
            }
        }

        private static function _section(&$html_array, &$params){
            for($i = 0; $i < count($html_array); $i++){
                if(mb_strpos(trim($html_array[$i]), '@section') !== false){
                    $section_name = self::get_condition($html_array[$i]);
                    $variable = array_map(function($v){
                        return trim(trim($v), '\'');
                    }, explode(',', $section_name));

                    if(count($variable) == 1){
                        $stack = [];
                        for($j = $i; $j < count($html_array); $j++){
                            preg_match_all('/{/', $html_array[$j], $left);
                            if(count($left[0]) > 0)
                                array_push($stack, ...$left[0]);

                            preg_match_all('/}/', $html_array[$j], $right);
                            array_splice($stack, 0, count($right[0]));

                            if(count($stack) == 0){
                                $temp = array_slice($html_array, $i+1, $j-$i-1);

                                array_splice($html_array, $i, $j-$i+1, []);

                                for($k = 0; $k < count($html_array); $k++){
                                    if(mb_strpos(trim($html_array[$k]), '{{ $yield_'.$section_name.' }}') !== false){
                                        array_splice($html_array, $k, 1, $temp);
                                    }
                                }
                                break;
                            }
                        }
                    }else if(count($variable) == 2){
                        $params['yield_'.$variable[0]] = $variable[1];
                        array_splice($html_array, $i, 1, []);
                    }
                }
            }
        }

        private static function _decrypt_for_expression(&$html_array, $params){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            for($i = 0; $i < count($html_array); $i++){
                if(mb_strpos(trim($html_array[$i]), '@foreach') === 0){
                    $stack = [];
                    for($j = $i; $j < count($html_array); $j++){
                        preg_match_all('/{/', $html_array[$j], $left);
                        if(count($left[0]) > 0)
                            array_push($stack, ...$left[0]);

                        preg_match_all('/}/', $html_array[$j], $right);
                        array_splice($stack, 0, count($right[0]));

                        if(count($stack) == 0){
                            $condition = self::get_condition($html_array[$i]);
                            list($start, $end) = [$i, $j];
                            $temp = array_slice($html_array, $i+1, $j-$i-1);
                            $array = [];
                            $index_variable = trim(mb_substr($condition, mb_strpos($condition, 'as')+2));
                            $syntax = ('foreach('.$condition.'){ $params[mb_substr($index_variable,1)] = '.$index_variable.'; array_push($array, ...self::for_assign_variable($temp, $params, $index_variable)); }');
                            eval($syntax);
                            array_splice($html_array, $start, $end-$start+1, $array);
                            $i = -1;
                            break;
                        }
                    }
                }else if(mb_strpos(trim($html_array[$i]), '@for') === 0){
                    $stack = [];
                    for($j = $i; $j < count($html_array); $j++){
                        preg_match_all('/{/', $html_array[$j], $left);
                        if(count($left[0]) > 0)
                            array_push($stack, ...$left[0]);

                        preg_match_all('/}/', $html_array[$j], $right);
                        array_splice($stack, 0, count($right[0]));

                        if(count($stack) == 0){
                            $condition = self::get_condition($html_array[$i]);
                            list($start, $end) = [$i, $j];
                            $temp = array_slice($html_array, $i+1, $j-$i-1);
                            $array = [];
                            $index_variable = trim(mb_substr($condition, 0, mb_strpos($condition, '=')));
                            $syntax = ('for('.$condition.'){ $params[mb_substr($index_variable,1)] = '.$index_variable.'; array_push($array, ...self::for_assign_variable($temp, $params, $index_variable)); }');
                            eval($syntax);
                            array_splice($html_array, $start, $end-$start+1, $array);
                            $i = -1;
                            break;
                        }
                    }
                }
            }
        }

        private static function _decrypt_if_expression(&$html_array, $params){

            foreach($params as $key =>$value){
                $$key = $value;
            }

            for($i = 0; $i < count($html_array); $i++){
                // convert if else to if + if
                if(preg_match('/}\s*else\s*{/', $html_array[$i])){

                    $stack = [];
                    for($j = $i-1; $j >= 0; $j--){
                        if(mb_strpos(trim($html_array[$j]),'}') === 0){
                            array_push($stack, '}');
                            continue;
                        }
                        if(mb_strpos(trim($html_array[$j]),'@if') === 0){
                            if(count($stack) == 0){
                                $condition = self::get_condition($html_array[$j]);
                                $array = ['}', "@if(!({$condition})){"];
                                array_splice($html_array, $i, 1, $array);
                                break;
                            }else{
                                array_pop($stack);
                            }
                        }
                    }
                }

                // convert else if to if > if
                if(preg_match('/}\s*else if\s*(.*){/', $html_array[$i])){
                    $stack = [];
                    for($j = $i-1; $j >= 0; $j--){
                        if(mb_strpos(trim($html_array[$j]),'}') === 0){
                            array_push($stack, '}');
                            continue;
                        }
                        if(mb_strpos(trim($html_array[$j]),'@if') === 0){
                            if(count($stack) == 0){
                                $condition = self::get_condition($html_array[$j]);
                                $condition2 = self::get_condition($html_array[$i]);
                                $array = ['}', "@if(!({$condition})){"];
                                $array2 = ["@if({$condition2}){"];
                                array_splice($html_array, $i+1,0,$array2);
                                array_splice($html_array, $i, 1, $array);
                                $stack = [];
                                for($k = $i+2; $k < count($html_array); $k++){
                                    preg_match_all('/{/', $html_array[$k], $left);
                                    if(count($left[0]) > 0)
                                        array_push($stack, ...$left[0]);

                                    preg_match_all('/}/', $html_array[$k], $right);
                                    array_splice($stack, 0, count($right[0]));

                                    if(count($stack) == 0){
                                        array_splice($html_array, $k+1, 0, '}');
                                        break;
                                    }
                                }

                                break;
                            }else{
                                array_pop($stack);
                            }
                        }
                    }
                }
            }

        }

        private static function get_expression($html_text){
            preg_match_all('/@([^{]*{([^}]*))/', $html_text, $expressions);
            return $expressions;
        }

        private static function find_brackets($array, $left_bracket, $right_bracket){
            if(!is_array($array)){
                $array = preg_split('//', $array);
                clearEmpty($array);
            }

            $left = -1;
            $right = -1;
            for($i = count($array)-1; $i >= 0; $i--){
                $row = trim($array[$i]);
                if(mb_strpos($row, '@') === 0 && mb_strpos($row, $left_bracket) !== false){
                    $left = $i;
                    break;
                }
            }

            if($left == -1)
                return [-1, -1];

            for($i = $left; $i < count($array); $i++){
                $row = trim($array[$i]);
                if(mb_strpos($row, $right_bracket) === 0){
                    $right = $i;
                    break;
                }
            }

            if($right == -1)
                return [-1, -1];

            return [$left, $right];
        }

        private static function get_condition($expression){
            $left = mb_strpos($expression, '(');
            $right = mb_strripos($expression, ')');
            return mb_substr($expression, $left+1, $right-$left-1);
        }

        private static function _decrypt(&$html_array, $params){
            list($left, $right) = self::find_brackets($html_array, '{', '}');

            while($left != -1 && $right != -1){
                $expression = trim($html_array[$left]);
                if(mb_strpos($expression, '@') === 0){
                    $function_name = '_'.trim(mb_substr($expression, 1, mb_strpos($expression, '(')-1));
                    self::$function_name($html_array, $expression, $left, $right, $params);
                }
                list($left, $right) = self::find_brackets($html_array, '{', '}');
            }
        }

        private static function _if(&$html_array, $expression, $left, $right, $params=[]){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $condition = self::get_condition($expression);

            $condition = eval("return {$condition};");

            if($condition){
                $array = array_slice($html_array, $left+1, $right-$left-1);
                array_splice($html_array, $left+1, $right-$left);
                $html_array[$left] = implode(' ',$array);
            }else{
                array_splice($html_array, $left, $right-$left+1);
            }
        }

        private static function _for(&$html_array, $expression, $left, $right, $params=[]){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $condition = self::get_condition($expression);
            $temp = array_slice($html_array, $left+1, $right-$left-1);
            $array = [];
            $index_variable = mb_substr($condition, 0, mb_strpos($condition, '=')-1);
            $syntax = ('for('.$condition.'){ $params[mb_substr($index_variable,1)] = '.$index_variable.'; array_push($array, ...self::for_assign_variable($temp, $params)); }');
            eval($syntax);
            array_splice($html_array, $left+1, $right-$left);
            $html_array[$left] = implode(' ',$array);
        }

        private static function for_assign_variable($temp, $params, $index_variable){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $array = [];
            for($params['for1'] = 0; $params['for1'] < count($temp); $params['for1']++){
                preg_match_all('/{{\s+([^}]*)\s+}}/', $temp[$params['for1']], $matches);

                if(preg_match('/\\'.$index_variable.'\W/', $temp[$params['for1']])){
                    if(is_string($params[mb_substr($index_variable,1)])){
                        $array[$params['for1']] = preg_replace('/\\'.$index_variable.'/', '\''.$params[mb_substr($index_variable,1)].'\'', $temp[$params['for1']]);
                    }else{
                        $array[$params['for1']] = preg_replace('/\\'.$index_variable.'/', '\''.$params[mb_substr($index_variable,1)].'\'', $temp[$params['for1']]);
                    }
                }else{
                    $array[$params['for1']] = $temp[$params['for1']];
                }
            }

            return $array;
        }

    }