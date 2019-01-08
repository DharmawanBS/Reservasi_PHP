<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 08-Jan-19
 * Time: 7:47 PM
 */

/**
 * @property Model_dashboard $Model_dashboard
 */
class Dashboard extends Basic_Controller
{
    /**
     * Dashboard constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Model_dashboard'));
    }

    public function index_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $month = $this->validate_input(@$data['month'],TRUE,FALSE,TRUE);
        if ($month !== NULL AND ($month < 0 || $month > 12)) {
            $this->output_invalid();
        }

        $out = array(
            'unique_user' => $this->Model_dashboard->user($month),
            'transaction_success' => $this->Model_dashboard->transaction($month),
            'active_vehicle' => $this->Model_dashboard->vehicle(),
            'total_income' => $this->Model_dashboard->income_total($month)
        );
        $this->output_ok($out);
    }

    public function income_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $month = $this->validate_input(@$data['month'],TRUE,FALSE,TRUE);
        if ($month !== NULL AND ($month < 0 || $month > 12)) {
            $this->output_invalid();
        }

        $out = $this->Model_dashboard->income_per_vehicle($month);

        $this->output_ok($out);
    }
}