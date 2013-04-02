<?php
/**
 *
 */
class User_model extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function get_user($select, $array_where, $array_like, $first, $offset, $order_by) {
		$data = array();
		$order = key($order_by);
		if ($order != null) {
			$sort = $order_by[$order];
			$this -> db -> order_by($order, $sort);
		}

		$this -> db -> select("*,users.id as user_id,users.state as user_state");
		$this -> db -> from('users');
		$this -> db -> where($array_where);
		$this -> db -> like($array_like);
		$this -> db -> limit($offset, $first);
		$this -> db -> join('roles', 'users.role_id=roles.id', 'left');
		$query = $this -> db -> get();
		if ($query -> num_rows() > 0) {
			foreach ($query->result() as $rows) {
				$data[] = $rows;
			}
			$query -> free_result();
			return $data;
		} else {
			return null;
		}
	}

	function total($array_where, $array_like) {
		$this -> db -> select('count(*) as total');
		$this -> db -> where($array_where);
		$this -> db -> like($array_like);
		$this -> db -> from('users');
		$query = $this -> db -> get();
		$rows = $query -> result();
		$query -> free_result();
		return $rows[0] -> total;
	}

	function get_user_by_id($id) {
		$select = '*';
		$array_where = array('users.id' => $id);
        return $this -> get_user($select, $array_where,array(), 0, 1, array());
	}

	function get_user_by_oau_id($id, $first, $offset) {
		$select = '*';
		$array_where = array('oau_id' => $id);
		$array_like = array();
		$order_by = array();
		return $this -> get_user($select, $array_where, $array_like, $first, $offset, $order_by);
	}

	function get_user_by_user_name($user_name, $first, $offset) {
		$select = '*';
		$array_where = array();
		$array_like = array('user_name' => $user_name);
		$order_by = array();
		return $this -> get_user($select, $array_where, $array_like, $first, $offset, $order_by);
	}

	function insert_user($data_array) {
		$this -> db -> insert('users', $data_array);
		return $this -> db -> insert_id();
	}

	public function remove_user($arr_where) {
		$this -> db -> where($arr_where);
		$this -> db -> delete('users');
	}

	public function remove_user_by_id($id) {
		$array_where = array('id' => $id);
		$this -> remove_user($array_where);
	}

	function update_user($data_array, $array_where) {
		$this -> db -> where($array_where);
		$this -> db -> update('users', $data_array);
	}

}
?>