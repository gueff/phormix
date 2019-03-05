<?php

/**
 * Phormix.php
 *
 * @package Phormix
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See doc/COPYING
 */

/**
 * Phormix
 *
 * Examples:
 *      $oPhormix = new Phormix();
 *      $oPhormix->init('/var/www/App/formular.json')
 *          ->run();
 *
 *      $oPhormix = new Phormix();
 *      $oPhormix->setSessionPrefix('myPhormixCheck')
 *          ->init('/var/www/App/formular.json')
 *          ->run();
 *
 *      $oPhormix = new Phormix();
 *      $oPhormix->setConfigArrayFromJsonFile($sAbsPathToConfigFile)
 *          ->setIdentifier($sIdentifier)
 *          ->run();
 *
 *      $oPhormix = new Phormix();
 *      $oPhormix->setConfigArray($sAbsPathToConfigFile)
 *          ->setIdentifier($sIdentifier)
 *          ->run();
 *
 *      $oPhormix = new Phormix();
 *      $oPhormix->setSessionPrefix('myPhormixCheck')
 *          ->setConfigArray($sAbsPathToConfigFile)
 *          ->setIdentifier($sIdentifier)
 *          ->run();
 */
class Phormix
{
	/**
	 * sesison prefix / namespace
     * @access protected
	 * @var string 
	 */
	protected $_sSessionPrefix = 'Phormix';
	
	/**
	 * config
     * @access protected
	 * @var array
	 */
	protected $_aConfig = array();
	
	/**
	 * error
     * @access protected
	 * @var array
	 */
	protected $_aError = array();
	
	/**
	 * message
     * @access protected
	 * @var array
	 */
	protected $_aMessage = array();
	
	/**
	 * missing
     * @access protected
	 * @var array
	 */
	protected $_aMissing = array();

	/**
	 * status as result of check() method
     * @access protected
	 * @var boolean
	 */
	protected $_bStatus = false;
	
	/**
	 * ticket
     * @access public
	 * @var string
	 */
	public $sTicket = '';
	
	/**
	 * unique identifier
     * @access protected
	 * @var string
	 */
	protected $_sIdentifier = '';

    /**
     * runtime log
     * @access protected
     * @var array
     */
    protected static $_aLog = array();

    /**
     * name of validate class
     * @access protected
     * @var string
     */
    protected $_sValidate = '\PhormixValidate';
    
    /**
     * name of sanitize class
     * @access protected
     * @var string
     */
    protected $_sSanitize = '\PhormixSanitize';
    
    /**
     * prefix of methods in validate & sanitize classes
     * (prefix is necessary to avoid misusing php-reserved words as methodnames; e.g. "empty")
     * @access protected
     * @var string
     */
    protected $_sMethodPrefix = '_';
    
	/**
	 * contains checked form data
     * @access protected
	 * @var array
	 */
	protected $_aFormDataChecked = array();
	
    /**
     * array containing sent data by form
     * @access protected
     * @var array
     */
    protected $_aFormData = array();       
    
    /**
     * Phormix constructor.
     * @access public
     */
	public function __construct()
	{
        $this->startSession();
	}

	//--------------------------------------------------------------------------
	// Getter
	
    /**
     * returns data sent by form
     * @access public
     * @return array 
     */
	public function getFormDataArray()
	{        
        if (!empty($this->_aFormData))
        {
            return $this->_aFormData;
        }    
 
        // get data sent by form
        $aRequest = $GLOBALS['_' . strtoupper($this->_aConfig['method'])];
        
        // make sure sent data contain the correct ticket (from this instance of this class)
        // if it does not contain the correct ticket, sent data is from another form or source
        if  (
                    !isset($_SESSION[$this->_sSessionPrefix][$this->_sIdentifier]['ticket']) 
                ||  (
                            null !== $aRequest 
                        &&  !isset($aRequest[$_SESSION[$this->_sSessionPrefix][$this->_sIdentifier]['ticket']])
                    )                    
            )
        {
            return array();
        }            

        if (null !== $aRequest)
        {
            return $aRequest;
        }
        
        return array();
	}
    
    /**
     * returns data handled by check
     * @access public
     * @return array
     */
	public function getFormDataCheckedArray()
	{
        return $this->_aFormDataChecked;
	}
	
