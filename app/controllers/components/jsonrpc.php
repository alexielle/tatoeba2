<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009-2010  HO Ngoc Phuong Trang <tranglich@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Tatoeba
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

/**
 * This is the API server component for the Tatoeba Android app
 * These functions are only meant to process JSON-RPC data
 * To initialize this component in a controller do the following:
 * 
 * public $components = array(
 *     'Jsonrpc' => array(
 *         'listen' => array('some_action')
 *     )
 * );
 *
 * @category API
 * @package  Components
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

class JsonrpcComponent extends Object
{
    
    /**
     * The other components needed
     * 
     * @var array
     */
    public $components = array("RequestHandler");
    
    
    /**
     * The actions of the controller to be used as a listener
     * 
     * @var mixed
     */
    public $listen = null;
    
    
    /**
     * The controller of this component
     * 
     * @var controller
     */
    private $_controller = null;
    
    
    /**
     * Current JSON-RPC version
     * 
     * @var string
     */
    private $_version = "2.0";
    
    
    /**
     * Callback function called after Controller::beforeFilter() but
     * before the controller executes the action
     * This basically intercepts the request for the controller action 
     * 
     * @param &$controller The controller
     */
    function startup(object& $controller)
    {
        $request = getDecodedJSONRequest();
        HttpResponse::setContentType('application/json');
        $response = null;
        
        $this->_controller = $controller;
        if(!$this->RequestHandler->isPost()) {
            //Method Not Allowed
            HttpResponse::status(405);
            $response = $this->_createRequestError("Request method must be HTTP POST");
            
        } else if(empty($request)) {
            HttpResponse::status(405);
            $response = $this->_createRequestError("No method requested.");
            
        } else if(empty($this->listen) || !is_string($this->listen) && !is_array($this->listen)) {
            //Internal Server Error
            //If this component wasn't initialized properly in the controller
            HttpResponse::status(500);
            $response = $this->_createInternalError("Component not initialized properly. The listen variable
                of the controller must be a non-empty string or array of strings.");
            
        } else if (is_string($this->listen) && $this->listen !== $controller->action) {
            //Resource Not Found
            HttpResponse::status(404);
            $response = $this->_createMethodError("Requested action is not registered as an API method.
                Include the method name in the listen variable to register it.");
            
        } else if (is_array($this->listen) && !in_array($controller->action, $this->listen)) {
            //Resource Not Found
            HttpResponse::status(404);
            $response = $this->_createMethodError("Requested action is not registered as an API method.
                Include the method name in the listen variable to register it.");
            
        } else if(!is_array($request)) {
            //Unable to decode JSON request
            HttpResponse::status(500);
            $response = $this->_createInternalError("Unable to decode JSON response.");
            
        } else {
            $response = callAction($request);
        }
        
        sendEncodedJSONResponse($response);
    }
    
    
    /**
     * Used to create a JSON-RPC error
     * 
     * @param int $code       The error code
     * @param string $message The error message
     * @param int $id         The ID of the JSON request
     * 
     * @return object A JSON error
     */
    private function _createError($code, $message, $id=null)
    {
        $object = new stdClass();
        $object->jsonrpc = $this->_version;
        $object->error = new stdClass();
        $object->error->code = $code;
        $object->error->message = $message;
        $object->id = $id;
        return $object;
    }
    
    
    /**
     * Creates a parsing error
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createParseError($message='Error parsing')
    {
        return $this->_createError(-32700, $message, null);
    }
    
    
    /**
     * Creates a parsing error
     * For bad requests
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createRequestError($message='Bad request')
    {
        return $this->_createJsonError(-32600, $message, null);
    }
    
    
    /**
     * Creates a method error
     * For non-existant methods
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createMethodError($message='Method not found')
    {
        return $this->_createJsonError(-32601, $message, null);
    }
    
    
    /**
     * Creates a parameter error
     * Something invalid with the params
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createParamsError($message='Invalid params')
    {
        return $this->_createError(-32602, $message, null);
    }
    
    
    /**
     * Creates a internal error
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createInternalError($message='Internal error')
    {
        return $this->_createError(-32603, $message, null);
    }
    
    
    /**
     * Creates a server error
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createServerError($message='Server error')
    {
        return $this->_createError(-32000, $message, null);
    }
    
    
    /**
     * Creates a server error
     * Generic error at the app level
     * 
     * @param $message  Error message
     * 
     * @return object A JSON error
     */
    private function _createApplicationError($code, $message="Unknown Error")
    {
        return $this->_createError($code, $message, null);
    }
    
    /**
     * Fetches, decodes and returns a JSON request
     * 
     * @return array The results from PHP's built in JSON parser
     */
    protected function getDecodedJSONRequest()
    {
        $jsonData = trim(file_get_contents("php://input"));
        $jsonData = str_replace("\'", "\"", $jsonData);
        $allowedChars = array(" " , "," , ":" , "[" , "]" , "{" , "}" , "\"" , "|");
        $jsonData = Sanatize::paranoid($jsonData, $allowedChars);
        return json_decode($jsonData, true);
    }
    
    
    /**
     * Encodes and ships a JSON response
     * Set the HTTP Status before calling this
     * 
     * @param array $jsonData JSON data
     * 
     * @return void
     */
    protected function sendEncodedJSONResponse($jsonData)
    {
        $jsonData = json_encode($jsonData);
        //Optional: Set the Status depending on what the request accomplished
        HttpResponse::status(200);
        HttpResponse::setData($jsonData);
        HttpResponse::send();
        //$this->_log();
    }
    
    
    /**
     * Call the controller method and get the data
     * 
     * @param object $jsonRequest The output from getDecodedJSONRequest()
     * 
     * @return array 
     */
    protected function callAction($jsonRequest)
    {
        if(!isset($jsonRequest->jsonrpc) || $jsonRequest->jsonrpc !== $this->_version) {
            return $this->_createRequestError("Requested JSONRPC version does not exist. Please 
                use version {$this->_version}");
            
        } else if (!isset($jsonRequest->method) || !method_exists($this->_controller, $jsonRequest->method)) {
            return $this->_createMethodError("Requested method does not exist on controller. Please create 
                {$jsonReqeust->method} for {$this->_controller}Controller");
            
        } else if(!isset($jsonRequest->params) || (!is_array($request->params) && !is_object($request->params))) {
            return $this->_createParamsError();
        }
        
        try {
            //Don't let the controller send any output to the browser
            ob_start();
            $results = call_user_func_array(
                array($this->_controller, $jsonRequest->method),
                array($jsonRequest->params)
            );
            ob_end_clean();
            return $results;
        } catch (Exception $e) {
            return $this->_createApplicationError($e->getCode(), $e->getMessage(), null);
        }
    }
    
    /**
     * For logging records of API activity
     */
    protected function _log()
    {
        
    }
    
}

?>