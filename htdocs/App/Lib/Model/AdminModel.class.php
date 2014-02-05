<?php
// agency model
class AdminModel extends Model {
    
    public function accountExisted($account) {
        $w = array('account' => $account);
        $f = array('uid');
        $result = $this->where($w)->field($field)->find();

        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }
    
    public function getAdminInfoById($id) {
    	return $this->where(array('id'=>$id))->find();
    }
     
    public function getAdminInfoByAccount($account) {
        return $this->where(array('account'=>$account))->find();
    }
    
    public function getCount() {
    	//$w = array(statusid => 1);
    	return $this->count();
    }

    public function deleteUsersByIds($ids) {
        if (!isset($ids) || empty($ids)){
            return false;
        }
         
        if(is_array($ids)){
            if(count($ids) > 1) {
                $w = 'id in('.implode(',',$ids).')';
            } else if (count($ids) == 1) {
                $w = 'id='.$ids[0];
            }
        } 
        return $this->where($w)->delete();
    }
}