<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
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

    private function _check_input($user,$id,$type,$number,$feature,$price)
    {
        $data = array(
            'vehicle_type' => $type,
            'vehicle_number' => $number,
            'vehicle_price' => $price,
            'vehicle_lastmodified' => $this->date_time,
            'vehicle_lastmodified_id' => $user
        );

        if (is_null($id)) {
            $data['vehicle_created'] = $this->date_time;
            $data['vehicle_created_id'] = $user;
            $data['vehicle_is_active'] = TRUE;
            $id = $this->Model_vehicle->insert($data);
        }
        else {
            $this->Model_vehicle->update($data,$id);
        }

        $features = array();
        if (! is_null($feature)) {
            foreach ($feature as $key => $value) {
                $key = $this->validate_input($key, FALSE, FALSE, TRUE);
                $value = $this->validate_input($value, FALSE, FALSE, TRUE);

                if (is_null($key) || is_null($value)) continue;
                else {
                    $temp = array(
                        'vehicle_feature_id' => $id,
                        'vehicle_feature_key' => $key,
                        'vehicle_feature_value' => $value,
                    );
                    array_push($features, $temp);
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
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $type = $this->validate_input(@$data['type'],FALSE,FALSE,FALSE);
        $number = $this->validate_input(@$data['number'],FALSE,FALSE,FALSE);
        $feature = $this->validate_input(@$data['feature'],FALSE,TRUE,TRUE);
        $price = $this->validate_input(@$data['price'],FALSE,FALSE,FALSE);

        $id = $this->_check_input($user,NULL,$type,$number,$feature,$price);

        $this->output_ok($id);
    }

    public function update_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,FALSE);
        $type = $this->validate_input(@$data['type'],FALSE,FALSE,FALSE);
        $number = $this->validate_input(@$data['number'],FALSE,FALSE,FALSE);
        $feature = $this->validate_input(@$data['feature'],FALSE,TRUE,TRUE);
        $price = $this->validate_input(@$data['price'],FALSE,FALSE,FALSE);

        $id = $this->_check_input($user,$id,$type,$number,$feature,$price);

        $this->output_ok($id);
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);
        $is_free = $this->validate_input(@$data['is_free'],FALSE,FALSE,TRUE);
        $date = $this->validate_input(@$data['date'],FALSE,FALSE,TRUE);
        if ( ! is_bool($is_free)) $is_free = NULL;
        if (is_null($date)) $date = $this->date_time;

        $data = $this->Model_vehicle->select($id,$is_free,$date);
        if (is_null($data)) {
            $this->output_empty();
        }
        else {
            for($i=0;$i<sizeof($data);$i++) {
                $data[$i]->feature = $this->Model_vehicle->select_feature($data[$i]->id);
            }
            $this->output_ok($data);
        }
    }

    public function delete_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,FALSE);

        $data = array(
            'vehicle_is_active' => FALSE
        );
        $this->Model_vehicle->update($data,$id);
        $this->output_ok(NULL);
    }
}