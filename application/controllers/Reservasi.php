<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 06-Dec-18
 * Time: 10:15 PM
 */

/**
 * @property Model_vehicle $Model_vehicle
 * @property Model_reservasi $Model_reservasi
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

        $this->load->model(array('Model_vehicle','Model_reservasi'));

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

    public function reservasi_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $name = $this->validate_input(@$data['name'],FALSE,FALSE,FALSE);
        $phone = $this->validate_input(@$data['phone'],FALSE,FALSE,FALSE);
        $destination = $this->validate_input(@$data['destination'],FALSE,FALSE,TRUE);
        $pick_up_location = $this->validate_input(@$data['pick_up_location'],FALSE,FALSE,FALSE);
        $start = $this->validate_input(@$data['start'],FALSE,FALSE,FALSE);
        $end = $this->validate_input(@$data['end'],FALSE,FALSE,FALSE);
        $vehicle = $this->validate_input(@$data['vehicle'],TRUE,FALSE,FALSE);
        $notes = $this->validate_input(@$data['notes'],FALSE,FALSE,FALSE);

        if ($this->Model_reservasi->is_free($vehicle,$start,$end)) {
            $price = $this->Model_vehicle->find_price($vehicle);
            if (is_null($price)) {
                $this->output_failed('a');
            }
            else {
                $data = array(
                    'reservasi_name' => $name,
                    'reservasi_phone' => $phone,
                    'reservasi_destination' => $destination,
                    'reservasi_pick_up_location' => $pick_up_location,
                    'reservasi_start' => $start,
                    'reservasi_end' => $end,
                    'reservasi_vehicle' => $vehicle,
                    'reservasi_notes' => $notes,
                    'reservasi_price' => $price,
                    'reservasi_created' => $this->date_time,
                    'reservasi_created_by' => $user,
                );
                $id = $this->Model_reservasi->insert($data);

                $booking = $this->month[intval(date("m"))-1].$id.'-'.date("Y")%2000;
                
                $data = array(
                    'reservasi_booking' => $booking
                );
                $this->Model_reservasi->update($data,$id);

                $this->output_ok($booking);
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
        $booking = $this->validate_input(@$data['booking'],FALSE,FALSE,TRUE);
        $start = $this->validate_input(@$data['start'],FALSE,FALSE,TRUE);
        $end = $this->validate_input(@$data['end'],FALSE,FALSE,TRUE);

        $data = $this->Model_reservasi->select($id,$booking,$start,$end);

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

        $data = $this->Model_reservasi->select($id,NULL,NULL,NULL);

        if (is_null($data)) {
            $this->load->view('failed');
        }
        else {
            $data = $data[0];
            $data = array(
                'id' => $data->id,
                'booking' => $data->booking,
                'name' => $data->name,
                'phone' => $data->phone,
                'destination' => $data->destination,
                'pick_up_location' => $data->pick_up_location,
                'start' => $data->start,
                'end' => $data->end,
                'vehicle' => $data->vehicle,
                'notes' => $data->notes,
                'price' => $data->price,
                'created' => $data->created,
                'type' => $data->type,
                'number' => $data->number
            );
            $this->load->view('print',$data);
        }
    }
}