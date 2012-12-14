<?php
/**
 *
 */
class M_role extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	public function get_role($select, $array_where, $array_like, $first, $offset, $order_by) {
		$data = array();
		$order = key($order_by);
		if ($order != null) {
			$sort = $order_by[$order];
			$this -> db -> order_by($order, $sort);
		}
		$this -> db -> select($select);
		$this -> db -> from('tbl_role');
		$this -> db -> where($array_where);
		$this -> db -> like($array_like);
		$this -> db -> limit($offset, $first);
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

	public function total($array_where, $array_like) {
		$this -> db -> select('count(*) as total');
		$this -> db -> where($array_where);
		$this -> db -> like($array_like);
		$this -> db -> from('tbl_role');
		$query = $this -> db -> get();
		$rows = $query -> result();
		$query -> free_result();
		return $rows[0] -> total;
	}

	public function insert_role($data_array) {
		$this -> db -> insert('tbl_role', $data_array);
		return $this -> db -> insert_id();
	}


	public function remove_role($arr_where) {
		$this -> db -> where($arr_where);
		$this -> db -> delete('tbl_role');
	}

	public function remove_role_by_id($id) {
		$array_where = array('cls_id' => $id);
		$this -> remove_role($array_where);
	}

	public function update_role($data_array, $array_where) {
		$this -> db -> where($array_where);
		$this -> db -> update('tbl_role', $data_array);
	}

}
?>