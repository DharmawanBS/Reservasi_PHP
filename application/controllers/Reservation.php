<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 06-Dec-18
 * Time: 10:15 PM
 */

/**
 * @property Model_vehicle $Model_vehicle
 * @property Model_reservation $Model_reservation
 */
class Reservasi extends Basic_Controller
{
    private $month;

    /**
     * Reservasi constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Model_vehicle','Model_reservation'));

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

    public function reservation_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $start = $this->validate_input(@$data['start'],FALSE,FALSE,FALSE);
        $end = $this->validate_input(@$data['end'],FALSE,FALSE,FALSE);
        $vehicle = $this->validate_input(@$data['vehicle'],TRUE,FALSE,FALSE);
        $price = $this->validate_input(@$data['price'],TRUE,FALSE,FALSE);

        if ($this->Model_reservation->is_free($vehicle,$start,$end)) {
            $price = $this->Model_vehicle->find_price($vehicle);
            if (is_null($price)) {
                $this->output_failed('a');
            }
            else {
                $data = array(
                    'reservation_code' => code,
                    'reservation_start' => $start,
                    'reservation_end' => $end,
                    'vehicle_id' => $vehicle,
                    'price_id' => $price,
                    'reservation_datetime' => $this->date_time,
                    'user_id' => $user
                );
                $id = $this->Model_reservation->insert($data);

                $code = $this->month[intval(date("m"))-1].$id.'-'.date("Y")%2000;

                $data = array(
                    'reservation_code' => $code
                );
                $this->Model_reservation->update($data,$id);

                $this->output_ok($code);
            }
        }
        else {
            $this->output_failed();
        }
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);
        $code = $this->validate_input(@$data['code'],FALSE,FALSE,TRUE);
        $start = $this->validate_input(@$data['start'],FALSE,FALSE,TRUE);
        $end = $this->validate_input(@$data['end'],FALSE,FALSE,TRUE);

        $data = $this->Model_reservation->select($id,$code,$start,$end);

        if (is_null($data)) {
            $this->output_empty();
        }
        else {
            $this->output_ok($data);
        }
    }

    public function print_get()
    {
        $id = $this->input->get('id');

        $data = $this->Model_reservation->select($id,NULL,NULL,NULL);

        if (is_null($data)) {
            $this->load->view('failed');
        }
        else {
            $data = $data[0];
            $data = array(
                'id' => $data->id,
                'code' => $data->code,
                'start' => $data->start,
                'end' => $data->end,
                'vehicle' => $data->vehicle,
                'price' => $data->price,
                'user' => data->user,
                'created' => $data->created
            );
            $this->load->view('print',$data);
        }
    }
}