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
        $start_month = $this->validate_input(@$data['start_month'],TRUE,FALSE,TRUE);
        $end_month = $this->validate_input(@$data['end_month'],TRUE,FALSE,TRUE);
        $year = $this->validate_input(@$data['year'],TRUE,FALSE,TRUE);

        if ($year === NULL) $year = $this->year;
        $prev_year = $year-1;

        if ($start_month === NULL) $start_month = 1;
        if ($end_month === NULL) {
            if ($year === $this->year) {
                $end_month = (int) $this->month;
            }
            else {
                $end_month = 12;
            }
        }

        if ($start_month > $end_month) $start_month = $end_month;

        $data_per_month = array(
            'unique_user' => $this->Model_dashboard->user_per_month($start_month,$end_month,$year),
            'transaction_success' => $this->Model_dashboard->transaction_per_month($start_month,$end_month,$year),
            'total_income' => $this->Model_dashboard->income_total_per_month($start_month,$end_month,$year)
        );

        $data_this_year = array(
            'unique_user' => $this->Model_dashboard->user($year),
            'transaction_success' => $this->Model_dashboard->transaction($year),
            'total_income' => $this->Model_dashboard->income_total($year)
        );

        $data_prev_year = array(
            'unique_user' => $this->Model_dashboard->user($prev_year),
            'transaction_success' => $this->Model_dashboard->transaction($prev_year),
            'total_income' => $this->Model_dashboard->income_total($prev_year)
        );

        $out = array(
            'this_year' => $data_this_year,
            'prev_year' => $data_prev_year,
            'per_month' => $data_per_month,
            'vehicle' => $this->Model_dashboard->vehicle()
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