    /**
     * returns array(config) of complete element identified by its attribute key/value (first occurance wins)
     * @example $aEmailConfig = getElementArrayByAttribute('name', 'email');
     * @access public
     * @param string $sAttributeKey e.g. 'name'
     * @param string $sAttributeValue e.g. 'email'
     * @return array
     */
	public function getElementArrayByAttribute($sAttributeKey = '', $sAttributeValue = '')
	{
		foreach ($this->_aConfig['element'] as $aValue)
		{
			if (isset($aValue['attribute'][$sAttributeKey]) && $aValue['attribute'][$sAttributeKey] === $sAttributeValue)
			{
				return $aValue;
			}
		}

		return array();
	}

    /**
     * returns array(config) of complete element identified by its label (first occurance wins)
     * @example $aEmailConfig = getElementArrayByLabel('E-Mail');
     * @access public
     * @param string $sLabel e.g. 'E-Mail'
     * @return array
     */
	public function getElementArrayByLabel($sLabel = '')
	{
		foreach ($this->_aConfig['element'] as $aValue)
		{
			if (isset($aValue['label']) && $aValue['label'] === $sLabel)
			{
				return $aValue;
			}
		}

		return array();
	}    

    /**
     * returns complete confif array
     * @access public
     * @return array
     */
	public function getConfigArray()
	{
		return $this->_aConfig;
	}

    /**
     * returns identifier
     * @access public
     * @return string
     */
	public function getIdentifier()
	{
		return $this->_sIdentifier;
	}

    /**
     * returns session prefix
     * @access public
     * @return string
     */
	public function getSessionPrefix()
	{
		return $this->_sSessionPrefix;
	}

    /**
     * returns elements config array only
     * @access public
     * @return array
     */
	public function getElementConfig()
	{
		$aFinal = array();
		
		foreach ($this->_aConfig['element'] as $aElement)
		{
			$aFinal[$aElement['name']] = $aElement;
		}
		
		return $aFinal;
	}

    /**
     * returns boolean status about form success
     * @access public
     * @return boolean
     */
	public function getStatus ()
	{
		return $this->_bStatus;
	}

    /**
     * returns error array
     * @access public
     * @return array
     */
	public function getErrorArray ()
	{
		return $this->_aError;
	}

    /**
     * returns message array
     * @access public
     * @return array
     */
	public function getMessageArray ()
	{
		return $this->_aMessage;
	}

    /**
     * returns missing array
     * @access public
     * @return array
     */
	public function getMissingArray ()
	{
		return $this->_aMissing;
	}
    
    /**
     * returns log entries
     * @access public
     * @return string
     */
    public function getLog()
    {
        return implode("\n", self::$_aLog);
    }

	//--------------------------------------------------------------------------
	// Setter
	
    /**
     * sets array(config) for an element, identified by its attribute-name
     * @access public
     * @param string $sElement
     * @param array $aValue
	 * @return void 
     */
    public function setElementArrayByName($sElement = '', array $aValue = array())
    {
        $iElementKey = $this->_aConfig['index'][$sElement]['iKey'];
        $this->_aConfig['element'][$iElementKey] = $aValue;
    }

    /**
     * sets session prefix
     * @access public
     * @param string $sSessionPrefix
     * @return \Phormix
     */
	public function setSessionPrefix($sSessionPrefix = '')
    {
        $this->_sSessionPrefix = $sSessionPrefix;

        return $this;
    }

    /**
     * sets validate class name
     * @access public
     * @param string $sValidate
	 * @return void 
     */
    public function setValidateClass($sValidate)
    {
        $this->_sValidate = $sValidate;
    }

    /**
     * sets sanitize class name
     * @access public
     * @param string $sSanitize
	 * @return void 
     */
    public function setSanitizeClass($sSanitize)
    {
        $this->_sSanitize = $sSanitize;
    }
    
    /**
     * sets config array
     * @access public
     * @param array $aConfig
     * @return \Phormix
     */
	public function setConfigArray(array $aConfig = array())
    {
        $this->_aConfig = $aConfig;

        return $this;
    }

