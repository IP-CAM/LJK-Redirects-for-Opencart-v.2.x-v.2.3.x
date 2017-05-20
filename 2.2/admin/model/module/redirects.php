<?php

class ModelModuleRedirects extends Model {

	public function listAllRedirects() {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_redirect rd ORDER by rd.`id`");

		return $query->rows;

	}

	public function deleteAllRedirects(){
	    $this->db->query("DELETE FROM " . DB_PREFIX . "url_redirect");
    }

    public function saveRedirect($data) {

        $query = "INSERT INTO " . DB_PREFIX . "url_redirect (old_url, new_url) VALUES ('" . $this->db->escape($data['old_url']) . "', '" . $this->db->escape($data['new_url']) . "') ON DUPLICATE KEY UPDATE old_url = '". $this->db->escape($data['old_url']) . "', new_url = '" . $this->db->escape($data['new_url']) . "'" ;
        $this->db->query($query);

    }
}