<?php

use dokuwiki\Extension\AuthPlugin;

/**
 * DokuWiki Plugin autham (Auth Component)
 *
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mr_Fang <klxf@vip.qq.com>
 */
class auth_plugin_autham extends AuthPlugin
{
    /** @inheritDoc */
    public function __construct()
    {
        parent::__construct(); // for compatibility

        // FIXME set capabilities accordingly
        //$this->cando['addUser']     = false; // can Users be created?
        //$this->cando['delUser']     = false; // can Users be deleted?
        //$this->cando['modLogin']    = false; // can login names be changed?
        //$this->cando['modPass']     = false; // can passwords be changed?
        //$this->cando['modName']     = false; // can real names be changed?
        //$this->cando['modMail']     = false; // can emails be changed?
        //$this->cando['modGroups']   = false; // can groups be changed?
        //$this->cando['getUsers']    = false; // can a (filtered) list of users be retrieved?
        //$this->cando['getUserCount']= false; // can the number of users be retrieved?
        //$this->cando['getGroups']   = false; // can a list of available groups be retrieved?
        //$this->cando['external']    = false; // does the module do external auth checking?
        //$this->cando['logout']      = true; // can the user logout again? (eg. not possible with HTTP auth)

        // FIXME intialize your auth system and set success to true, if successful
        $this->success = true;
    }

    /** @inheritDoc */
    // public function logOff()
    // {
    // }

    /** @inheritDoc */
    //public function trustExternal($user, $pass, $sticky = false)
    //{
        /* some example:

        global $USERINFO;
        global $conf;
        $sticky ? $sticky = true : $sticky = false; //sanity check

        // do the checking here

        // set the globals if authed
        $USERINFO['name'] = 'FIXME';
        $USERINFO['mail'] = 'FIXME';
        $USERINFO['grps'] = array('FIXME');
        $_SERVER['REMOTE_USER'] = $user;
        $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
        $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
        $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
        return true;

        */
    //}

    /** @inheritDoc */
    public function checkPass($user, $pass)
    {
        global $conf;
        
        $sql_host = $conf['plugin']['autham']['sql_host'];
        $sql_user = $conf['plugin']['autham']['sql_user'];
        $sql_pass = $conf['plugin']['autham']['sql_pass'];
        $database = $conf['plugin']['autham']['database'];
        $table = $conf['plugin']['autham']['table'];
        
        $conn = new mysqli($sql_host, $sql_user, $sql_pass, $database);
        if ($conn->connect_error) {
            die("连接数据库失败: " . $conn->connect_error);
        }
        $user = strtolower(mysqli_real_escape_string($conn, $user));
        $query = "SELECT * FROM $table WHERE username = '$user'";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $password = $row["password"];
            $conn->close();
            
            $password = explode("$", $password);
            $salt = $password[2];
            $pass_md5 = $password[3];
            if($pass_md5 == hash("sha256", hash("sha256", $pass).$salt)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        
        
        return false; // return true if okay
    }

    /** @inheritDoc */
    public function getUserData($user, $requireGroups = true)
    {
        global $conf;
        
        $sql_host = $conf['plugin']['autham']['sql_host'];
        $sql_user = $conf['plugin']['autham']['sql_user'];
        $sql_pass = $conf['plugin']['autham']['sql_pass'];
        $database = $conf['plugin']['autham']['database'];
        $table = $conf['plugin']['autham']['table'];
        $admin = $conf['plugin']['autham']['admin'];
        
        $conn = new mysqli($sql_host, $sql_user, $sql_pass, $database);
        if ($conn->connect_error) {
            die("连接数据库失败: " . $conn->connect_error);
        }
        $user = strtolower(mysqli_real_escape_string($conn, $user));
        $query = "SELECT * FROM $table WHERE username = '$user'";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $realName = $row["realname"];
            $email = $row["email"];
            $conn->close();
			
            $admin = explode(",", $admin);
			
            if(in_array($realName, $admin)) {
                $group = ['admin'];
            } else {
                $group = ['user'];
            }
            
            $userinfo = [
                'name' => $realName,
                'mail' => $email,
                'grps' => $group
            ];
        } else {
            return false;
        }

        return $userinfo;
    }

    /** @inheritDoc */
    //public function createUser($user, $pass, $name, $mail, $grps = null)
    //{
        // FIXME implement
    //    return null;
    //}

    /** @inheritDoc */
    //public function modifyUser($user, $changes)
    //{
        // FIXME implement
    //    return false;
    //}

    /** @inheritDoc */
    //public function deleteUsers($users)
    //{
        // FIXME implement
    //    return false;
    //}

    /** @inheritDoc */
    //public function retrieveUsers($start = 0, $limit = 0, $filter = null)
    //{
        // FIXME implement
    //    return array();
    //}

    /** @inheritDoc */
    //public function getUserCount($filter = array())
    //{
        // FIXME implement
    //    return 0;
    //}

    /** @inheritDoc */
    //public function addGroup($group)
    //{
        // FIXME implement
    //    return false;
    //}

    /** @inheritDoc */
    //public function retrieveGroups($start = 0, $limit = 0)
    //{
        // FIXME implement
    //    return array();
    //}

    /** @inheritDoc */
    public function isCaseSensitive()
    {
        return true;
    }

    /** @inheritDoc */
    public function cleanUser($user)
    {
        return $user;
    }

    /** @inheritDoc */
    public function cleanGroup($group)
    {
        return $group;
    }

    /** @inheritDoc */
    //public function useSessionCache($user)
    //{
      // FIXME implement
    //}
}
