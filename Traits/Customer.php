<?php

namespace BitTools\SkyHub\Traits;

use Magento\Customer\Model\Address;
use Magento\Framework\DataObject;

trait Customer
{

    /**
     * @param string $fullname
     *
     * @return DataObject
     */
    protected function breakName($fullname)
    {
        $fullnametmp = (array) explode(' ', $fullname);

        $firstname = ucwords(array_shift($fullnametmp));
        $lastname = ucwords(array_pop($fullnametmp));
        if (!$lastname) {
            $lastname = $firstname;
        }
        $middlename = ucwords(implode(' ', $fullnametmp));

        return new DataObject([
            'firstname'  => $firstname,
            'middlename' => $middlename,
            'lastname'   => $lastname,
            'fullname'   => $fullname,
        ]);
    }


    /**
     * @param string $phone
     * @return string
     */
    protected function formatPhone($phone)
    {
        if (!$phone) {
            return '(00) 0000-0000';
        }
        return $phone;
    }


    /**
     * @param Address $address
     * @param         $addressSize
     *
     * @return string
     */
    protected function formatAddress($address, $addressSize)
    {
        $street = $address->getData('street');
        $number = $address->getData('number');
        $complement = implode(' ', [$address->getData('reference'), $address->getData('detail')]);
        $neighborhood = $address->getData('neighborhood');

        return $this->_formatAddress(
            [
                $street,
                $number,
                $complement,
                $neighborhood,
            ],
            $addressSize
        );
    }


    /**
     * @param array $address
     * @param       $addressSize
     * @return string
     */
    private function _formatAddress(array $address, $addressSize)
    {
        if ($addressSize == 1) {
            return implode(' - ', $address);
        }

        return (array_shift($address) . "\n" . $this->_formatAddress($address, $addressSize - 1));
    }
    
    
    /**
     * @param string $vatNumber
     *
     * @return bool
     */
    protected function customerIsPj($vatNumber)
    {
        $vatNumber = preg_replace('/[^0-9]/', '', (string) $vatNumber);
        
        if (strlen($vatNumber) <= 11) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @param string      $street
     * @param string|null $number
     * @param string|null $neighborhood
     * @param string|null $complement
     *
     * @return array
     */
    protected function prepareAddressStreetLines(
        $street,
        $number = null,
        $neighborhood = null,
        $complement = null,
        $linesCount = 2
    ) {
        $linesCount = min(4, max(2, $linesCount));
        
        switch ($linesCount) {
            case 2:
                $streetLines = [
                    $this->joinStrings(', ', $street, $number),
                    $this->joinStrings(' - ', $neighborhood, $complement)
                ];
                break;
            case 3:
                $streetLines = [
                    $street,
                    $number,
                    $this->joinStrings(' - ', $neighborhood, $complement)
                ];
                break;
            default:
                $streetLines = [
                    $street,
                    $number,
                    $neighborhood,
                    $complement
                ];
                break;
        }
        
        return $streetLines;
    }
}
