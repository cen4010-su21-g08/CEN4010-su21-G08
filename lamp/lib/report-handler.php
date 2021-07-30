<?php

class Report
{
    public $r_id = null;
    public $reported = null;
    public $reporter = null;
    public $report_date = null;
    public $reason = null;
    public $m_id = null;
    public $ch_id = null;
    public $course_id = null;
    public $message = null;
    public $flags = null;

    function __construct($r_id = null, $reported = null, $reporter = null, $report_date = null, $reason = null, $m_id = null, $ch_id = null, $course_id = null, $message = null, $flags = null) {
        if ($r_id == null) {
            // do nothing
        } else if ($reported == null) {
            // query db with $r_id
            global $conn;
            
            $sql = "SELECT * FROM `reports` WHERE `r_id` = '" . $conn->real_escape_string($r_id) .  "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                // do nothing (no matching report)
            } else {
                $report = $result->fetch_assoc();
                
                $this->r_id = $report['r_id'];
                $this->reported = $report['reported'];
                $this->reporter = $report['reporter'];
                $this->report_date = $report['report_date'];
                $this->reason = $report['reason'];
                $this->m_id = $report['m_id'];
                $this->ch_id = $report['ch_id'];
                $this->course_id = $report['course_id'];
                $this->message = $report['message'];
                $this->flags = $report['flags'];
            }

        } else {
            $this->r_id = $r_id;
            $this->reported = $reported;
            $this->reporter = $reporter;
            $this->report_date = $report_date;
            $this->reason = $reason;
            $this->m_id = $m_id;
            $this->ch_id = $ch_id;
            $this->course_id = $course_id;
            $this->message = $message;
            $this->flags = $flags;
        }
    }

    public static function get($r_id) {
        // helper function
        return new Report($r_id);
    }

    public static function list_by_courseReports($course_id) {
        global $conn;
        $sql = "SELECT * FROM `reports` WHERE 'course_id' = '" . $conn->real_escape_string($course_id) . "'";
        $result = $conn->query($sql);
        $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = new Report($row['r_id'], $row['reported'], $row['reporter'], $row['report_date'], $row['reason'], $row['m_id'], $row['ch_id'], $row['course_id'], $row['message'], $row['flags']);
            }
            return $out;
    }
    
    public static function list_by_reportedUser($user_id) {
        global $conn;
        $sql = "SELECT * FROM `reports` WHERE 'reported' = '" . $conn->real_escape_string($user_id) . "'";
        $result = $conn->query($sql);
        $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = new Report($row['r_id'], $row['reported'], $row['reporter'], $row['report_date'], $row['reason'], $row['m_id'], $row['ch_id'], $row['course_id'], $row['message'], $row['flags']);
            }
            return $out;
    }

    public static function list_by_reporter($user_id) {
        global $conn;
        $sql = "SELECT * FROM `reports` WHERE 'reporter' = '" . $conn->real_escape_string($user_id) . "'";
        $result = $conn->query($sql);
        $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = new Report($row['r_id'], $row['reported'], $row['reporter'], $row['report_date'], $row['reason'], $row['m_id'], $row['ch_id'], $row['course_id'], $row['message'], $row['flags']);
            }
            return $out;
    }

    public static function create($reported, $reason, $m_id, $ch_id) {
        global $user;
        $reporter = $user->uid;

        $channel = new Channel($ch_id);
        $course_id = $channel->course_id;

        $m = Message::get($m_id);
        $message = $m->message;
    }

    




    public function ignore() {

    }
    

    /* Report Actions */
    public function deleteMessage() {

    }

    public function muteUser() {

    }

    public function kickUser() {

    }

    public function banUser() {

    }
}