    /**
     * sets config array from a config JSON file
     * @access public
     * @param string $sAbsPathToConfigFile
     * @return \Phormix
     */
    public function setConfigArrayFromJsonFile($sAbsPathToConfigFile = '')
    {
        if (!file_exists($sAbsPathToConfigFile))
        {
            return $this;
        }

        $this->_aConfig = json_decode(
            file_get_contents($sAbsPathToConfigFile),
            true // as Array
        );

        $this->_enrichConfig($this->_aConfig);

        return $this;
    }

    /**
     * sets identifier
     * @access public
     * @param string $sIdentifier
     * @return \Phormix
     */
    public function setIdentifier ($sIdentifier = '')
    {
        $this->_sIdentifier = ($sIdentifier == '') ? md5(serialize($this->_aConfig)) : $sIdentifier;

        return $this;
    }

    /**
     * sets ticket
     * @access public
     * @param string $sTicket
	 * @return void 
     */
    public function setTicket($sTicket = '')
    {
        $this->sTicket = ('' === $sTicket) ? md5(uniqid() . microtime()) : $sTicket;
    }

	//--------------------------------------------------------------------------
	// etc
	
    /**
     * sets config array, sets identifier
     * @access public
     * @param string $sAbsPathToConfigFile absolute Path to configFile (JSON)
     * @param string $sIdentifier Identifier | Default=md5($sAbsPathToConfigFile)
     * @return \Phormix
     */
	public function init($sAbsPathToConfigFile = '', $sIdentifier = '')
    {
        $this->setConfigArrayFromJsonFile($sAbsPathToConfigFile);
        $this->setIdentifier($sIdentifier);
        
        return $this;
    }

    /**
     * runs check, renews ticket, saves to session
     * @access public
     * @return \Phormix
     */
	public function run()
    {
        // check Data
        if (true === $this->checkFormSend ())
        {
            $this->_bStatus = $this->check();
            self::addLog("INFO\t" . 'Status: ' . (int) $this->_bStatus);
        }

        // generate new ticket
        $this->setTicket();

        // save to session
        $this->_setSessionInfos();
        
        // overwrite global
        $GLOBALS['_' . strtoupper($this->_aConfig['method'])] = $this->getFormDataArray();
            
        return $this;
    }

    /**
     * starts a session if none done yet
     * @access public
	 * @return void 
     */
    public function startSession()
    {
        if ('' === session_id())
        {
            session_start();
        }
    }

    /**
     * checks form was sent
     * @access public
     * @return boolean
     */
    public function checkFormSend ()
    {
		$aFormData = $this->getFormDataArray();
		
        if  (
                    isset($aFormData) 
                &&  !empty($aFormData) 
                &&  array_key_exists($this->_sIdentifier, $_SESSION[$this->_sSessionPrefix]))        
        {
            return true;
        }

        return false;
    }

