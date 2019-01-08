<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 06-Dec-18
 * Time: 9:15 PM
 */

/**
 * @property Model_vehicle $Model_vehicle
 */
class Vehicle extends Basic_Controller
{
    /**
     * Auth constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Model_vehicle');
    }

    private function _check_input($user,$id,$type,$number,$price,$feature)
    {
        $data = array(
            'vehicle_type' => $type,
            'vehicle_number' => $number,
            'vehicle_price' => $price,
            'vehicle_lastmodified' => $this->date_time,
            'vehicle_lastmodified_id' => $user
        );

        if ($id === null) {
            $data['vehicle_created'] = $this->date_time;
            $data['vehicle_created_id'] = $user;
            $data['vehicle_is_active'] = TRUE;
            $id = $this->Model_vehicle->insert($data);
        }
        else {
            $this->Model_vehicle->update($data,$id);
        }

        $features = array();
        if ($feature !== null) {
            foreach ($feature as $key => $value) {
                $key = $this->validate_input($key, FALSE, FALSE, TRUE);
                $value = $this->validate_input($value, FALSE, FALSE, TRUE);

                if ($key === null || $value === null) continue;
                else {
                    $temp = array(
                        'vehicle_feature_id' => $id,
                        'vehicle_feature_key' => $key,
                        'vehicle_feature_value' => $value,
                    );
                    $features[] = $temp;
                }
            }
        }
        $this->Model_vehicle->update_feature($features,$id);
        return $id;
    }

    public function insert_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $type = $this->validate_input(@$data['type']);
        $number = $this->validate_input(@$data['number']);
        $price = $this->validate_input(@$data['price'],TRUE);
        $feature = $this->validate_input(@$data['feature'],FALSE,TRUE,TRUE);
        $prices = $this->validate_input(@$data['prices'],FALSE,TRUE,TRUE);

        $id = $this->_check_input($user,NULL,$type,$number,$price,$feature);

        if ($prices !== null) {
            $data = $this->_price_post($user, $id, $prices);
            $this->Model_vehicle->update_price($data);
        }

        $this->output_ok($id);
    }

    public function update_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $type = $this->validate_input(@$data['type']);
        $number = $this->validate_input(@$data['number']);
        $price = $this->validate_input(@$data['price'],TRUE);
        $feature = $this->validate_input(@$data['feature'],FALSE,TRUE,TRUE);
        $prices = $this->validate_input(@$data['prices'],FALSE,TRUE,TRUE);

        $id = $this->_check_input($user,$id,$type,$number,$price,$feature);

        if ($prices !== null) {
            $data = $this->_price_post($user, $id, $prices);
            $this->Model_vehicle->update_price($data);
        }

        $this->output_ok($id);
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);
        $is_free = $this->validate_input(@$data['is_free'],FALSE,FALSE,TRUE);
        $date_start = $this->validate_input(@$data['date_start'],FALSE,FALSE,TRUE);
        $date_end = $this->validate_input(@$data['date_end'],FALSE,FALSE,TRUE);
        $status = $this->validate_input(@$data['status'],FALSE,FALSE,TRUE);

        if ( ! is_bool($is_free)) $is_free = NULL;
        if ( ! is_bool($status)) $status = NULL;
        if ($date_start === null) $date_start = $this->date_time;
        if ($date_end === null) $date_end = $this->date_time;

        $data = $this->Model_vehicle->select($id,$is_free,$date_start,$date_end,$status);
        if ($data === null) {
            $this->output_empty();
        }
        else {
            for($i=0, $iMax = count($data); $i< $iMax; $i++) {
                $data[$i]->feature = $this->Model_vehicle->select_feature($data[$i]->id);
                $data[$i]->prices = $this->Model_vehicle->select_price($data[$i]->id);
            }
            $this->output_ok($data);
        }
    }

    public function delete_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);

        $data = array(
            'vehicle_is_active' => FALSE
        );
        $this->Model_vehicle->update($data,$id);
        $this->output_ok(NULL);
    }

    public function price_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $prices = $this->validate_input(@$data['prices'],FALSE,TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);

        $data = $this->_price_post($user,$id,$prices);

        $this->Model_vehicle->update_price($data);

        $this->output_ok(NULL);
    }

    private function _price_post($user,$vehicle,$prices)
    {
        $data = array();
        foreach ($prices as $item)
        {
            $price = $this->validate_input(@$item['price'],TRUE);
            $start = $this->validate_input(@$item['start']);
            $usertype = $this->validate_input(@$item['usertype'],TRUE);

            $temp = array(
                'price_price' => $price,
                'price_start' => $start,
                'vehicle_id' => $vehicle,
                'user_type_id' => $usertype,
                'price_created' => $this->date_time,
                'price_created_id' => $user
            );
            $data[] = $temp;
        }
        return $data;
    }

    public function activate_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);

        $data = array(
            'vehicle_lastmodified' => $this->date_time,
            'vehicle_lastmodified_id' => $user,
            'vehicle_status' => TRUE
        );
        $this->Model_vehicle->update($data,$id);

        $this->output_ok(NULL);
    }

    public function deactivate_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);

        $data = array(
            'vehicle_lastmodified' => $this->date_time,
            'vehicle_lastmodified_id' => $user,
            'vehicle_status' => FALSE
        );
        $this->Model_vehicle->update($data,$id);

        $this->output_ok(NULL);
    }

    public function find_price_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user_type = $this->validate_input(@$data['user_type'],TRUE);

        $prices = $this->Model_vehicle->find_price($id,$this->date,$user_type);
        if ($prices) {
            $this->output_ok($prices);
        }
        else {
            $this->output_empty();
        }
    }
}