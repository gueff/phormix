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
	 * @var string 
     * @access protected
	 */
	protected $_sSessionPrefix = 'Phormix';
	
	/**
	 * config
	 * @var array
     * @access protected
	 */
	protected $_aConfig = array();
	
	/**
	 * error
	 * @var array
     * @access protected
	 */
	protected $_aError = array();
	
	/**
	 * message
	 * @var array
     * @access protected
	 */
	protected $_aMessage = array();
	
	/**
	 * missing
	 * @var array
     * @access protected
	 */
	protected $_aMissing = array();

	/**
	 * status as result of check() method
	 * @var boolean
     * @access protected
	 */
	protected $_bStatus = false;
	
	/**
	 * ticket
	 * @var string
     * @access public
	 */
	public $sTicket = '';
	
	/**
	 * unique identifier
	 * @var string
     * @access protected
	 */
	protected $_sIdentifier = '';

    /**
     * runtime log
     * @var array
     * @access protected
     */
    protected static $_aLog = array();

    /**
     * name of validate class
     * @var string
     */
    private $_sValidate = '\PhormixValidate';
    
    /**
     * name of sanitize class
     * @var string
     */
    private $_sSanitize = '\PhormixSanitize';
    
    /**
     * prefix of methods in validate & sanitize classes
     * @var string
     * @access protected
     */
    protected $_sMethodPrefix = '_';
    
    /**
     * Phormix constructor.
     * @access public
     */
	public function __construct()
	{
        $this->startSession();
	}

    /**
     * sets config array, sets identifier
     * @access public
     * @param string $sAbsPathToConfigFile absolute Path to configFile (JSON)
     * @param string $sIdentifier Identifier | Default=md5($sAbsPathToConfigFile)
     * @return $this
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
     */
	public function run()
    {
        // check Data
        if (true === $this->checkFormSend ())
        {
            $this->_bStatus = $this->check();
            self::LOG("INFO\t" . 'Status: ' . (int) $this->_bStatus);
        }

        // generate new ticket
        $this->setTicket();

        // save to session
        $this->_setSessionInfos();
    }

    /**
     * sets validate class name
     * @access public
     * @param string $sValidate
     */
    public function setValidateClass($sValidate)
    {
        $this->_sValidate = $sValidate;
    }

    /**
     * sets sanitize class name
     * @access public
     * @param string $sSanitize
     */
    public function setSanitizeClass($sSanitize)
    {
        $this->_sSanitize = $sSanitize;
    }
    
    /**
     * sets config array
     * @access public
     * @param array $aConfig
     * @return $this
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
     * @return $this
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
     * @return $this
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
     */
    public function setTicket($sTicket = '')
    {
        $this->sTicket = ('' === $sTicket) ? md5(uniqid() . microtime()) : $sTicket;
    }

    /**
     * starts a session if none done yet
     * @access public
     */
    public function startSession()
    {
        if ('' === session_id())
        {
            session_start();
        }
    }

    /**
     * adds an index to existing config array: key is the attribute:name of current element
     * @access private
     */
    private function _enrichConfig()
    {
        foreach ($this->_aConfig['element'] as $iKey => $aValue)
        {
            $aValue['iKey'] = $iKey;
            $this->_aConfig['index'][$aValue['attribute']['name']] = $aValue;
        }
    }

    /**
     * adds error to error array
     * @access public
     * @param string $sKey
     * @param mixed $mValue
     * @return boolean
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
     * @return boolean
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
     * @return boolean
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
     * inits session namespace, saves identifier + ticket to session namespace
     * @access private
     */
    private function _setSessionInfos()
	{
		(!array_key_exists($this->_sSessionPrefix, $_SESSION)) ? $_SESSION[$this->_sSessionPrefix] = array() : false;		
		$_SESSION[$this->_sSessionPrefix][$this->_sIdentifier] = array();
		$_SESSION[$this->_sSessionPrefix][$this->_sIdentifier]['ticket'] = $this->sTicket;		
	}

    /**
     * returns data sent by form
     * @access public
     * @return array|mixed
     */
	public function getFormDataArray()
	{
        $aRequest = $GLOBALS['_' . strtoupper($this->_aConfig['method'])];

        if (null !== $aRequest)
        {
            return $aRequest;
        }

        return array();
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
     * sets array(config) for an element, identified by its attribute-name
     * @access public
     * @param string $sElement
     * @param array $aValue
     */
    public function setElementArrayByName($sElement = '', array $aValue = array())
    {
        $iElementKey = $this->_aConfig['index'][$sElement]['iKey'];
        $this->_aConfig['element'][$iElementKey] = $aValue;
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
     * sets session prefix
     * @access public
     * @param string $sSessionPrefix
     * @return $this
     */
	public function setSessionPrefix($sSessionPrefix = '')
    {
        $this->_sSessionPrefix = $sSessionPrefix;

        return $this;
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
		// check page/ticket
		if (false === $this->_checkTicket($_SESSION[$this->_sSessionPrefix][$this->_sIdentifier]['ticket']))
		{
			return false;
		}
        
		// walk config elements
		foreach ($this->_aConfig['element'] as $iKey => $aElement)
		{
            // if element has type=file, we need to look at $_FILES
			$aFormData = (isset($aElement['attribute']['type']) && strtolower(trim($aElement['attribute']['type'])) === 'file') ? $_FILES : $this->getFormDataArray();
            
			// get element name
			$sElementName = $aElement['attribute']['name'];
			
			// per config expected Element is required=true
			// and is not in sent data
            $bKeyExists = array_key_exists($sElementName, $aFormData);
            
			if	(true === $aElement['attribute']['required'] && false === $bKeyExists)
			{
                $this->_aMissing[$iKey] = $this->_aConfig['element'][$iKey];
				self::LOG("FAIL\t" . 'missing Element: ' . $sElementName);				
                
				return false;
			}
			
			// Validate
			if	(
						array_key_exists('required', $aElement['attribute']) 
					&&	array_key_exists('filter', $aElement) 
					&&	array_key_exists('validate', $aElement['filter']) 
					&&	true === $aElement['attribute']['required']
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
                        
                        $sIdentifier = (array_key_exists('label', $aElement)) ? $aElement['label'] : $aElement['name'];
                        
                        if (true === $aElement['attribute']['required'] && false === $bElementIsValid)
                        {
                            // add error
                            $this->_aError[$aElement['attribute']['name']] =  (array_key_exists('fail', $aValidate['message'])) ? '"' . $sIdentifier . '": ' . sprintf($aValidate['message']['fail'], $aValidate['value']) : '`' . $aElement['label'] . '` is invalid.';
                            self::LOG("FAIL\t" . 'Validate ' . $sMyMthod . '(' . $aFormData[$sElementName] . ', ' . json_encode($aValidate['value']) . ')' . ' [$sElementName: ' . $sElementName . ']');
                            
                            return false;
                        }
                        elseif (true === $aElement['attribute']['required'] && true === $bElementIsValid)
                        {
                            // add message
                            $this->_aMessage[$aElement['attribute']['name']]['validate'][$sMyMthod] =  (array_key_exists('success', $aValidate['message'])) ? '"' . $sIdentifier . '": ' . sprintf($aValidate['message']['success'], $aValidate['value']) : '`' . $aElement['label'] . '` is valid.';
                            self::LOG("SUCCESS\t" . 'Validate ' . $sMyMthod . '(' . $aFormData[$sElementName] . ', ' . json_encode($aValidate['value']) . ')' . ' [$sElementName: ' . $sElementName . ']');
                        }
                    }
                    else
                    {
                        self::LOG("FAIL\t" . 'missing "value" on Validator: ' . $sKey . ' [$sElementName: ' . $sElementName . ']');	
                    }
				}
			}
			
			// Sanitize
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
                        
                        $aFormData[$sElementName] = $sSanitized;					
                       
                        $this->_aMessage[$aElement['attribute']['name']]['sanitize'][$sMyMthod] =  (array_key_exists('success', $aSanitize['message'])) ? '"' . $sIdentifier . '": ' . $aSanitize['message']['success'] : '`' . $aElement['label'] . '` is valid.';
                    }                    
				}
			}						
		}

        self::LOG("INFO\t" . 'Field "' . $sElementName . '" succeeded (' . $sElementName . '=' . $aFormData[$sElementName] . ')' . ' [$sElementName: ' . $sElementName . ']');	
        
		return true;
	}

    /**
     * checks existance of expected ticket
     * @access private
     * @param string $sTicket
     * @return boolean
     */
	private function _checkTicket ($sTicket = '')
	{
		$aFormMethod = $this->getFormDataArray(); // e.g. $_POST
        
		// check if ticket exists as varname
		if (!array_key_exists($sTicket, $aFormMethod))
		{
			return false;
		}
		
		return true;
	}
    
    /**
     * logs info into internal array
     * @access public
     * @param string $sString
     */
    public static function LOG ($sString = '')
    {
        self::$_aLog[] = $sString;
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
		
}
