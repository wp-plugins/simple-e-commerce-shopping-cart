<?php
abstract class SimpleEcommCartBaseModelAbstract {
  
  protected $_data;
  protected $_errors;
  protected $_jqErrors;
  
  /**
   * Overwrite any common keys with the values from the passed in array.
   * If the optional $replace parameter is true, then the object is cleared before the new data is set.
   * 
   * @param array $data The assoc array used to populate data
   * @param boolean $replace If true, object is cleared before data is set
   * @return void
   */
  public function setData(array $data, $replace=false) {
    if($replace) {
      $this->clear();
    }
    
    foreach($data as $key => $value) {
      if(array_key_exists($key, $this->_data)) {
        if($key == 'id') {
          if(is_numeric($value) && $value > 0) {
            $this->_data[$key] = $value;
          }
          else {
            $this->_data[$key] = null;
          }
        }
        else {
          $this->_data[$key] = $value;
        }
      }
    }
  }
    
  public function getData() {
    return $this->_data;
  }
  
  public function dumpData() {
    echo '<pre>';
    print_r($this->_data);
    echo '</pre>';
  }
  
  public function getErrors() {
    if(!is_array($this->_errors)) {
      $this->_errors = array();
    }
    return $this->_errors;
  }

  public function getJqErrors() {
    if(!is_array($this->_jqErrors)) {
      $this->_jqErrors = array();
    }
    return $this->_jqErrors;
  }
  
  public function setErrors(array $errors) {
    $this->_errors = $errors;
  }
  
  public function addError($key, $value, $formFieldId=null) {
    $this->_errors[$key] = $value;
    if(isset($formFieldId)) {
      $this->_jqErrors[] = $formFieldId;
    }
  }
  
  public function clearErrors() {
    $this->_errors = array();
    $this->_jqErrors = array();
  }
  
  public function hasErrors() {
    $hasErrors = (count($this->_errors) > 0);
    return $hasErrors;
  }
  
  public function clear() {
    foreach($this->_data as $key => $value) {
      $this->_data[$key] = '';
    }
    if(isset($this->_data['id'])) {
      $this->_data['id'] = null;
    }
  }
  
  protected function _camelToSnake($name) {
		$pattern = "/([A-Z])/";
		$replace = "_$1";
		$name = strtolower(preg_replace($pattern, $replace, $name));
		return $name;
	}
	
	/**
   * Return a camelCase string based on the given snake_case value.
   *
   * If the optional $lcFirst parameter is true, the first letter of 
   * the returned value is lower case like this lowerCase otherwise 
   * the first letter is upper case like this UpperCase.
   *
   * @param string $val
   * @param boolean $lcFirst
   * @return string
   */
  protected function _snakeToCamel($val, $lcFirst=true) {
    $val = str_replace(' ', '', ucwords(str_replace('_', ' ', $val)));
    if($lcFirst) {
      $val = strtolower(substr($val,0,1)).substr($val,1); 
    }
    return $val;
  }
  
  public function __get($key) {
    $key = $this->_camelToSnake($key);
    $value = false;
    $funcName = "_get" . $this->_snakeToCamel($key, false);
    if(method_exists($this, $funcName)) {
      $value = $this->{$funcName}();
    }
    elseif(array_key_exists($key, $this->_data)) {
      $value = $this->_data[$key];
    }
    
    return $value;
  }
  
  public function __set($key, $value) {
    $key = $this->_camelToSnake($key);
    if(array_key_exists($key, $this->_data)) {
      // Check for hook to override setting incoming data using _setKeyName() as the expected function
      $funcName = "_set" . $this->_snakeToCamel($key);
      if(method_exists($this, $funcName)) {
        $value = $this->{$funcName}($value);
      }
      else {
        $this->_data[$key] = $value;
      }
    }
  }
  
  public function __isset($key) {
    $key = $this->_camelToSnake($key);
    return isset($this->_data[$key]);
  }
  
}