    /**
     * performs the formular data check
     * @access public
     * @return boolean
     */
	public function check()
	{	
		$this->_aFormDataChecked = array();
			
		// check page/ticket
		if (false === $this->_checkTicket($_SESSION[$this->_sSessionPrefix][$this->_sIdentifier]['ticket']))
		{
			return false;
		}
        
		// walk config elements
		foreach ($this->_aConfig['element'] as $iKey => $aElement)
		{
            // if element has type=file, we need to look at $_FILES            
            if (isset($aElement['attribute']['type']) && strtolower(trim($aElement['attribute']['type'])) === 'file')
            {
                $aFormData = $_FILES;
            }
            else
            {
                $aFormData = $this->getFormDataArray();
                $this->_aFormData = $aFormData;
            }

			/**
			 * check for 
			 * essential attributes:
			 * 
			 * - name
			 * - required
			 */
			if (!isset($aElement['attribute']['name']))
			{
				$this->_aError[8888] = 'missing attribute: $aElement[' . $iKey . '][attribute][name]';
				self::addLog("FAIL\t" . 'missing attribute: $aElement[' . $iKey . '][attribute][name]');				

				return false;
			}

			if (!isset($aElement['attribute']['required']))
			{
				$this->_aError[9999] = 'missing attribute: $aElement[' . $iKey . '][attribute][required]';
				self::addLog("FAIL\t" . 'missing attribute: $aElement[' . $iKey . '][attribute][required]');				

				return false;
			}
			
			// get element name
			$sElementName = $aElement['attribute']['name'];
			
			// per config expected Element is required=true
			// and is not in sent data
			$bKeyExists = array_key_exists($sElementName, $aFormData);
            
			if	(true === $aElement['attribute']['required'] && false === $bKeyExists)
			{
				$this->_aMissing[$iKey] = $this->_aConfig['element'][$iKey];
				self::addLog("FAIL\t" . 'missing Element: ' . $iKey . '[' . $sElementName . ']');				
                
				return false;
			}

			// Validate if: 
			// required:true 
			// OR
			// required:false BUT input is given by that element
			if	(
						// things that must be given
						array_key_exists('required', $aElement['attribute']) 
					&&	array_key_exists('filter', $aElement) 
					&&	array_key_exists('validate', $aElement['filter']) 
						
					&&	(
								// required:true
								true === $aElement['attribute']['required']
						
								// required:false BUT input is given by that element
							||	(
										false === $aElement['attribute']['required']
									&&	true ===  (isset($this->_aConfig['index'][$sElementName]))	# Element exists in config
									&&	true === (isset($aFormData[$sElementName]))					# Element was sent by form
									&&	!empty($aFormData[$sElementName])							# Data sent not empty
								)
						)
				)
			{				                
				foreach ($aElement['filter']['validate'] as $sKey => $aValidate)
				{
					$sMyMthod = $this->_sMethodPrefix . strtoupper($sKey);
					(!array_key_exists('message', $aValidate)) ? $aValidate['message'] = array() : false;

                    if (array_key_exists('value', $aValidate))
                    {
                        // call validate target method
                        $oReflectionClass = new \ReflectionClass($this->_sValidate);
                        $oInstance =  $oReflectionClass->newInstanceWithoutConstructor();
                        $bElementIsValid = $oInstance->$sMyMthod($aFormData[$sElementName], $aValidate['value']);
						
                        $sElementLabelOrName = (array_key_exists('label', $aElement)) ? $aElement['label'] : $aElement['name'];
                        
                        if (false === $bElementIsValid)
                        {
                            // add error
                            $this->_aError[$aElement['attribute']['name']] =  (array_key_exists('fail', $aValidate['message'])) ? '"' . $sElementLabelOrName . '": ' . sprintf($aValidate['message']['fail'], $aValidate['value']) : '`' . $aElement['label'] . '` is invalid.';
                            self::addLog("FAIL\t" . 'Validate ' . $sMyMthod . '(' . (is_array($aFormData[$sElementName]) ? http_build_query($aFormData[$sElementName], '_', ', ') : $aFormData[$sElementName]) . ', ' . json_encode($aValidate['value']) . ')' . ' [$sElementName: ' . $sElementName . ']');
           
                            return false;
                        }
                        elseif (true === $bElementIsValid)
                        {
                            // add message
                            $this->_aMessage[$aElement['attribute']['name']]['validate'][$sMyMthod] =  (array_key_exists('success', $aValidate['message'])) ? '"' . $sElementLabelOrName . '": ' . sprintf($aValidate['message']['success'], $aValidate['value']) : '`' . $aElement['label'] . '` is valid.';
                            self::addLog("SUCCESS\t" . 'Validate ' . $sMyMthod . '(' . (is_array($aFormData[$sElementName]) ? http_build_query($aFormData[$sElementName], '_', ', ') : $aFormData[$sElementName]) . ', ' . json_encode($aValidate['value']) . ')' . ' [$sElementName: ' . $sElementName . ']');
                        }
                    }
                    else
                    {
                        self::addLog("FAIL\t" . 'missing "value" on Validator: ' . $sKey . ' [$sElementName: ' . $sElementName . ']');	
                    }
				}
			}
			
			// add first time as the validates has been passed
			$this->_aFormDataChecked[$sElementName] = $aFormData[$sElementName];
			
			// Sanitize: always
			if	(
						array_key_exists('filter', $aElement) 
					&&	array_key_exists('sanitize', $aElement['filter']) 
				)
			{
				foreach ($aElement['filter']['sanitize'] as $sKey => $aSanitize)
				{
					(!array_key_exists('message', $aSanitize)) ? $aSanitize['message'] = array() : false;
					
                    if (array_key_exists('value', $aSanitize))
                    {
                        $sMyMthod = $this->_sMethodPrefix . strtoupper($sKey);
                        
                        // call sanitize target method
                        $oReflectionClass = new \ReflectionClass($this->_sSanitize);
                        $oInstance =  $oReflectionClass->newInstanceWithoutConstructor();
                        $sSanitized = $oInstance->$sMyMthod($aFormData[$sElementName], $aSanitize['value']);                        
                        $sIdentifier = (array_key_exists('label', $aElement)) ? $aElement['label'] : $aElement['name'];
						
						$this->_aFormDataChecked[$sElementName] = $sSanitized;
						
                        if ($aFormData[$sElementName] === $sSanitized)
                        {
                            $this->_aMessage[$aElement['attribute']['name']]['sanitize'][$sMyMthod] =  (array_key_exists('success', $aSanitize['message'])) ? '"' . $sElementLabelOrName . '": ' . sprintf($aSanitize['message']['success'], $aSanitize['value']) : '`' . $aElement['label'] . '` is valid.';
                        }
                        else
                        {
                            $this->_aMessage[$aElement['attribute']['name']]['sanitize'][$sMyMthod] =  (array_key_exists('fail', $aSanitize['message'])) ? '"' . $sElementLabelOrName . '": ' . sprintf($aSanitize['message']['fail'], $aSanitize['value']) : '`' . $aElement['label'] . '` is invalid.';
                        }
                        
                        $aFormData[$sElementName] = $sSanitized;					                        
						self::addLog("SUCCESS\t" . 'Sanitize ' . $sMyMthod . '(' . $sElementName . ' =>  ' . $sKey . ': ' . json_encode($aSanitize) . '): `' . json_encode($sSanitized) . '`');
                    }                    
				}
			}						
		}
        
		return true;
	}

