<?php

/**
 * Sanitize.php
 *
 * @package Phormix
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See application/doc/COPYING
 */

/**
 * @name $PhormixModel
 */
namespace Phormix;


/**
 * Sanitize
 */
class Sanitize
{			    
    /**
     * sanitizes against maxlength
     * @param string $sFieldValue
     * @param integer $iValue
     * @return string sanitized (shortened to maxlength)
     */
	public static function _MAXLENGTH($sFieldValue, $iValue)
	{
		$sFieldValue = mb_substr($sFieldValue, 0, $iValue);
		
		return $sFieldValue;
	}

    /**
     * sanitizes via regex pattern
     * @param string $sFieldValue
     * @param string $sPattern
     * @return string sanitized
     */
	public static function _REGEX($sFieldValue, $sPattern)
	{		
		$sFieldValue = preg_replace($sPattern, '', $sFieldValue);
        
		return $sFieldValue;
	}

    /**
     * sanitizes email by filer_var
     * @param string $sFieldValue
     * @param mixed $mData
     * @return string sanitized
     */
	public static function _EMAIL($sFieldValue, $mData)
	{
        $sFieldValue = filter_var($sFieldValue, FILTER_SANITIZE_EMAIL);
        
		return $sFieldValue;
	}	
}
