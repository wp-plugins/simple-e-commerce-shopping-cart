<?php
class SimpleEcommCartTaxRate extends SimpleEcommCartModelAbstract {
  
  protected $_canada = array(
    "AB" => 'Alberta',
    "BC" => 'British Columbia',
    "MB" => 'Manitoba',
    "NB" => 'New Brunswick',
    "NF" => 'Newfoundland',
    "NT" => 'Northwest Territories',
    "NS" => 'Nova Scotia',
    "NU" => 'Nunavut',
    "ON" => 'Ontario',
    "PE" => 'Prince Edward Island',
    "PQ" => 'Quebec',
    "SK" => 'Saskatchewan',
    "YT" => 'Yukon Territory'
  );
  
  protected $_usa = array(
    'All States' => 'All States',
    'AL' => 'Alabama',
    'AK' => 'Alaska',
    'AZ' => 'Arizona',
    'AR' => 'Arkansas',
    'CA' => 'California',
    'CO' => 'Colorado',
    'CT' => 'Connecticut',
    'DC' => 'District of Columbia',
    'DE' => 'Delaware',
    'FL' => 'Florida',
    'GA' => 'Georgia',
    'HI' => 'Hawaii',
    'ID' => 'Idaho',
    'IL' => 'Illinois',
    'IN' => 'Indiana',
    'IA' => 'Iowa',
    'KS' => 'Kansas',
    'KY' => 'Kentucky',
    'LA' => 'Louisiana',
    'ME' => 'Maine',
    'MD' => 'Maryland',
    'MA' => 'Massachusetts',
    'MI' => 'Michigan',
    'MN' => 'Minnesota',
    'MS' => 'Mississippi',
    'MO' => 'Missouri',
    'MT' => 'Montana',
    'NE' => 'Nebraska',
    'NV' => 'Nevada',
    'NH' => 'New Hampshire',
    'NJ' => 'New Jersey',
    'NM' => 'New Mexico',
    'NY' => 'New York',
    'NC' => 'North Carolina',
    'ND' => 'North Dakota',
    'OH' => 'Ohio',
    'OK' => 'Oklahoma',
    'OR' => 'Oregon',
    'PA' => 'Pennsylvania',
    'RI' => 'Rhode Island',
    'SC' => 'South Carolina',
    'SD' => 'South Dakota',
    'TN' => 'Tennessee',
    'TX' => 'Texas',
    'UT' => 'Utah',
    'VT' => 'Vermont',
    'VA' => 'Virginia',
    'WA' => 'Washington',
    'WV' => 'West Virginia',
    'WI' => 'Wisconsin',
    'WY' => 'Wyoming'
  );
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('tax_rates');
    parent::__construct($id);
  }
  
  public function loadByZip($zip) {
    $isLoaded = false;
    
    if($zip != null) {
      if(strpos($zip, '-') > 0) {
        // only use first part of hyphenated zip codes
        $zip = array_shift(explode('-', $zip));
      }
      if(is_numeric($zip) && $zip > 0) {
        $sql = "SELECT * from $this->_tableName where zip_low <= $zip AND zip_high >= $zip";
        if($row = $this->_db->get_row($sql, ARRAY_A)) {
          $this->setData($row);
          $isLoaded = true;
        }
      }
    }
    
    return $isLoaded;
  }
  
  /**
   * First check to see if an individual state is taxed, if not check for all sales tax.
   * The individual sales tax rates take precedence over the all sales tax rate.
   */
  public function loadByState($state) {
    $isLoaded = false;
    
    if(strlen($state) > 2) {
      $state = $this->getStateAbbreviation($state);
    }
    
    $state = strtoupper($state);
    
    $sql = "SELECT * from $this->_tableName where state='$state'";
    if($row = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($row);
      $isLoaded = true;
    }
    else {
      $sql = "SELECT * from $this->_tableName where state='All Sales'";
      if($row = $this->_db->get_row($sql, ARRAY_A)) {
        $this->setData($row);
        $isLoaded = true;
      }
    }
    
    return $isLoaded;
  }
  
  public function getFullStateName($state=null) {
    if(is_null($state)) {
      $state = $this->state;
    }
    $allStates = array_merge($this->_canada, $this->_usa);
    return $allStates[$state];
  }
  
  public function getStateAbbreviation($stateName) {
    $allStates = array_merge($this->_canada, $this->_usa);
    foreach($allStates as $abbr => $name) {
      $allStates[$abbr] = strtolower($name);
    }
    $stateName = strtolower($stateName);
    return array_search($stateName, $allStates);
  }
  
}