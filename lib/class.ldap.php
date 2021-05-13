<?php
//echo md5("sip_id:asterisk:password"); die;
class CRM_LDAP {
	
    var $uri="ldap://91.236.251.76";
    var $admin_ldap="cn=admin,dc=precall";
    var $pass_admin="1q2w3e4r";
    var $ldap_base="ou=Users,dc=precall";
    var $connect = false;
	
	function __construct() {
		$this->Connect();
	}
	
	private function Connect() {
		$this->connect = ldap_connect($this->uri);
        ldap_set_option($this->connect, LDAP_OPT_PROTOCOL_VERSION, 3);
		return $this;
	}

    function all_uid() {
	    $ldapbind = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN = ldap_search($this->connect, $this->ldap_base, "uid=*");
        $ldap_search_result = ldap_get_entries($this->connect, $ldap_DN);
       
	    return $ldap_search_result;	
	}
	
	function seach_uid() {
	    $ldapbind = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN=ldap_search($this->connect, $this->ldap_base, "(uid=*)");
        $ldap_search_result = ldap_get_entries($this->connect, $ldap_DN);
        for ($i=0; $i<$ldap_search_result["count"]; $i++)
	    {
	        $uid_arr[$i] = $ldap_search_result[$i]["uidnumber"][0];
	    }
	    sort($uid_arr);
	    $element=0;
        for($i = $uid_arr[0]; $i < end($uid_arr); $i++)
        {
            if($i!=$uid_arr[$element]) {
                $next_uid = ((int)($uid_arr[$element-1]))+1;
                break;
            } 
            $element++;
        }
        if(!$next_uid) {
            $next_uid = ((int)end($uid_arr))+1;
        }
	    return $next_uid;	
	}
    
	function search_gid() {
	    $ldapbind = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN=ldap_search($this->connect, "ou=Users,dc=precall", "(gid=*)");
        $ldap_search_result = ldap_get_entries($this->connect, $ldap_DN);
        for ($i=0; $i<$ldap_search_result["count"]; $i++)
	    {
	        $gid_arr[$i] = $ldap_search_result[$i]["gid"][0];
	    }
	    $next_gid = ((int)end($gid_arr))+1;
        
	    return $next_gid;	
	}
    
	function pass_gen($pass, $sip_id = 0, $is_pack = 0) {
        if(!$is_pack) $passwd = "{MD5}".md5($sip_id.":asterisk:".$pass);
        else $passwd = "{MD5}".base64_encode( pack ("H*", md5 ( $pass ) ) );
        
	    return $passwd;
	}
    
	function free_sip($group_name, $sip_rate, $is_ar = 0, $crm_sips = array()) {
        $ldap_filter = "objectClass=posixGroup";
        $ldapbind = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN = ldap_search($this->connect, 'oou=Users,dc=precall', $ldap_filter);
        $ldap_search_result = ldap_get_entries($this->connect, $ldap_DN);
        for($k=0; $k<=$ldap_search_result["count"]; $k++) {
            if (isset($ldap_search_result[$k]["uid"]["count"])){
                for ($i=0; $i<$ldap_search_result[$k]["uid"]["count"]; $i++)
                {
                    $ldap_DN=ldap_search($this->connect,$this->ldap_base,'(&(cn='.$ldap_search_result[$k]["uid"][$i].')(objectClass=person))');
                    $ldap_sip_result = ldap_get_entries($this->connect, $ldap_DN);
                    
                    if( (int)(@$ldap_sip_result[0]["telephonenumber"][0]) ) {
                        $sip_mas[$k.$i] = $ldap_sip_result[0]["telephonenumber"][0];
                    }
                }
            }
        }
        sort($sip_mas);
        $sip_rate = explode("-",$sip_rate);
        for($s = (int)$sip_rate[0]; $s <= (int)$sip_rate[1]; $s++)
	    {
	        $ar_sip[$s] = (string)$s;
	    }
        $sip_mas = array_intersect($sip_mas,$ar_sip);
        $sip_mas = array_diff($ar_sip,$sip_mas);
        $sip_mas = array_diff($sip_mas,$crm_sips);
        if($is_ar) return $sip_mas;
        else return current($sip_mas);
	}

