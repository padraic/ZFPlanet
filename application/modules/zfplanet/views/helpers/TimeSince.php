<?php

class Zfplanet_View_Helper_TimeSince extends Zend_View_Helper_Abstract
{

    protected $_now = null;

    public function __construct()
    {
        $this->_now = new Zend_Date;
    }

    /**
     * Calculate number of days/hours/minutes since the provided date (must be ISO 8601 date).
     * Because this adds partial days, we just round the result up or down to
     * get the closest number of "full" days/hours/minutes. Note that we only use
     * ONE measure. If it's less than a day, we return hours. If less than an hour...
     *
     * @param string $date ISO 8601 compatible date
     * @return integer
     */
    public function timeSince($date)
    {
        $then = new Zend_Date($date, Zend_Date::ISO_8601);
        $diff = $this->_now->sub($then);
        $days = round($diff->getTimestamp() / (60 * 60 * 24));
        if ($days >= 1) {
            return $days . ' days';
        }
        $hours = round($diff->getTimestamp() / (60 * 60));
        if ($hours >= 1) {
            return $hours . ' hours';
        }
        return round($diff->getTimestamp() / 60) . ' minutes';
    }

}
