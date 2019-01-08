<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dharmawan
 * Date: 06-Dec-18
 * Time: 8:54 PM
 */

/**
 * @property Model_auth $Model_auth
 */
class Auth extends Basic_Controller
{
    /**
     * Auth constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Model_auth');
    }

    public function login_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $username = $this->validate_input(@$data['username']);
        $password = $this->validate_input(@$data['password']);
        $id = $this->Model_auth->auth($username,$password);
        if ($id === null) {
            $this->output_failed();
        }
        else {
            $this->output_ok(array('id' => $id));
        }
    }
}