	function user_add($login, $group_name, $user_name, $user_second_name, $sip_rate, $crm_id, $user_pass = '11111111',$sip_set = 0) {
        $add_ldap_group = false;
        $add_ldap_user = false;
        $mod_ldap = false;
        $ldap_filter="uid=$login";
        $pre_gecos=explode(".",$login);
        $gecos = ucwords($pre_gecos[0].' '.$pre_gecos[1]);
        $ldapbind_add = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_add = ldap_search($this->connect, $this->ldap_base, $ldap_filter);
        $ldap_search_result_add = ldap_get_entries($this->connect, $ldap_DN_add);
        $result_add = $ldap_search_result_add["count"];
	     $sip_id = $sip_set;
		//error_log('<><>'.$sip_id .'<>><'.$group_name.'<>><'.$sip_rate.'<>><'.$crm_sips);
	        $info["cn"] = $login;
	        $info["givenname"] = $user_name;
	        $info["sn"]= $user_second_name;
	        $info["userpassword"] = $this->pass_gen($user_pass,0,1);
	        $info["uid"] = $login;
	        $info["mail"] = $login."@premium-call.com.ua";
	        $info["o"] = "Precall";
	        $info["astaccountdeny"] = "0.0.0.0/0";
	        $info["astaccountpermit"][0] = "0.0.0.0/0";
            $info["astaccountqualify"] = 'yes';
            $info["astaccountcalllimit"] = '5';
            $info["astaccountdisallowedcodec"] = 'all';
            $info["astaccountallowedcodec"] = 'g729,alaw,ulaw';
            $info["astaccountcallerid"] = '"'.$login.'" <'.$sip_id.'>';
	        $info["astaccountcontext"] = "operator";
	        $info["astaccountrealmedpassword"] = $this->pass_gen($user_pass,$sip_id,0);
	        $info["telephonenumber"] = (string)$sip_id;
	        $info["astaccounthost"] = "dynamic";
	        $info["astaccounttype"] = "friend";
	        $info["objectclass"][0] = "inetOrgPerson";
	        $info["objectclass"][1] = "AsteriskSIPUser";
	        $add_ldap_user = ldap_add($this->connect, "uid=$login,ou=Users,".$this->ldap_base."", $info);   
        
 	   return $add_ldap_user;
	}
	
	function user_destroy($login,$group_name) {
        $ldap_filter="uid=$login";
        $ldapbind_del = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_del = ldap_search($this->connect, $this->ldap_base, $ldap_filter);
        $ldap_search_result_del = ldap_get_entries($this->connect, $ldap_DN_del);
        $result_del = $ldap_search_result_del["count"];
        if ($result_del == 1) {
	        $del_ldap_user = ldap_delete($this->connect, "uid=$login,ou=Users,".$this->ldap_base."");
            if($del_ldap_user) {
                $ldap_filter_gr = "cn=$login";
                $ldap_DN_gr = ldap_search($this->connect, $this->ldap_base, $ldap_filter_gr);
                $ldap_search_result_gr = ldap_get_entries($this->connect, $ldap_DN_gr);
                $result_gr = $ldap_search_result_gr["count"];
                    if ($result_gr == 1) {
                        $del_ldap_group_user = ldap_delete($this->connect, "cn=$login,ou=Group-users,".$this->ldap_base."");
                    } 
                if($del_ldap_group_user) {
                    $ldap_filter_mod = "cn=$group_name";
                    $ldap_DN_mod = ldap_search($this->connect, $this->ldap_base, $ldap_filter_mod);
                    $ldap_search_result_mod = ldap_get_entries($this->connect, $ldap_DN_mod);
                    $result_mod = $ldap_search_result_mod["count"];
                    if ($result_mod == 1) {
                        $info_gr_mod["memberUid"][]="$login";
                        $del_ldap_mod_user = ldap_mod_del($this->connect, "cn=$group_name,ou=Group,".$this->ldap_base."", $info_gr_mod);
                    } 
                }
            } 
        }
        
        if($del_ldap_mod_user) {
            $s_con = $this->ssh_connect();
            if (ssh2_auth_pubkey_file($s_con, 'crm',
                          '/var/www/.ssh/id_rsa.pub',
                          '/var/www/.ssh/id_rsa', 'secret')) {
                  $is_dir = is_dir("/media/ldap-user/".$login);
                  if($is_dir) $stream = ssh2_exec($s_con, 'sudo mv /media/nas/ldap-users/'.$login.' /media/nas/ldap-users.del/'); else  $stream = false;
            } else { $stream = false; }
        }
        return $del_ldap_mod_user;
	}
    
