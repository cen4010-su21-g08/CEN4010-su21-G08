<?php
    class Channel {
        public $ch_id = null;
        public $name = null;
        public $type = null;
        public $course_id = null;
        
        function __construct($ch_id, $type = null, $name = null, $course_id = null)
        {
            if ($type == null) {
                global $conn;
                
                $sql = "SELECT * FROM `channels` WHERE `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";
                $result = $conn->query($sql);

                $numRows = mysqli_num_rows($result);
                if ($numRows <= 0) {
                    return null;
                }

                $channel = $result->fetch_assoc();

                $this->ch_id = $channel['ch_id'];
                $this->name = $channel['name'];
                $this->type = $channel['type'];
                $this->course_id = $channel['course_id'];
            } else {
                $this->ch_id = $ch_id;
                $this->name = $name;
                $this->type =$type;
                $this->course_id =$course_id;
            }
        }

        public static function get_course_channels($course_id)
        {
            global $conn;

            $sql = "SELECT * FROM `channels` WHERE `course_id` = '" . $conn->real_escape_string($course_id) . "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return array();
            }

            $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = new Channel($row['ch_id'], $row['type'], $row['name'], $row['course_id']);
            }

            return $out;
        }

        public static function get_users_channels_in_course($uid, $course_id, $only_groups = false)
        {
            global $conn;

            $sql = "SELECT * FROM `groupMembership` LEFT JOIN `channels` ON `channels`.`ch_id` = `groupMembership`.`ch_id` WHERE `channels`.`course_id` = '" . $conn->real_escape_string($course_id) . "' AND `groupMembership`.`uid` = '" . $conn->real_escape_string($uid) . "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                $out = array();
            }
            else
            {
                $out = array();
                while ($row = $result->fetch_assoc()) {
                    $out[] = new Channel($row['ch_id'], $row['type'], $row['name'], $row['course_id']);
                }
            }
            if (!$only_groups) {
                $out[] = $course_id;
            }

            return $out;
        }

        public static function create_channel($course_id, $name=null, $type=1, $ch_id = null)
        {
            global $conn;
            /*
            name: optional
            channel: default type 1
            course_id: required
            */

            if (!isset($course_id))
                throwError(500, "Error creating channel: course does not exist");
            
            if (!isset($ch_id))
                $ch_id = generateRandomString();

            $sql = "INSERT INTO `channels` (`ch_id`,";
            if (isset($name)) $sql .= "`name`,";
            $sql .= "`type`,`course_id`) VALUES (";
            $sql .= "'" . $conn->real_escape_string($ch_id) . "'" . ", ";
            if (isset($name))
                $sql .= "'" . $conn->real_escape_string($name) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($type) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($course_id) . "'" . ") ";

            $conn->query($sql);

            $channel = new Channel($ch_id);

            return $channel;
        }

        public function delete_channel()
        {
            global $conn;

            $sql = "DELETE FROM `channels` WHERE `ch_id` = '" . $conn->real_escape_string($this->ch_id) . "'";

            $conn->query($sql);

            $sql = "DELETE FROM `groupMembership` WHERE `ch_id` ='" . $conn->real_escape_string($this->ch_id) . "'";

            $conn->query($sql);

            $sql = "DELETE FROM `messages` WHERE `ch_id` = '" . $conn->real_escape_string($this->ch_id) . "'";

            $conn->query($sql);
        }

        public static function get_members($ch_id)
        {
            global $conn;

            $channel = new Channel($ch_id);

            if ($channel->type == 2)
            {
                $sql = "SELECT `uid` from `groupMembership` WHERE `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";

                $result = $conn->query($sql);
            }
            else
            {
                $sql = "SELECT `uid` from `courseMembership` WHERE `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";

                $result = $conn->query($sql);
            }

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                $out = array();
            }

            $out = array();
            while ($row = $result->fetch_assoc()) {
                $member = new User($row['uid'], null, ["uid", "first_name", "last_name", "display_name"]); 
                $member->display_name = get_display_name($member->first_name, $member->last_name, $member->display_name);
                $out[] = $member;
            }

            return $out;
        }

        /*
            Channel->get_role
            Parameters:
                [$uid] - user ID to get role of (defaults to logged in user)
            Gets the role of a user and returns that value.
            Returns: integer,
                0 = no access
                1 = student
                2 = teacher
                3 = TA (not yet implemented functionality)
        */
        public function get_role($uid = null) {
            if ($uid == null) {
                global $user;
                $uid = $user->uid; 
            }
            
            // get a membership based on the channel type
            $membership = null;
            if ($this->type == 1) {
                $membership = new CourseMembership($uid, $this->ch_id);
            } else if ($this->type == 2) {
                $membership = new GroupMembership($uid, $this->ch_id);
            } else {
                return 0;
            }
            
            if ($membership == null || !isset($membership->role) || $membership->role == null) {
                // no corresponding membership found = no access
                return 0;
            } else {
                return $membership->role;
            }
        }

        public function change_name($name)
        {
            if (!isset($name))
            {
                throwError(500, "name cannot be updated because a new name has not been passed.");
            }
            
            global $conn;

            $sql = "UPDATE `channels` SET `name` = '" . $conn->real_escape_string($name) . "' WHERE `ch_id` = '" . $conn->real_escape_string($this->ch_id) . "'";

            $conn->query($sql);
        }
    }

class GroupMembership
{
    public $uid = null;
    public $ch_id = null;

    function __construct($uid, $ch_id) 
        {
            global $conn;

            $sql = "SELECT * FROM `groupMembership` WHERE `uid` = '" . $conn->real_escape_string($uid) . "' AND `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";
            $result = $conn->query($sql);
            // $statement = $conn->prepare($sql);

            // $statement->bind_param("ss", $uid, $course_id);
            // $statement->execute();
            
            // $result = $statement->get_result();
            $numRows = mysqli_num_rows($result);
            if ($numRows > 0) {
                $groupMembership = $result->fetch_assoc();

                $this->uid = $groupMembership['uid'];
                $this->ch_id = $groupMembership['ch_id'];
            }
        }
    
    public static function is_user_member($uid, $ch_id) {
        if (new GroupMembership($uid, $ch_id) != null) {
            return true;
        } else {
            return false;
        }
    }

    public static function create_membership($uid, $ch_id)
        {
            // $uid = $_SESSION['uid'];
    
            global $conn;

            if ((new GroupMembership($uid, $ch_id))->uid != null)
            {
                $groupMembership = new GroupMembership($uid, $ch_id);
                return $groupMembership;
            }
            else
            {
            $sql = "INSERT INTO `groupMembership` (`uid`, `ch_id`) VALUES (";
            $sql .= "'" . $conn->real_escape_string($uid) . "', ";
            $sql .= "'" . $conn->real_escape_string($ch_id) . "')";

            $conn->query($sql);

            $groupMembership = new GroupMembership($uid, $ch_id);

            return $groupMembership;
            }
        }
    
        public static function delete_membership($uid, $ch_id)
        {
            global $conn;

            $sql = "DELETE FROM `groupMembership` WHERE `uid` = '" . $conn->real_escape_string($uid) . "' AND `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";

            $conn->query($sql);
        }
}