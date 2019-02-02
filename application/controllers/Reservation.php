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
    protected $month_key;

    /**
     * Reservation constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Model_vehicle','Model_reservation','Model_user_type'));

        $this->month_key = array(
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
        $is_public = $this->validate_input(@$data['is_public'],FALSE,FALSE,TRUE);
        $is_finish = $this->validate_input(@$data['is_finish'],FALSE,FALSE,TRUE);
        $is_approved = $this->validate_input(@$data['is_approved'],FALSE,FALSE,TRUE);
        $past_time = $this->validate_input(@$data['past_time'],FALSE,FALSE,TRUE);
        $future_date = $this->validate_input(@$data['future_time'],FALSE,FALSE,TRUE);

        if ( ! is_bool($is_public)) $is_public = NULL;
        if ( ! is_bool($is_finish)) $is_finish = NULL;
        if ( ! is_bool($is_approved)) $is_approved = NULL;
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

        $data = $this->Model_reservation->select($id,$code,$vehicle,$start,$end,$is_public,$is_finish,$is_approved);

        if ($data === null) {
            $this->output_empty();
        }
        else {
            for($i=0, $iMax = count($data); $i< $iMax; $i++) {
                $data[$i]->payment = $this->Model_reservation->select_payment($data[$i]->id);
            }
            $this->output_ok($data);
        }
    }

    private function _pre($user,$is_approved = NULL)
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $client_id = $this->validate_input(@$data['client_id'],TRUE,FALSE,TRUE);
        $client_name = $this->validate_input(@$data['client_name']);
        $client_phone = $this->validate_input(@$data['client_phone']);
        $destination = $this->validate_input(@$data['destination']);
        $pick_up_location = $this->validate_input(@$data['pick_up_location']);
        $start = $this->validate_input(@$data['start']);
        $end = $this->validate_input(@$data['end']);
        $vehicle = $this->validate_input(@$data['vehicle'],TRUE);
        $notes = $this->validate_input(@$data['notes']);
        $user_type_id = $this->validate_input(@$data['user_type_id'],TRUE,FALSE,TRUE);

        $price = $this->validate_input(@$data['price'],TRUE,FALSE,TRUE);

        if ($is_approved === null) {
            if ($user !== null) {
                if ($this->Model_user_type->is_admin($user)) {
                    $is_approved = TRUE;
                    $approve_datetime = $this->date_time;
                    $approve_id = $user;
                }
            }
            else {
                $is_approved = NULL;
                $approve_datetime = NULL;
                $approve_id = NULL;
            }
        }

        return array(
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
            'user_type_id' => $user_type_id
        );
    }

    private function _set_payment($id,$is_approved)
    {
        if (! $is_approved) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), TRUE);
        $payment_method = $this->validate_input(@$data['payment_method'], FALSE, FALSE, TRUE);
        $payment_type = $this->validate_input(@$data['payment_type'], FALSE, FALSE, TRUE);
        $payment_price = $this->validate_input(@$data['payment_price'], TRUE, FALSE, TRUE);
        $payment_date = $this->validate_input(@$data['payment_date'], FALSE, FALSE, TRUE);

        if ($payment_method === NULL || $payment_type === NULL || $payment_price === NULL || $payment_date === NULL) {
            return;
        }

        $data = array(
            'reservation_id' => $id,
            'payment_method' => $payment_method,
            'payment_type' => $payment_type,
            'payment_price' => $payment_price,
            'payment_date' => $payment_date,
            'payment_insert' => $this->date_time
        );
        $this->Model_reservation->insert_payment($data);
    }

    private function _generate_code($id)
    {
        return $this->month_key[(int)date("m") - 1] . $id . '-' . date("Y") % 2000;
    }

    private function _status($id,$approved,$price,$payment)
    {
        return array(
            'id' => $id,
            'is_approved' => $approved === null ? FALSE : $approved,
            'price_is_set' => $price !== NULL && $price > 0,
            'payment_is_complete' => $payment
        );
    }

    public function reservation_post()
    {
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,TRUE);

        $data = $this->_pre($user);

        if ($this->Model_reservation->is_free($data['vehicle_id'],$data['reservation_start'],$data['reservation_end'])) {

            $id = $this->Model_reservation->insert($data);

            $this->_set_payment($id,$data['reservation_is_approved']);
            $is_payment_completed = $this->Model_reservation->payment_complete($id);

            $update = array(
                'reservation_code' => $this->_generate_code($id),
                'reservation_finish' => $is_payment_completed,
                'user_id' => $user
            );
            $this->Model_reservation->update($update,$id);

            $this->output_ok($this->_status($id,$data['reservation_is_approved'],$data['price'],$is_payment_completed));
        }
        else {
            $this->output_failed();
        }
    }

    public function update_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE);
        $is_approved = $this->validate_input(@$data['is_approved']);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,TRUE);

        $is_approved = $this->to_bool($is_approved);

        if ($this->Model_reservation->is_waiting_approval($id)) {
            $data = $this->_pre($user,$is_approved);

            $this->_set_payment($id,$is_approved);
            $is_payment_completed = $this->Model_reservation->payment_complete($id);

            $data['reservation_code'] = $this->_generate_code($id);
            $data['reservation_finish'] = $is_payment_completed;

            $this->Model_reservation->update($data, $id);
            $this->output_ok($this->_status($id,$data['reservation_is_approved'],$data['price'],$is_payment_completed));
        }
        else {
            $this->output_failed();
        }
    }

    public function print_get()
    {
        $id = $this->input->get('id');

        $data = $this->Model_reservation->select($id,NULL,NULL, NULL,NULL, NULL,TRUE,TRUE);

        if ($data === null) {
            $this->load->view('print_failed');
        }
        else {
            $payment = $this->Model_reservation->select_payment($id);

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
                'paid' => $data->paid,
                'payment' => $payment,
                'user' => $data->user,
                'created' => $data->created
            );
            $this->load->view('print_success',$data);
        }
    }

    public function kuitansi_get()
    {
        $id = $this->input->get('id');

        $data = $this->Model_reservation->select($id,NULL,NULL, NULL,NULL, NULL,NULL,NULL);

        if ($data === null) {
            $this->load->view('kuitansi_failed');
        }
        else {
            $payment = $this->Model_reservation->select_payment($id);

            $data = $data[0];
            $data = array(
                'id' => $data->id,
                'code' => $data->code,
                'client_name' => $data->client_name,
                'start' => $data->start,
                'end' => $data->end,
                'duration' => $data->duration,
                'vehicle_type' => $data->vehicle_type,
                'vehicle_number' => $data->vehicle_number,
                'price' => $data->price,
                'paid' => $data->paid,
                'payment' => $payment
            );
            $this->load->view('kuitansi_success',$data);
        }
    }
}