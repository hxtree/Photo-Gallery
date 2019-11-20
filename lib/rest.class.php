<?php
/*

REST Class

*/

class REST {

    public $status = array(
        200 => '200 OK',
        400 => '400 Bad Request',
        422 => 'Unprocessable Entity',
        500 => '500 Internal Server Error'
    );    
    
    function __construct() {
        // start output buffering
        // as to catture WARNING/ERROR stdout for response
        // ob_start();     
    }

    // send response as JSend
    // https://labs.omniti.com/labs/jsend
    function response($data){
        // check for premature output
        //$stdout = ob_get_contents();
        //ob_end_clean();
        //if($stdout != ''){
        //    $data = array(
        //        'status' => 'error',
        //        'message' => $stdout
        //    );
        //} 
        
        header_remove();
		http_response_code(200); // TODO: figure out
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Type: application/json');

        // calculate key
        if(isset($data['status'])){
            switch($data['status']){
                case 'success': 
                    header('Status: 200'); 
                    break;
                case 'fail': 
                    header('Status: 304'); 
                    break;
                case 'error': 
                    header('Status: 400'); 
                    break;
            }    
        }

        echo json_encode($data);
        die();
    }

    public function request($request){
        if($request['verbose']){
            echo '<p>'.$request['url'].' <b>'.$request['method'].'</b> '.print_r($request['parameters'],true).'</p>';
        }

        // validation
        $methods = array('POST','GET','PUT','PATCH','DELETE');
        if(!isset($request['url'])||!in_array($request['method'],$methods)){
            $this->response(array(
                'status' => 'error',
                'data' => 'invalid request'
            ));
        }

        // start curl
        $curl_handle = curl_init();	
        curl_setopt($curl_handle,CURLOPT_URL, $request['url']);
        curl_setopt($curl_handle,CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl_handle,CURLOPT_CUSTOMREQUEST, $request['method']);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT, 4);

        // SSL requirement hack 
        // INSECURE USED FOR DEV ONLY  
        // DO NOT USE THIS IN A PRODUCTION CLIENT
        if(isset($request['dev']) && ($request['dev'])){
            curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, false);
        }

        // pass security
        if(isset($request['username']) && isset($request['password'])){
            curl_setopt($curl_handle,CURLOPT_USERPWD, $request['username'].':'.$request['password']);
        }

        // if request contains a file then send data using post otherwise send as json 
        if(array_key_exists('file',$request['parameters'])){
            // send file and json
            if(isset($parameters['data'])){
                $parameters['data'] = json_encode($parameters['data']);
            }
            curl_setopt($curl_handle, CURLOPT_POST, 1);
           // curl_setopt($curl_handle, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Content-Type:multipart/form-data'));
        } else {
            // send json
            $request['parameters'] = json_encode($request['parameters']);	
            curl_setopt($curl_handle,CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        }

        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $request['parameters']);
        $json = curl_exec($curl_handle);

        // handout return and output
        if(!$json) {
            if($request['verbose']){
                echo curl_error($curl_handle);
            }
            return false;
        } else {
            curl_close($curl_handle);
            $response = json_decode($json, true);
        
            if($request['verbose']){
                echo '<pre>'.print_r($response,true).'</pre>';
            }		
            return $response;
        }
    }
}

?>
