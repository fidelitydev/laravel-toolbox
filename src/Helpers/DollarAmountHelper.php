<?php

namespace Knighttower\Toolbox\Helpers;

// convert/parse to standard dollar formats

class DollarAmountHelper
{
    /**
    * Helper Constructor
    */
    public function __construct()
    {
        //set to US currency
        setlocale(LC_MONETARY, 'en_US');
    }


    /**
    * Used to pre-validate the $amount and avoid WET code
     *
    * @internal
    * @param string $function
    * @param mixed $args
    * @return mixed
    */
    public function __call($function, $args)
    {
        $value = $args[0];
        if (empty($value) && $value !== '0') {
            return $value;
        }

        return $this->{$function}(...$args);
    }


    /**
    * Test if the value is in dollar format
     *
    * @param mixed $value
    * @return bool
    */
    private function isDollarAmount($value): bool
    {
        return !empty(preg_match('/(\$[0-9]+(.[0-9]+)?)+(\.|\,)?/', $value, $match));
    }


    /**
     * remove the $
     *
     * @param string $amount
     * @return string
     */
    private function removeSymbol(string $amount): string
    {
        return preg_replace('/[\$,]/', '', $amount);
    }


    /**
     * remove the ,
     *
     * @param string $amount
     * @return string
     */
    private function removeFormat(string $amount): string
    {
        return str_replace(',', '', $amount);
    }


    /**
     * from float to currency + round to 2
     *
     * @param string $amount
     * @return string
     */
    private function toCurrency(string $amount): string
    {
        $amount = $this->removeSymbol($amount);

        if (!is_numeric($amount)) {
            return $amount;
        }

        return number_format($amount, 2);
    }


    /**
     * from currency to decimal
     *
     * @param string $amount
     * @return string
     */
    private function toDecimal(string $amount): string
    {
        $amount = $this->removeSymbol($amount);

        //remove the ',' when using currency format
        $amount = $this->removeFormat($amount);

        //remove the extra decimals from "DB Double"
        $amount = number_format($amount, 2, '.', '');

        return floatval($amount);
    }


    /**
     * Convert into condensed string
     *
     * @param int|string $amount
     * @return void|formatted string
     */
    private function toString($amount): string
    {
        //remove any $
        $amount = $this->toDecimal($amount);

        if ($amount < 1000) {
            return number_format($amount, 2);
        }
        //else:
        //formats to 0000 K|M
        if ($amount < 1000000) {
            return number_format($amount / 1000) . 'K';
        }
        if ($amount < 1000000000) {
            return number_format($amount / 1000000, 2) . 'M';
        }

        return $amount;
    }
}
