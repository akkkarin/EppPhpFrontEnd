<?php
// agency model
class TrackingModel extends Model {
      
    public function getCount() {
    	//$w = array(statusid => 1);
    	return $this->count();
    }
}