    /**
     * adds error to error array
     * @access public
     * @param string $sKey
     * @param mixed $mValue
     * @return boolean success
     */
    public function addError($sKey = '', $mValue = '')        
    {
        if ('' === $sKey || '' === $mValue)
        {
            return false;
        }
        
        $this->_aError[$sKey] = $mValue;       
        
        return true;
    }
	
    /**
     * adds message to message array
     * @access public
     * @param string $sKey
     * @param mixed $mValue
     * @return boolean success
     */
    public function addMessage($sKey = '', $mValue = '')        
    {
        if ('' === $sKey || '' === $mValue)
        {
            return false;
        }
        
        $this->_aMessage[$sKey] = $mValue;       
        
        return true;
    }
	
    /**
     * adds string to missing array
     * @access public
     * @param string $sKey
     * @param mixed $mValue
     * @return boolean success
     */
    public function addMissing($sKey = '', $mValue = '')        
    {
        if ('' === $sKey || '' === $mValue)
        {
            return false;
        }
        
        $this->_aMissing[$sKey] = $mValue;       
        
        return true;
    }
    
    /**
     * logs info into internal array
     * @access public
     * @param string $sString
     */
    public static function addLog ($sString = '')
    {
        self::$_aLog[] = $sString;
    }

    /**
     * adds an index to existing config array: key is the attribute:name of current element
     * @access protected
	 * @return void 
     */
    protected function _enrichConfig()
    {
        foreach ($this->_aConfig['element'] as $iKey => $aValue)
        {
            $aValue['iKey'] = $iKey;
            $this->_aConfig['index'][$aValue['attribute']['name']] = $aValue;
        }
    }
	
    /**
     * inits session namespace, saves identifier + ticket to session namespace
     * @access protected
	 * @return void 
     */
    protected function _setSessionInfos()
	{
		(!array_key_exists($this->_sSessionPrefix, $_SESSION)) ? $_SESSION[$this->_sSessionPrefix] = array() : false;		
		$_SESSION[$this->_sSessionPrefix][$this->_sIdentifier] = array();
		$_SESSION[$this->_sSessionPrefix][$this->_sIdentifier]['ticket'] = $this->sTicket;		
	}

    /**
     * checks existance of expected ticket
     * @access protected
     * @param string $sTicket
     * @return boolean success
     */
	protected function _checkTicket ($sTicket = '')
	{
		$aFormMethod = $this->getFormDataArray(); // e.g. $_POST
        
		// check if ticket exists as varname
		if (!array_key_exists($sTicket, $aFormMethod))
		{
			return false;
		}
		
		return true;
	}		
}
