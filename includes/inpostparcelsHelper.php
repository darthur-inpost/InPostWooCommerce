<?php

class inpostparcelsHelper
{

    public static function test(){
        return 'test';        
    }

    public static function connectInpostparcels($params = array()){

        $params = array_merge(
            array(
                'url' => $params['url'],
                'token' => $params['token'],
                'ds' => '?',
                'methodType' => $params['methodType'],
                'params' => $params['params']
            ),
            $params
        );

        $ch = curl_init();

        switch($params['methodType']){
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET') );
                $getParams = null;
                if(!empty($params['params'])){
                    foreach($params['params'] as $field_name => $field_value){
                        $getParams .= $field_name.'='.urlencode($field_value).'&';
                    }
                    curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token'].'&'.$getParams);
                }else{
                    curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token']);
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                break;

            case 'POST':
                $string = json_encode($params['params']);
                #$string = $params['params'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: POST') );
                curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($string))
                );
                break;

            case 'PUT':
                $string = json_encode($params['params']);
                #$string = $params['params'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT') );
                curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($string))
                );
                break;

        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return array(
            'result' => json_decode(curl_exec($ch)),
            'info' => curl_getinfo($ch),
            'errno' => curl_errno($ch),
            'error' => curl_error($ch)
        );
    }

    public static function generate($type = 1, $length){
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";

        if($type == 1){
            # AZaz09
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        }elseif($type == 2){
            # az09
            $chars = "abcdefghijklmnopqrstuvwxyz1234567890";
        }elseif($type == 3){
            # AZ
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }elseif($type == 4){
            # 09
            $chars = "0123456789";
        }

        $token = "";
            for ($i = 0; $i < $length; $i++) {
                $j = rand(0, strlen($chars) - 1);
                if($i==0 && $j == 0){
                    $j = rand(2,9);
                }
                $token .= $chars[$j];
            }
        return $token;
    }

    public static function getParcelStatus(){
        return array(
            'Created' => 'Created',
            'Prepared' => 'Prepared'
        );
    }

    public static function calculateDimensions($product_dimensions = array(), $config = array()){
        $parcelSize = 'A';
        $is_dimension = true;

        if(!empty($product_dimensions)){
            $maxDimensionFromConfigSizeA = explode('x', strtolower(trim($config['MAX_DIMENSION_A'])));
            $maxWidthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[0]);
            $maxHeightFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[1]);
            $maxDepthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[2]);
            // flattening to one dimension
            $maxSumDimensionFromConfigSizeA = $maxWidthFromConfigSizeA + $maxHeightFromConfigSizeA + $maxDepthFromConfigSizeA;

            $maxDimensionFromConfigSizeB = explode('x', strtolower(trim($config['MAX_DIMENSION_B'])));
            $maxWidthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[0]);
            $maxHeightFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[1]);
            $maxDepthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[2]);
            // flattening to one dimension
            $maxSumDimensionFromConfigSizeB = $maxWidthFromConfigSizeB + $maxHeightFromConfigSizeB + $maxDepthFromConfigSizeB;

            $maxDimensionFromConfigSizeC = explode('x', strtolower(trim($config['MAX_DIMENSION_C'])));
            $maxWidthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[0]);
            $maxHeightFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[1]);
            $maxDepthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[2]);

            if($maxWidthFromConfigSizeC == 0 || $maxHeightFromConfigSizeC == 0 || $maxDepthFromConfigSizeC == 0){
                // bad format in admin configuration
                $is_dimension = false;
            }
            // flattening to one dimension
            $maxSumDimensionFromConfigSizeC = $maxWidthFromConfigSizeC + $maxHeightFromConfigSizeC + $maxDepthFromConfigSizeC;
            $maxSumDimensionsFromProducts = 0;
            foreach($product_dimensions as $product_dimension){
                $dimension = explode('x', $product_dimension);
                $width = trim(@$dimension[0]);
                $height = trim(@$dimension[1]);
                $depth = trim(@$dimension[2]);
                if($width == 0 || $height == 0 || $depth){
                    // empty dimension for product
                    continue;
                }

                if(
                    $width > $maxWidthFromConfigSizeC ||
                    $height > $maxHeightFromConfigSizeC ||
                    $depth > $maxDepthFromConfigSizeC
                ){
                    $is_dimension = false;
                }

                $maxSumDimensionsFromProducts = $maxSumDimensionsFromProducts + $width + $height + $depth;
                if($maxSumDimensionsFromProducts > $maxSumDimensionFromConfigSizeC){
                    $is_dimension = false;
                }
            }
            if($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeA){
                $parcelSize = 'A';
            }elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeB){
                $parcelSize = 'B';
            }elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeC){
                $parcelSize = 'C';
            }
        }

        $parcelSizeRemap = array(
            'UK' => array(
                'A' => 'S',
                'B' => 'M',
                'C' => 'L'
            ),
            'PL' => array(
                'A' => 'M',
                'B' => 'S',
                'C' => 'D'
            )
        );

        return array(
            //'parcelSize' => $parcelSizeRemap[self::getCurrentApi()][$parcelSize],
            'parcelSize' => $parcelSize,
            'isDimension' => $is_dimension
        );
    }

    public static function getCurrentApi(){
        require_once(CLASSPATH ."shipping/inpostparcels.cfg.php");

        $currentApi = 'UK';
        if(ALLOWED_COUNTRY && !is_array(ALLOWED_COUNTRY)){
            $currentApi = ALLOWED_COUNTRY;
            if($currentApi == 'GB'){
                $currentApi = 'UK';
            }
        }


        return $currentApi;
    }

    public static function setLang(){
        global $mosConfig_absolute_path;

        $jlang = JFactory::getLanguage();

        $file = 'english';
        switch($jlang->getTag()){
            case 'en-GB';
                $file = 'english';
                break;
            case 'pl-PL';
                $file = 'polish';
                break;
        }

        if(file_exists(CLASSPATH.'shipping/inpostparcels/languages/'.$file.'.php')){
            require_once(CLASSPATH.'shipping/inpostparcels/languages/'.$file.'.php');
        }else{
            require_once($mosConfig_absolute_path.'/administrator/components/com_virtuemart/classes/shipping/inpostparcels/languages/'.$file.'.php');
        }
    }

    public static function getVersion(){
        return '1.0.0';
    }

    public static function getGeowidgetUrl(){

        switch(self::getCurrentApi()){
            case 'UK':
                return 'https://geowidget.inpost.co.uk/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function';
                break;

            case 'PL':
                return 'https://geowidget.inpost.pl/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function';
                break;
        }

    }

}