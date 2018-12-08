<?php
/**
 * Created by IntelliJ IDEA.
 * User: DELL
 * Date: 08-Dec-18
 * Time: 12:47 PM
 */

/**
 * @property Model_user_type $Model_user_type
 */
class User_type extends Basic_Controller
{

    /**
     * User_type constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Model_user_type');
    }

    private function _check_input($user,$id,$name)
    {
        $data = array(
            'user_type_name' => $name,
            'user_type_lastmodified' => $this->date_time,
            'user_type_lastmodified_id' => $user
        );

        if (is_null($id)) {
            $data['user_type_created'] = $this->date_time;
            $data['user_type_created_id'] = $user;
            $data['user_type_is_active'] = TRUE;
            $id = $this->Model_user_type->insert($data);
        }
        else {
            $this->Model_user_type->update($data,$id);
        }

        return $id;
    }

    public function insert_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $name = $this->validate_input(@$data['name'],FALSE,TRUE,FALSE);

        $id = $this->_check_input($user,NULL,$name);

        $this->output_ok($id);
    }

    public function update_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $user = $this->validate_input(@$data['user'],TRUE,FALSE,FALSE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,FALSE);
        $name = $this->validate_input(@$data['name'],FALSE,TRUE,FALSE);

        $id = $this->_check_input($user,$id,$name);

        $this->output_ok($id);
    }

    public function read_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,TRUE);

        $data = $this->Model_user_type->select($id);
        if (is_null($data)) {
            $this->output_empty();
        }
        else {
            $this->output_ok($data);
        }
    }

    public function delete_post()
    {
        //  get input data
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $id = $this->validate_input(@$data['id'],TRUE,FALSE,FALSE);

        $data = array(
            'user_type_is_active' => FALSE
        );
        $this->Model_user_type->update($data,$id);
        $this->output_ok(NULL);
    }
}