<?php

namespace MZoo\MzMboAccess\Access;

use MZoo\MzMboAccess as NS;
use MZoo\MzMindbody as MZ;
use MZoo\MzMboAccess\Core as Core;
use MZoo\MzMboAccess\Client as Client;
use MZoo\MzMindbody\Common as Common;
use MZoo\MzMindbody\Common\Interfaces as Interfaces;

class AccessUtilities extends Client\RetrieveClient
{



    /**
     * Access Level
     *
     * Since 1.0.5
     *
     * @ int indicating client access level, 0, 1 or 2.
     */
    public $access_level = 0;

    /**
     * Check Access Permissions
     *
     * Since 1.0.0
     *
     * @return int indicating client access level, 0, 1 or 2.
     */
    public function check_access_permissions($client_id)
    {
        $result = $this->set_client_access_level($client_id);
        return $result;
    }

    /**
     * Compare Client Service Status
     *
     * Since 2.5.8
     *
     * return true if active membership matches one in received array (or string)
     *
     * @param $membership_types string or array of membership types
     *
     * @return bool
     */
    public function set_client_access_level($client_id)
    {

        // TODO can we avoid doing this here AND in access display?
        $mz_mbo_access_options = get_option('mz_mbo_access');

        $level_1_contracts = explode(',', $mz_mbo_access_options['level_1_contracts']);
        $level_2_contracts = explode(',', $mz_mbo_access_options['level_2_contracts']);
        $level_1_contracts = array_map('trim', $level_1_contracts);
        $level_2_contracts = array_map('trim', $level_2_contracts);

        if (count($level_1_contracts) >= 1 || count($level_2_contracts) >= 1) {
            $contracts = $this->get_client_contracts($client_id);

            if (empty($contracts)) {
                $this->access_level = 0;
                // Update client session with empty keys just in case
                return $this->update_client_session(
                    array(
                        'access_level' => 0,
                        'contracts'    => array(),
                    )
                );
            }

            // Comapre level two services first
            foreach ($contracts as $contract) {
                if (in_array($contract['ContractName'], $level_2_contracts)) {
                    // No need to check further
                    $this->access_level = 2;
                    return $this->update_client_session(
                        array(
                            'access_level' => 2,
                            'contracts'    => $contracts,
                        )
                    );
                }
                // If not level two do we have level one access?
                if (in_array($contract['ContractName'], $level_1_contracts)) {
                    // No need to check further
                    $this->access_level = 1;
                    return $this->update_client_session(
                        array(
                            'access_level' => 1,
                            'contracts'    => $contracts,
                        )
                    );
                }
            }
        }

        // No contracts so must be dealing with services
        $level_1_services = explode(',', $mz_mbo_access_options['level_1_services']);
        $level_2_services = explode(',', $mz_mbo_access_options['level_2_services']);
        $level_1_services = array_map('trim', $level_1_services);
        $level_2_services = array_map('trim', $level_2_services);

        $services = $this->get_client_services($client_id);

        if (false == (bool) $services['ClientServices']) {
            $this->access_level = 0;
            // Update client session with empty keys just in case
            return $this->update_client_session(
                array(
                    'access_level' => 0,
                    'services'     => array(),
                )
            );
        }

        // Comapre level two services first
        foreach ($services['ClientServices'] as $service) {
            if (in_array($service['Name'], $level_2_services)) {
                if (! $this->is_service_valid($service)) {
                    continue;
                }
                $this->access_level = 2;
                // No need to check further
                return $this->update_client_session(
                    array(
                        'access_level' => 2,
                        'services'     => $services['ClientServices'],
                    )
                );
            }
            // If not level two do we have level one access?
            if (in_array($service['Name'], $level_1_services)) {
                if (! $this->is_service_valid($service)) {
                    continue;
                }
                $this->access_level = 1;
                // No need to check further
                return $this->update_client_session(
                    array(
                        'access_level' => 1,
                        'services'     => $services['ClientServices'],
                    )
                );
            }
        }
        foreach ($services['ClientServices'] as $service) {
        }

        return $this->access_level;
    }

    /**
     * Is Service Valid
     *
     * @since  1.0.0
     * @param  array service as returned from mbo
     * @return bool true if there are remaining and date not expired
     */
    private function is_service_valid($service)
    {

        if ($service['Remaining'] < 1) {
            return false;
        }

        $service_expiration = new \DateTime($service['ExpirationDate'], MZ\MZMBO()::$timezone);
        $now                = new \DateTimeImmutable('now', MZ\MZMBO()::$timezone);
        if ($service_expiration->date < $now->date) {
            return false;
        }

        return true;
    }

    /**
     * Compare Client Contract Status
     *
     * Since 1.0.0
     *
     * return true if active membership matches one in received array (or string)
     *
     * @param $membership_types string or array of membership types
     *
     * @return bool
     */
    public function compare_client_contract_status($contract_types = array())
    {

        $contract_types = is_array($contract_types) ? $contract_types : array( $contract_types );

        $contracts = $this->get_client_contracts();

        if (false == (bool) $contracts[0]['ContractName']) {
            return 0;
        }

        foreach ($contracts as $contract) {
            if (in_array($contract['ContractName'], $contract_types)) {
                return 2;
            }
        }

        return 0;
    }


    /**
     * Compare Client Purchase Status
     *
     * Since 2.5.8
     *
     * return true if TODO active membership matches one in received array (or string)
     *
     * @param TODO $membership_types string or array of membership types
     *
     * @return bool
     */
    public function compare_client_purchase_status($purchase_types = array())
    {

        $purchase_types = is_array($purchase_types) ? $purchase_types : array( $purchase_types );

        $purchases = $this->get_client_purchases();

        if (false == (bool) $purchases[0]['Sale']) {
            return 0;
        }

        foreach ($purchases as $purchase) {
            if (in_array($purchase['Description'], $purchase_types)) {
                return 1;
            }
        }

        return 0;
    }




    /**
     * Get Client Access Level
     *
     * Since 2.5.8
     *
     * @return bool
     */
    public function get_client_access_level()
    {

        return $this->access_level;
    }
}
