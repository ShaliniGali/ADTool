<?php
/**
 * General Helper class
 *
 *
 */
defined('BASEPATH') || exit('No direct script access allowed');

if ( ! function_exists('performIssetTenaryOp'))
{
 // --------------------------------------------------------------------
    /**
     * Tenary op
     *
     * @param  bool  $condition
     * @param  array  $true_expression_obj
     * @param  string  $true_expression_key
     * @param  mixed     $false_expression
     * @return  mixed
     */
    function performIssetTenaryOp($condition, $true_expression_obj, $true_expression_key, $false_expression)
    {
        return $condition ? $true_expression_obj[$true_expression_key] : $false_expression;
    }
}

if ( ! function_exists('performGeneralTenaryOp'))
{
 // --------------------------------------------------------------------
    /**
     * Tenary op
     *
     * @param  string  $date
     * @param  string  $format
     * @return  boolean
     */
    function performGeneralTenaryOp($condition, $true_expression, $false_expression)
    {
        return $condition ? $true_expression : $false_expression;
    }
}

if ( ! function_exists('performIssetMultiKeyTenaryOp'))
{
 // --------------------------------------------------------------------
    /**
     * Tenary op
     *
     * @param  bool  $condition
     * @param  array  $true_expression_obj
     * @param  string  $true_expression_key
     * @param  mixed     $false_expression
     * @return  mixed
     */
    function performIssetMultiKeyTenaryOp($keys, $array, $false_expression)
    {
        $current = $array;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return $false_expression;
            }
            $current = $current[$key];
        }

        return $current;
    }
}
