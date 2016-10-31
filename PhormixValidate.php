<?php

/**
 * PhormixValidate.php
 *
 * @package myMVC
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See application/doc/COPYING
 */

/**
 * @name $PhormixModel
 */
//namespace Phormix\Model;


/**
 * PhormixValidate
 */
class PhormixValidate
{
    /**
	 * validates on minimum length 
	 * @param string $sFieldValue
	 * @param integer $iValue
	 * @return boolean success
	 */
	public static function _MINLENGTH($sFieldValue, $iValue)
	{
		if (mb_strlen($sFieldValue) < $iValue)
		{
			return false;
		}

		return true;
	}
    
    /**
     * validates on maxlength
     * @param string $sFieldValue
     * @param integer $iValue
     * @return boolean
     */
	public static function _MAXLENGTH($sFieldValue, $iValue)
	{
		if (strlen($sFieldValue) > $iValue)
		{
			return false;
		}

		return true;
	}

    /**
     * validates on expected values
     * @param string $sFieldValue
     * @param array $aExpect
     * @return boolean
     */
	public static function _EXPECT($sFieldValue, $aExpect)
	{
		if (!in_array($sFieldValue, $aExpect))
		{
			return false;
		}

		return true;
	}

    /**
     * validates by regex pattern
     * @param string $sFieldValue
     * @param string $sPattern
     * @return boolean
     */
	public static function _REGEX($sFieldValue, $sPattern)
	{
        $bStatus = (boolean) preg_match($sPattern, $sFieldValue);
        
        return $bStatus;
	}

    /**
     * validates on empty value
     * @param string $sFieldValue
     * @param boolean $bEmpty
     * @return boolean
     */
	public static function _EMPTY($sFieldValue, $bEmpty)
	{
		$bCheck = ('' === $sFieldValue);
		
		if ((boolean) $bEmpty !== $bCheck)
		{
			return false;
		}

		return true;
	}

    /**
     * validates file access
     * @param array $aFiles
     * @param boolean $bValidate
     * @return boolean
     */
	public static function _FILE($aFiles, $bValidate)
	{
		if (!is_array($aFiles))
		{
			return false;
		}

        // check syntax of $aFile - how it compares to common $_FILES array syntax
        if (0 !== count(array_diff(array('name', 'type', 'tmp_name', 'error', 'size'), array_keys($aFiles))))
        {
			return false;
        }  
        
        // check error
        if ($aFiles['error'] !== 0)
        {
            return false;
        }
        
		return true;
	}

    /**
     * validates filetype
     * @param array $aFiles
     * @param array $aValid
     * @return boolean
     */
	public static function _FILETYPE($aFiles, $aValid)
	{
        if (false === self::_file($aFiles, $bValidate))
        {
            return false;
        }
        
        (!is_array($aValid)) ? $aValid = array($aValid) : false;        
        $sIsFileType = trim(shell_exec('file -bi -- ' . escapeshellarg($aFiles['tmp_name'])));
        
        foreach ($aValid as $sValidFileType)
        {
            if (substr($sIsFileType, 0, strlen($sValidFileType)) == $sValidFileType)
            {
                return true;
            }
        }
                
    	return false;
	}

    /**
     * validates max filesize of file
     * @param array $aFiles
     * @param boolean $bValidate
     * @return boolean
     */
	public static function _FILEMAXFILESIZE($aFiles, $bValidate)
	{
        if (false === self::_file($aFiles, $bValidate))
        {
            return false;
        }
        
        // check size
        if  ($aFiles['size'] > $bValidate)
        {
            return false;
        }
                
		return true;
	}
    
    /**
     * validates email
     * @param string $sFieldValue
     * @param string $aData
     * @return boolean
     */
	public static function _EMAIL($sFieldValue, $aData)
	{
        $bValid = filter_var($sFieldValue, FILTER_VALIDATE_EMAIL);
        
        return $bValid;
	}
}
