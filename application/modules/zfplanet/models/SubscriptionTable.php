<?php

class Zfplanet_Model_SubscriptionTable extends Doctrine_Table
    implements Zend_Feed_Pubsubhubbub_Model_SubscriptionInterface
{

    /**
     * Save subscription to RDMBS
     *
     * @param array $data
     * @return bool
     */
    public function setSubscription(array $data)
    {
        if (!isset($data['id'])) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception(
                'ID must be set before attempting a save'
            );
        }
        $result = $this->find($data['id']);
        if ($result) {
            $data['created_time'] = $result->created_time;
            $now = new Zend_Date;
            $data['last_modified'] = $now->get('yyyy-MM-dd HH:mm:ss');
            $data['expiration_time'] = $now->add($result->lease_seconds, Zend_Date::SECOND) // should be hub though...
                ->get('yyyy-MM-dd HH:mm:ss');
            $result->merge($data);
            $result->save();
            return false;
        }
        $subscription = new Zfplanet_Model_Subscription;
        $subscription->merge($data);
        $subscription->save();
        return true;
    }
    
    /**
     * Get subscription by ID/key
     * 
     * @param  string $key 
     * @return array
     */
    public function getSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                .' of "' . $key . '" must be a non-empty string');
        }
        $result = $this->find($key);
        if ($result) {
            return $result->toArray();
        }
        return false;
    }

    /**
     * Determine if a subscription matching the key exists
     * 
     * @param  string $key 
     * @return bool
     */
    public function hasSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                .' of "' . $key . '" must be a non-empty string');
        }
        $result = $this->find($key);
        if ($result) {
            return true;
        }
        return false;
    }
    
    /**
     * Delete a subscription
     *
     * @param string $key
     * @return bool
     */
    public function deleteSubscription($key)
    {
        if ($this->hasSubscription($key)) {
            Doctrine_Query::create()
                ->delete('Zfplanet_Model_Subscription')
                ->where('id = ?', $key);
            return true;
        }
        return false;
    }

}
