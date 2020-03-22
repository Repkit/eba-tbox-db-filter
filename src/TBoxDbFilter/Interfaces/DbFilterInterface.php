<?php

namespace TBoxDbFilter\Interfaces;
/**
 *
 * @author Alex
 */
interface DbFilterInterface
{
	/**
     * Retrieve filtered collections
     *
     * @return array|bool
     *   Filtered collection array or false in case filter data is not valid
     */
    public function Apply();

}