	function change_pass($pass,$login,$sip_id){
        $del_ldap_mod_pass = false;
        $ldap_filter="uid=$login";
        $ldapbind_pass = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_pass = ldap_search($this->connect, $this->ldap_base, $ldap_filter);
        $ldap_search_result_pass = ldap_get_entries($this->connect, $ldap_DN_pass);
        if ($ldap_search_result_pass['count'] == 1) {
            $info_gr_mod["userpassword"] = $this->pass_gen($pass,0,1);
            $info_gr_mod["astaccountrealmedpassword"] = $this->pass_gen($pass,$sip_id,0);
            $del_ldap_mod_pass = ldap_mod_replace($this->connect, "uid=$login,ou=Users,".$this->ldap_base."", $info_gr_mod);
        }
	   return $del_ldap_mod_pass;
	}
    
	function change_sip($login,$sip_id_new){
        $del_ldap_mod_sip = false;
        $ldap_filter="uid=$login";
        $ldapbind_sip = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_sip = ldap_search($this->connect, $this->ldap_base, $ldap_filter);
        $ldap_search_result_pass = ldap_get_entries($this->connect, $ldap_DN_sip);
        if ($ldap_search_result_pass['count'] == 1) {
            $info_gr_mod["astaccountcallerid"] = '"'.$login.'" <'.$sip_id_new.'>';
	        $info_gr_mod["astaccountcontext"] = "operator";
	        $info_gr_mod["telephonenumber"] = (string)$sip_id_new;
            $del_ldap_mod_sip = ldap_mod_replace($this->connect, "uid=$login,ou=Users,".$this->ldap_base."", $info_gr_mod);
        }
        return $del_ldap_mod_sip;
	}
    
	function change_login($login,$login_new,$user_name,$user_second_name,$sip_id,$group_name){
        $del_ldap_mod_login = false;
        $ldap_filter="uid=$login";
        $ldapbind_login = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_login = ldap_search($this->connect, $this->ldap_base, $ldap_filter);
        $ldap_search_result_login = ldap_get_entries($this->connect, $ldap_DN_login);
        $pre_gecos=explode(".",$login_new);
        $gecos = ucwords($pre_gecos[0].' '.$pre_gecos[1]);
	//error_log($ldap_search_result_login['count'].'<><>><<>><'.$login);
        if ($ldap_search_result_login['count'] == 1) {
            $rename = ldap_rename($this->connect, "uid=$login,ou=Users,".$this->ldap_base, "uid=$login_new", "ou=Users,".$this->ldap_base, 1);
            if($rename) {
                $info_gr_mod["cn"] = $login_new;
                $info_gr_mod["givenname"] = $user_name;
                $info_gr_mod["sn"]= $user_second_name;
                $info_gr_mod["uid"] = $login_new;
                $info_gr_mod["gecos"] = $gecos;
                $info_gr_mod["displayName"] = $user_second_name.' '.$user_name;
                $info_gr_mod["homedirectory"] = "/home-ldap/".$login_new;
                $info_gr_mod["mail"] = $login_new."@cartli.com.ua";
                $info_gr_mod["destinationindicator"] = "/var/mail/cartli.com.ua/".$login_new."/";
                $info_gr_mod["personaltitle"] = "cartli.com.ua/".$login_new."/";
                $info_gr_mod["shadowlastchange"] = (string)((int)((time())/(3600*24)));
                $info_gr_mod["astaccountcallerid"] = '"'.$login_new.'" <'.$sip_id.'>';
                $ldap_mod_login = ldap_mod_replace($this->connect, "uid=$login_new,ou=Users,".$this->ldap_base."", $info_gr_mod);
            } 
        }
        if($ldap_mod_login) {
            $ldap_filter_mod = "cn=$group_name";
            $ldap_DN_mod = ldap_search($this->connect, $this->ldap_base, $ldap_filter_mod);
            $ldap_search_result_mod = ldap_get_entries($this->connect, $ldap_DN_mod);
            $result_mod_del = $ldap_search_result_mod["count"];
            if ($result_mod_del == 1) {
                $info_gr_mod_del["memberUid"][]="$login";
                $del_ldap_mod_user = ldap_mod_del($this->connect, "cn=$group_name,ou=Group,".$this->ldap_base."", $info_gr_mod_del);
            }
            if($del_ldap_mod_user) {
                $ldap_DN_mod = ldap_search($this->connect, $this->ldap_base, $ldap_filter_mod);
                $ldap_search_result_mod = ldap_get_entries($this->connect, $ldap_DN_mod);
                $result_mod_add = $ldap_search_result_mod["count"];
                if ($result_mod_add == 1) {
                    $info_gr_mod_add["memberUid"][0]="$login_new";
                    $mod_ldap = ldap_mod_add($this->connect,"cn=$group_name,ou=Group,".$this->ldap_base."", $info_gr_mod_add);
                }
            }
            $ldap_filter_gr = "cn=$login";
            $ldap_DN_gr = ldap_search($this->connect, $this->ldap_base, $ldap_filter_gr);
            $ldap_search_result_gr = ldap_get_entries($this->connect, $ldap_DN_gr);
            $result_gr = $ldap_search_result_gr["count"];
            if ($result_gr == 1) {
                $rename = ldap_rename ($this->connect, "cn=$login,ou=Group-users,".$this->ldap_base."", "cn=$login_new", "ou=Group-users,".$this->ldap_base."",1);
            }
        	if($rename) {
        	$info_gr_mod_ren["memberuid"] = "$login_new";
                $ldap_mod_login = ldap_mod_replace($this->connect, "cn=$login_new,ou=Group-users,".$this->ldap_base."", $info_gr_mod_ren);
        	}
        
	    $stream = exec("/usr/local/bin/mail-dir $login $login_new");
        
	    $s_con = $this->ssh_connect();
        if (ssh2_auth_pubkey_file($s_con, 'crm',
                          '/var/www/.ssh/id_rsa.pub',
                          '/var/www/.ssh/id_rsa', 'secret')) {
                  $is_dir = is_dir("/media/ldap-user/".$login);
                  if($is_dir) $stream = ssh2_exec($s_con, 'sudo mv /media/nas/ldap-users/'.$login.'/ /media/nas/ldap-users/'.$login_new.'/'); else  $stream = false;
            } else { $stream = false; }
	//error_log($is_dir.'<><><><>'.$stream);
        }
	   return $ldap_mod_login;
	}
    
