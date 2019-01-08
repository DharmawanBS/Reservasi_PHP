<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 06-Dec-18
 * Time: 10:15 PM
 */

/**
 * @property Model_vehicle $Model_vehicle
 * @property Model_reservation $Model_reservation
 * @property Model_user_type $Model_user_type
 */
class Reservation extends Basic_Controller
{
    private $month;

    /**
     * Reservation constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Model_vehicle','Model_reservation','Model_user_type'));

        $this->month = array(
            'JA',
            'FB',
            'MR',
            'AP',
            'MY',
            'JN',
            'JL',
            'AU',
            'SP',
            'OK',
            'NO',
            'DS'
        );
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);
        $code = $this->validate_input(@$data['code'],FALSE,FALSE,TRUE);
        $vehicle = $this->validate_input(@$data['vehicle'],TRUE,FALSE,TRUE);
        $start = $this->validate_input(@$data['start'],FALSE,FALSE,TRUE);
        $end = $this->validate_input(@$data['end'],FALSE,FALSE,TRUE);
        $is_approved = $this->validate_input(@$data['is_approved'],FALSE,FALSE,TRUE);
        $is_cancel = $this->validate_input(@$data['is_cancel'],FALSE,FALSE,TRUE);
        $past_time = $this->validate_input(@$data['past_time'],FALSE,FALSE,TRUE);
        $future_date = $this->validate_input(@$data['future_time'],FALSE,FALSE,TRUE);

        if ( ! is_bool($past_time)) $past_time = NULL;
        if ( ! is_bool($future_date)) $future_date = NULL;

        if ($past_time === TRUE) {
            $temp_end = date("Y-m-d", strtotime($this->date ."- 1 day"));
            if ($end === null) $end = $temp_end;
            else {
                if ($end > $temp_end) $end = $temp_end;
            }
        }
        if ($future_date === TRUE) {
            $temp_start = $this->date;
            if ($start === null) $start = $temp_start;
            else {
                if ($start > $temp_start) $start = $temp_start;
            }
        }
        if ( $start !== null AND $end !== null) {
            if ($end < $start) {
                $start = NULL;
                $end = NULL;
            }
        }

        $data = $this->Model_reservation->select($id,$code,$vehicle,$start,$end,$is_approved,$is_cancel);

        if ($data === null) {
            $this->output_empty();
        }
        else {
            for($i=0, $iMax = count($data); $i< $iMax; $i++) {
                $data[$i]->crew = $this->Model_reservation->select_crew($data[$i]->id);
            }
            $this->output_ok($data);
        }
    }

    private function _need_crew($crew,$id)
    {
        if(count($crew) == 0) {
            $this->Model_reservation->delete($id);
            $this->output_invalid();
        }

        $list_crew = array();
        foreach ($crew as $item){
            $name = $this->validate_input(@$item['name'],FALSE,FALSE,TRUE);
            $status = $this->validate_input(@$item['status'],FALSE,FALSE,TRUE);

            if ($name === null || $status === null) {
                $this->Model_reservation->delete($id);
                $this->output_invalid();
            }

            $temp = array(
                'reservation_id' => $id,
                'crew_name' => $name,
                'crew_status' => $status
            );
            $list_crew[] = $temp;
        }
        $this->Model_reservation->insert_crew($list_crew);
    }

    public function reservation_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $client_id = $this->validate_input(@$data['client_id'],TRUE,FALSE,TRUE);
        $client_name = $this->validate_input(@$data['client_name']);
        $client_phone = $this->validate_input(@$data['client_phone']);
        $destination = $this->validate_input(@$data['destination']);
        $pick_up_location = $this->validate_input(@$data['pick_up_location']);
        $start = $this->validate_input(@$data['start']);
        $end = $this->validate_input(@$data['end']);
        $vehicle = $this->validate_input(@$data['vehicle'],TRUE);
        $notes = $this->validate_input(@$data['notes']);
        $price = $this->validate_input(@$data['price']);
        $user_type_id = $this->validate_input(@$data['user_type_id'],TRUE,FALSE,TRUE);
        $crew = $this->validate_input(@$data['crew'],FALSE,TRUE,TRUE);

        if ($this->Model_user_type->is_admin($user)) {
            $is_approved = TRUE;
            $approve_datetime = $this->date_time;
            $approve_id = $user;

            if ($crew === null || (is_array($crew) AND count($crew) == 0)) $this->output_invalid();
        }
        else {
            $is_approved = NULL;
            $approve_datetime = NULL;
            $approve_id = NULL;
        }

        if ($this->Model_reservation->is_free($vehicle,$start,$end)) {
            $data = array(
                'reservation_is_approved' => $is_approved,
                'reservation_approved_datetime' => $approve_datetime,
                'reservation_approved_id' => $approve_id,
                'reservation_start' => $start,
                'reservation_end' => $end,
                'reservation_datetime' => $this->date_time,
                'reservation_client_id' => $client_id,
                'reservation_client_name' => $client_name,
                'reservation_client_phone' => $client_phone,
                'reservation_destination' => $destination,
                'reservation_pick_up_location' => $pick_up_location,
                'reservation_notes' => $notes,
                'vehicle_id' => $vehicle,
                'price' => $price,
                'user_id' => $user,
                'user_type_id' => $user_type_id
            );
            $id = $this->Model_reservation->insert($data);

            $code = $this->month[(int)date("m") -1].$id.'-'.date("Y")%2000;

            $data = array(
                'reservation_code' => $code
            );
            $this->Model_reservation->update($data,$id);

            if ($is_approved) {
                $this->_need_crew($crew,$id);
            }

            $this->output_ok($code);
        }
        else {
            $this->output_failed();
        }
    }

    public function approval_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);
        $crew = $this->validate_input(@$data['crew'],FALSE,TRUE);

        if ($this->Model_reservation->is_waiting_approval($id)) {
            $this->_need_crew($crew, $id);

            $data = array(
                'reservation_approved_datetime' => $this->date_time,
                'reservation_is_approved' => TRUE,
                'reservation_approved_id' => $user
            );
            $this->Model_reservation->update($data, $id);
            $this->output_ok(NULL);
        }
        else {
            $this->output_failed();
        }
    }

    public function reject_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);

        if ($this->Model_reservation->is_waiting_approval($id)) {
            $data = array(
                'reservation_approved_datetime' => $this->date_time,
                'reservation_is_approved' => FALSE,
                'reservation_approved_id' => $user
            );
            $this->Model_reservation->update($data, $id);
            $this->output_ok(NULL);
        }
        else {
            $this->output_failed();
        }
    }

    public function cancel_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $user = $this->validate_input(@$data['user'],TRUE);

        if ($this->Model_reservation->is_before_date($id)) {
            $data = array(
                'reservation_cancel_datetime' => $this->date_time,
                'reservation_is_cancel' => TRUE,
                'reservation_cancel_id' => $user
            );
            $this->Model_reservation->update($data, $id);
            $this->output_ok(NULL);
        }
        else {
            $this->output_failed();
        }
    }

    public function print_get()
    {
        $id = $this->input->get('id');

        $data = $this->Model_reservation->select($id,NULL,NULL, NULL,NULL, TRUE,NULL);

        if ($data === null) {
            $this->load->view('failed');
        }
        else {
            $crew = $this->Model_reservation->select_crew($id);

            $data = $data[0];
            $data = array(
                'id' => $data->id,
                'code' => $data->code,
                'client_name' => $data->client_name,
                'client_phone' => $data->client_phone,
                'destination' => $data->destination,
                'pick_up_location' => $data->pick_up_location,
                'notes' => $data->notes,
                'start' => $data->start,
                'end' => $data->end,
                'duration' => $data->duration,
                'vehicle_type' => $data->vehicle_type,
                'vehicle_number' => $data->vehicle_number,
                'price' => $data->price,
                'user' => $data->user,
                'created' => $data->created,
                'crew' => $crew
            );
            $this->load->view('print',$data);
        }
    }
}