	function create_group($group_name) {
	    $ldap_filter_mod = "cn=$group_name";
	    $ldapbind_pass = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_mod = ldap_search($this->connect, $this->ldap_base, $ldap_filter_mod);
        $ldap_search_result_mod = ldap_get_entries($this->connect, $ldap_DN_mod);
        $result_mod = $ldap_search_result_mod["count"];
        if ($result_mod == 0) {
            $info_gr["cn"] = $group_name;
            $info_gr["gidnumber"] = $this->search_gid();
            $info_gr["objectclass"][0] = "posixGroup";
            $info_gr["objectclass"][1] = "top";
            $mod_ldap = ldap_add($this->connect,"cn=$group_name,ou=Group,".$this->ldap_base."", $info_gr);
        }
        return $mod_ldap;
	}
    
	function destroy_group($group_name) {
        $ldap_filter_mod = "cn=$group_name";
        $ldapbind_pass = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_DN_mod = ldap_search($this->connect, $this->ldap_base, $ldap_filter_mod);
        $ldap_search_result_mod = ldap_get_entries($this->connect, $ldap_DN_mod);
        $result_mod = $ldap_search_result_mod["count"];
        if ($result_mod == 1) {
            $mod_ldap = ldap_delete($this->connect,"cn=$group_name,ou=Group,".$this->ldap_base."");
        }
	   return $mod_ldap;
	}
    
	function close() {
	    return ldap_close($this->connect);
	}
    
    function getUser($uid)
	{
		$SearchFor = $uid;
		$SearchField = "uid";
		
		$filter="($SearchField=$SearchFor*)";
		$sr=ldap_search($this->connect, $this->ldap_base, $filter);
		$info = ldap_get_entries($this->connect, $sr);
		return $info;
	}
    
    function serchAll(){
        $ldap_filter = "(telephoneNumber=*)";
	    $ldapbind_pass = ldap_bind($this->connect, $this->admin_ldap, $this->pass_admin);
        $ldap_search = ldap_search($this->connect, $this->ldap_base, $ldap_filter);
        $ldap_search_result = ldap_get_entries($this->connect, $ldap_search);
        
        return $ldap_search_result;
    }
    
    function setError($text)
    {
        $this->error[] = $text;
    }
    
    function getError()
    {
        foreach ($this->error as $errr)
        {
            echo $errr."<br/>\n";
        }
        //return $this->error;
    }
}